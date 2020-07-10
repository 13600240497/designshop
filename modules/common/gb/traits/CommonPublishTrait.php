<?php

namespace app\modules\common\gb\traits;

use app\base\SitePlatform;
use app\base\SwooleClient;
use app\modules\common\gb\components\CommonCrontabComponent;
use app\modules\common\gb\models\{
    ActivityGroupModel, ActivityModel, PageModel, PageGroupModel, PageLanguageModel, PagePublishCacheModel, PagePublishLogModel
};

use Diff;
use Diff_Renderer_Html_SideBySide;
use Yii;
use ego\curl\BaseResponseCurl;
use GuzzleHttp\Exception\GuzzleException;


/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/4/12
 * Time: 15:03
 */
trait CommonPublishTrait
{
    use CommonPageParseTrait;
    
    //创建类型|上线文件
    private $createTypeOnline = 'online';
    
    //创建类型|下线文件
    private $createTypeOffline = 'offline';
    
    //HTML文件后缀
    private $htmlFileType = 'html';
    
    //历史文件名前缀
    private $oldFileNamePre = 'diff_';
    
    //字段名
    private $fieldLocalPath = 'local_path';
    
    //页面内容缓存时间
    private $pageContentCacheTimeout = 60 * 60;
    
    //创建页面过程中的错误信息
    public $createHtmlErrors = [];
    
    /**
     * @var \app\modules\common\models\ActivityModel 活动信息
     */
    private $activityInfo;
    
    //页面信息数组
    private $pageArr = [];
    
    /**
     * 批量生成上线page页的html文件（若已存在则覆盖）
     *
     * @param array  $pageIds       页面ID数组
     * @param int    $activityId    活动ID
     * @param bool   $useCache      是否采用缓存来更新
     * @param bool   $updateGoods   是否只更新线上产品
     * @param bool   $updateUiGoods 是否更新组件里的产品
     * @param int    $type          发布类型 默认空全部发布 1不发布html
     * @param string $pipeline      渠道简码
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function batchCreateOnlinePageHtml($pageIds, $activityId, $pipeline, $useCache = false, $updateGoods = false, $updateUiGoods = false)
    {
        return $this->batchCreateHtml([
            $pageIds,
            $activityId,
            $this->createTypeOnline,
            $useCache,
            $updateGoods,
            $updateUiGoods,
            $pipeline,
        ]);
    }
    
    /**
     * 批量生成下线page页的html文件（若已存在则覆盖）
     *
     * @param array $pageIds       页面ID数组
     * @param int   $activityId    活动ID
     * @param bool  $useCache      是否采用缓存来更新
     * @param bool  $updateGoods   是否只更新线上产品
     * @param bool  $updateUiGoods 是否更新组件里的产品
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function batchCreateOfflinePageHtml($pageIds, $activityId, $pipeline, $useCache = true, $updateGoods = false, $updateUiGoods = false)
    {
        //下线页面默认采用上线页面的缓存来更新，所以最后一个参数传true
        return $this->batchCreateHtml([$pageIds, $activityId, $this->createTypeOffline, $useCache, $updateGoods, $updateUiGoods, $pipeline]);
    }
    
    /**
     * 根据版本号回滚页面
     *
     * @param int    $pageId     页面ID
     * @param int    $activityId 活动ID
     * @param string $version    版本号
     * @param string $lang       需要回滚的语言，为空时回滚当前页面的所有语言，默认为空
     *
     * @return array
     * @throws \Throwable
     * @throws \Exception
     */
    public function rollbackPageHtml(int $pageId, int $activityId, string $version, string $lang = '', string $pipeline = '')
    {
        $errorCount = 0;
        if (!empty($pageId)) {
            $envParams = $this->beforeBatchCreateHtml([$pageId], $activityId, $errorCount, $pipeline);
            if ($errorCount) {
                //前置条件失败，则直接返回
                return [!$errorCount, $this->createHtmlErrors];
            }
            
            $langList = $envParams[3];
            if (!empty($lang) && !\in_array($lang, array_column($langList, 'key'), true)) {
                $errorCount++;
                $this->createHtmlErrors[] = $this->createErrorItem($pageId, '语言项超出范围');
                
                return [!$errorCount, $this->createHtmlErrors];
            }
            
            if (!$this->createOnlinePageHtmlByVersion($langList, $pageId, $version, $lang, $pipeline)) {
                $errorCount++;
            }
            $this->afterBatchCreateHtml($envParams);
        }
        
        return [!$errorCount, $this->createHtmlErrors];
    }
    
    /**
     * 批量生成page页的html文件（若已存在则覆盖）
     *
     * @param array $params = [
     *                      array   $pageIds    页面ID数组
     *                      int     $activityId 活动ID
     *                      string  $createType 文件发布类型
     *                      bool    $useCache   是否采用缓存来更新
     *                      bool    $updateGoods 是否只更新线上产品
     *                      ]
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    private function batchCreateHtml($params)
    {
        list($pageIds, $activityId, $createType, $useCache, $updateGoods, $updateUiGoods, $pipeline) = $params;
        
        $errorCount = 0;
        if (!empty($pageIds) && is_array($pageIds)) {
            $envParams = $this->beforeBatchCreateHtml($pageIds, $activityId, $errorCount, $pipeline);
            if ($errorCount) {
                //前置条件失败，则直接返回
                return [!$errorCount, $this->createHtmlErrors];
            }
            
            $langList = $envParams[3];
            /** @var array $pageIds */
            foreach ($pageIds as $pageId) {
                if ($createType === $this->createTypeOnline
                    && !$this->createOnlinePageHtml($langList, $pageId, $useCache, $updateGoods, $updateUiGoods, $pipeline)
                ) {
                    $errorCount++;
                }
                if ($createType === $this->createTypeOffline
                    && !$this->createOfflinePageHtml($langList, $pageId, $useCache, $updateGoods, $updateUiGoods, $pipeline)
                ) {
                    $errorCount++;
                }
            }
            $this->afterBatchCreateHtml($envParams);
        }
        
        return [!$errorCount, $this->createHtmlErrors];
    }
    
    /**
     * 获取发布期间全局的信息（activity、pages）
     *
     * @param array $params
     * @param int   $errorCount
     * @param array $langList
     *
     * @return array
     */
    protected function setPublishGlobalInfo(array $params, int &$errorCount, array &$langList)
    {
        /** @var array $pageIds */
        list($pageIds, $activityId, $pipeline) = $params;
        
        //获取页面信息，以便后面使用
        $pageList = PageModel::find()->where(['id' => $pageIds])->asArray()->all();
        $pageLanguageList = PageLanguageModel::find()->where(['page_id' => $pageIds])->asArray()->all();
        if (!empty($pageList) && !empty($pageLanguageList)) {
            foreach ($pageList as $pageItem) {
                $this->pageArr[ $pageItem['id'] ] = array_merge($this->pageArr[ $pageItem['id'] ] ?? [], $pageItem);
                foreach ($pageLanguageList as $langItem) {
                    if ($pageItem['id'] == $langItem['page_id']) {
                        $this->pageArr[ $pageItem['id'] ]['langList'][ $langItem['lang'] ]['url_name'] = $langItem['url_name'];
                    }
                }
            }
        }
        
        if (\count($pageIds) !== \count($this->pageArr)) {
            //页面未获取全则设置错误信息
            $this->setPageNotFoundError($pageIds, $errorCount);
        }
        if (\count(array_filter(array_unique(array_column($this->pageArr, 'site_code')))) > 1) {
            //!!!为了高效的利用公共信息，batch开头的方法，一次只允许同站点（即site_code相同）页面一起调用!!!
            $this->setSiteCodeCrossSiteError($pageIds, $errorCount);
        }
        
        //记录活动信息，以便后面使用
        if (!empty($activityId)) {
            //活动页信息
            $this->activityInfo = ActivityModel::findOne($activityId);
            if (!$this->activityInfo || !isset(app()->params['sites'][ $this->activityInfo->site_code ])) {
                //页面未获取全则设置错误信息
                $this->setSiteCodeNotFoundError($pageIds, $errorCount);
            }
            $activityGroup = ActivityGroupModel::findOne($this->activityInfo->group_id);
            $langList = ActivityModel::getSitePipelineValidLang($this->activityInfo->site_code, $activityGroup->lang_list, $pipeline);
        } else {
            //首页信息
            $firstPage = current($this->pageArr);
            $langList = $this->getSiteLangs($firstPage['site_code']);
            //补全活动信息
            $this->activityInfo = new ActivityModel();
            $this->activityInfo->id = 0;
            $this->activityInfo->site_code = $firstPage['site_code'];
        }
        
        return $langList;
    }
    
    /**
     * 创建文件前保存现场
     *
     * @param array  $pageIds    页面Id数组
     * @param int    $activityId 活动ID
     * @param int    $errorCount 错误数
     * @param string $pipeline   渠道简码
     *
     * @return array
     */
    protected function beforeBatchCreateHtml($pageIds, $activityId, &$errorCount, $pipeline)
    {
        //这里要记录format的值，方便后面还原回来，因为runAction中有render，会改变format的值，若不还原会报错的
        //language和basePath设置也是的，因为生成多语html时会修改这个值
        $format = app()->response->format;
        $oldLang = app()->language;
        $oldTrans = app()->i18n->translations['yii'];
        if (\is_array(app()->i18n->translations['yii'])) {
            app()->i18n->translations['yii']['basePath'] = '@runtime/languages/' . $activityId;
        } else {
            app()->i18n->translations['yii']->basePath = '@runtime/languages/' . $activityId;
        }
        
        $this->createHtmlErrors = $langList = [];
        
        //设置一些发布期间的全局信息
        $this->setPublishGlobalInfo([$pageIds, $activityId, $pipeline], $errorCount, $langList);
        
        return [$format, $oldLang, $oldTrans, $langList];
    }
    
    /**
     * 创建文件完毕后恢复现场
     *
     * @param $params
     */
    protected function afterBatchCreateHtml($params)
    {
        list($format, $oldLang, $oldTrans) = $params;
        
        app()->response->format = $format;
        app()->language = $oldLang;
        app()->i18n->translations['yii'] = $oldTrans;
        
        //置空页面信息
        $this->activityInfo = null;
        //置空页面信息
        $this->pageArr = [];
    }
    
    /**
     * 生成上线page页的html文件（若已存在则覆盖）
     *
     * @param array $langList    活动语言列表
     * @param int   $pageId      页面ID
     * @param bool  $useCache    是否采用缓存来更新
     * @param  bool $updateGoods 是否只更新线上组件产品
     * @param  int  $type        发布类型 默认空正常发布 1不发布html
     *
     * @return bool
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    private function createOnlinePageHtml($langList, $pageId, $useCache, $updateGoods = false, $updateUiGoods = false, $pipeline)
    {
        if (!empty($langList)) {
            $version = app()->helper->microtime();
            $createResult = [];
            foreach ($langList as $lang) {
                $result = $this->parsePageInArray([$pageId, $lang['key'], $version, true, $useCache, $updateGoods, $updateUiGoods]);
                $actionType = PagePublishLogModel::ACTION_TYPE_ONLINE;
                $createRes = $this->createHtml([$pageId, $lang['key'], $result, $actionType, $version, $useCache, $pipeline]);
                if (false === $createRes) {
                    return false;
                }
                $createResult[] = $createRes;
            }
            !empty($createResult) && $this->saveCreateResult($createResult);
        }
        
        return true;
    }
    
    /**
     * 生成下线page页的html文件（若已存在则覆盖）
     *
     * @param array $langList 活动语言列表
     * @param int   $pageId   页面ID
     * @param bool  $useCache 是否采用缓存来更新
     *
     * @return bool
     * @throws \Throwable
     * @throws \ego\base\JsonResponseException
     * @throws \yii\db\Exception
     */
    private function createOfflinePageHtml($langList, $pageId, $useCache, $updateGoods, $updateUiGoods, $pipeline)
    {
        if (!empty($langList)) {
            $version = app()->helper->microtime();
            //强制使用缓存更新
            $useCache = true;
            $siteCode = $this->activityInfo->site_code;
            $createResult = [];
            foreach ($langList as $lang) {
                
                $result = [];
                if (in_array(SitePlatform::getPlatformCodeBySiteCode($siteCode), ['android', 'ios', 'ipad', 'app'])) {
                    //app不能页面跳转，只能使用下线模板
                    $result['html'] = $this->packageContent($this->getOfflineHtml($siteCode, $pageId, $lang['key'], true, $pipeline));
                } else {
                    //使用原上线页面内容
                    $result = $this->parsePageInArray([$pageId, $lang['key'], $version, true, $useCache, $updateGoods, $updateUiGoods]);
                    //将下线提示框的内容追加到原内容后面
                    $result['html'] .= $this->getOfflineHtml($siteCode, $pageId, $lang['key'], false, $pipeline);
                }
                
                $actionType = PagePublishLogModel::ACTION_TYPE_OFFLINE;
                $createRes = $this->createHtml([$pageId, $lang['key'], $result, $actionType, $version, $useCache, $pipeline]);
                if (false === $createRes) {
                    return false;
                }
                $createResult[] = $createRes;
            }
            !empty($createResult) && $this->saveCreateResult($createResult);
        }
        
        return true;
    }
    
    /**
     * 通过版本号去获取缓存html，然后生成上线page页的html文件（若已存在则覆盖）
     *
     * @param array  $langList 活动语言列表
     * @param int    $pageId   页面ID
     * @param string $version  版本号
     * @param string $lang     需要回滚的语言
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    private function createOnlinePageHtmlByVersion($langList, $pageId, $version, $lang, $pipeline)
    {
        if (!empty($langList)) {
            // 生成新的版本号，因为这是一次新的发布记录
            $newVersion = app()->helper->microtime();
            $createResult = [];
            !empty($lang) && $langList = [['key' => $lang]];//lang不为空时则只回滚该语言，注意langList结构
            foreach ($langList as $item) {
                $result = $this->parsePageInArrayByVersion([$pageId, $item['key'], $version, $newVersion, true]);
                $actionType = PagePublishLogModel::ACTION_TYPE_ONLINE;
                $createRes = $this->createHtml([$pageId, $item['key'], $result, $actionType, $newVersion, '', $pipeline]);
                if (false === $createRes) {
                    return false;
                }
                $createResult[] = $createRes;
            }
            !empty($createResult) && $this->saveCreateResult($createResult);
        }
        
        return true;
    }
    
    /**
     * 保存页面生成后的结果数据
     *
     * @param array $params
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    private function saveCreateResult($params)
    {
        foreach ($params as list($pageId, $lang, $logData, $redisData, $createRes, $pipeline)) {
            //记录page的访问URL和本地文件路径
            if ($pageLangModel = PageLanguageModel::findOne(['page_id' => $pageId, 'lang' => $lang])) {
                $publishPathPre = $this->getHtmlFilePathPreOnS3($this->activityInfo->site_code, $lang, $pipeline);
                $pageLangModel->page_url = explode($publishPathPre, $createRes['html_file'])[1] . SitePlatform::getPlatformUrl($lang, $this->activityInfo->site_code, $pipeline);
                
                $pageLangModel->local_files = json_encode([
                    'css'  => $createRes['css_file'],
                    'js'   => $createRes['js_file'],
                    'html' => $createRes['html_file']
                ]);
                $pageLangModel->s3_files = json_encode([
                    'css'  => $createRes['css_s3'],
                    'js'   => $createRes['js_s3'],
                    'html' => $createRes['html_s3']
                ]);
                $pageLangModel->save(true);
                
                if (!empty($logData)) {
                    $this->savePublishLog($logData);
                }
                
                if (!empty($redisData)) {
                    if (!$this->pushTaskToRedis($redisData)) {
                        $this->createHtmlErrors[] = $this->createErrorItem($pageId, '推送任务进redis队列出错');
                        
                        return false;
                    }
                    /*if (true !== $pushState = (new CommonCrontabComponent())->syncPushPage($redisData)) {
                        $this->createHtmlErrors[] = $this->createErrorItem($pageId, $pushState);
                        
                        return false;
                    }*/
                }
            }
        }
        
        return true;
    }
    
    /**
     * 生成html文件
     *
     * @param array $params = [
     *                      int     $pageId     页面ID
     *                      string  $lang       语言代码简称
     *                      array   $result     页面不同部位html数组
     *                      int     $actionType 操作类型
     *                      string  $version    版本号
     *                      bool    $useCache   是否采用缓存来更新
     *                      ]
     *
     * @return bool|array
     * @throws \yii\base\ViewNotFoundException
     * @throws \Exception
     */
    private function createHtml($params)
    {
        $logData = $redisData = [];
        list($pageId, $lang) = $params;
        $createRes = $this->createStatics($params, $redisData, $logData);
        if (false === $createRes) {
            return false;
        }
        
        return [$pageId, $lang, $logData, $redisData, $createRes, $params[6]];
    }
    
    /**
     * 生成static静态文件
     *
     * @param array $params    = [
     *                         int     $pageId     页面ID
     *                         string  $lang       语言代码简称
     *                         bool    $useCache   是否采用缓存来更新
     *                         array   $result     页面不同部位html数组
     *                         int     $actionType 操作类型
     *                         string  $version    版本号
     *                         string  $siteCode   站点siteCode
     *                         ]
     * @param array $redisData 存储到Redis中的数据数组
     * @param array $logData   日志数据
     *
     * @return bool|array
     * @throws \Exception
     */
    private function createStatics($params, &$redisData, &$logData)
    {
        $siteCode = $this->activityInfo->site_code;
        list($pageId, $lang, $result, $actionType, $version, $useCache, $pipeline) = $params;
        
        //匹配出组件中的自定义样式，合并到head
        $this->matchComponentsCss($result);
        $staticFileSuffix = (int) $this->activityInfo->id ? 'activity' : 'home';
        //生成css文件【20180929 将geshop-grid_home.css内容读取出来放到合并CSS文件前】
        if (isGearbestSite($siteCode)) {
            $gridCss = app()->basePath . '/htdocs/resources/sitesPublic/' . $siteCode . '/css/geshop-grid_' . $staticFileSuffix . '.css';
        } else {
            $gridCss = app()->basePath . '/htdocs/resources/sitesPublic/default/css/geshop-grid_' . $staticFileSuffix . '.css';
        }
        
        if (file_exists($gridCss)) {
            $result['css'] = file_get_contents($gridCss) . ($result['css'] ?? '');
        }
        list($cssFile, $cssS3) = !empty($result['css'])
            ? $this->createFile(
                [$pageId, $lang, 'css', $result['css'], $actionType, $version, $siteCode, '', '', $pipeline],
                $redisData,
                $logData
            ) : [false, ''];
        if ('' === $cssFile) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面css文件生成失败');
            
            return false;
        }
        
        //生成js文件(注意合并顺序，labJs在最前，其次commonJs)
        $labJs = app()->basePath . '/htdocs/resources/javascripts/library/LAB.js';
        if (isGearbestSite($siteCode)) {
            $commonJs = app()->basePath . '/htdocs/resources/sitesPublic/' . $siteCode . '/js/gs_common_' . $staticFileSuffix . '.min.js';
        } else {
            $commonJs = app()->basePath . '/htdocs/resources/sitesPublic/default/js/gs_common_' . $staticFileSuffix . '.min.js';
        }
        
        if (file_exists($commonJs)) {
            $result['js'] = file_get_contents($commonJs) . ($result['js'] ?? '');
        }
        if (file_exists($labJs)) {
            $result['js'] = file_get_contents($labJs) . ($result['js'] ?? '');
        }
        list($jsFile, $jsS3) = !empty($result['js'])
            ? $this->createFile(
                [$pageId, $lang, 'js', $result['js'], $actionType, $version, $siteCode, '', '', $pipeline],
                $redisData,
                $logData
            ) : [false, ''];
        
        if ('' === $jsFile) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面js文件生成失败');
            
            return false;
        }
        
        //先获取完整的html内容
        $result['html'] = $this->buildHtmlWithJsAndCss([$pageId, $lang, $siteCode, $result['html'], $cssS3, $jsS3, true]);
        if (false === $result['html']) {
            return false;
        }
        
        list($htmlFile, $htmlS3) = $this->createFile([
            $pageId,
            $lang,
            'html',
            $result['html'],
            $actionType,
            $version,
            $siteCode,
            $cssS3,
            $jsS3,
            $pipeline
        ], $redisData, $logData);
        if ('' === $htmlFile) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, '页面html文件生成失败');
            
            return false;
        }
        
        return [
            'css_file'  => $cssFile,
            'css_s3'    => $cssS3,
            'js_file'   => $jsFile,
            'js_s3'     => $jsS3,
            'html_file' => $htmlFile,
            'html_s3'   => $htmlS3,
        ];
    }
    
    /**
     * 匹配出组件中的自定义样式，合并到head
     *
     * @param $data
     */
    public function matchComponentsCss(&$data)
    {
        $pattern = /** @lang text */
            '/<!-- embed stylesheet begin -->\s*'
            . '<style type="text\/css">(.+?)<\/style>\s*<!-- embed stylesheet end -->/';
        preg_match_all($pattern, $data['html'], $matches);
        $data['css'] = ($data['css'] ?? '') . (!empty($matches[1]) ? implode('', array_unique($matches[1])) : '');
        $data['html'] = preg_replace($pattern, '', $data['html']);
    }
    
    /**
     * 创建缓存文件，并同步到S3上
     *
     * @param array $params    = [
     *                         int $pageId 页面ID
     *                         string $lang 语言代码简称
     *                         string $fileType 文件后缀名
     *                         string $content 文件内容
     *                         int $actionType 操作类型
     *                         string $siteCode 站点siteCode
     *                         ]
     * @param array $redisData 存储到Redis中的数据数组
     * @param array $logData   日志数据
     *
     * @return array
     * @throws \Exception
     */
    private function createFile($params, &$redisData, &$logData)
    {
        list($pageId, $lang, $fileType, $content, $actionType, $version, $siteCode, $cssS3, $jsS3, $pipeline) = $params;
        $localPath = $this->getLocalPath([$pageId, $lang, $fileType, $siteCode, $pipeline]);
        
        //内容写入Redis中
        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getPublishContentKey([$pageId, $lang, $fileType, $version]);
        app()->redis->setex($redisKey, $this->pageContentCacheTimeout, $content);
        
        //记录日志
        $fileInfo = pathinfo($localPath);
        $user = app()->user->username ?? '';
        $time = time();
        $log = [
            'version'             => $version,
            'log_type'            => '',
            'page_id'             => (int) $pageId,
            'lang'                => $lang,
            'site_code'           => $siteCode,
            'action_type'         => $actionType,
            'file_name'           => $fileInfo['basename'],
            'file_type'           => $fileType,
            'file_size'           => 0,
            'file_hash'           => '',
            $this->fieldLocalPath => $this->getRelativePathByLocalPath($localPath),
            's3_url'              => '',
            'diff'                => '',
            'ip'                  => app()->ip->get(true),
            'create_user'         => $user,
            'create_time'         => $time,
            'update_user'         => $user,
            'update_time'         => $time
        ];
        
        $s3Key = $this->getS3KeyByLocalPath($localPath, $fileType);
        $item = [
            'route'               => app()->controller->getRoute(),
            $this->fieldLocalPath => $localPath,
            's3_key'              => $s3Key,
            'activity_id'         => (int) $this->pageArr[ $pageId ]['activity_id'],
            'page_id'             => $pageId,
            'lang'                => $lang,
            'version'             => $version,
            'file_type'           => $fileType,
            'site_code'           => $siteCode,
            'redis_key'           => $redisKey,
            'css_s3'              => $cssS3,
            'js_s3'               => $jsS3,
            'pipeline'            => $pipeline,
        ];
        $log['log_type'] = PagePublishLogModel::LOG_TYPE_PUBLISH;
        $item['log_data'] = $log;
        $redisData[] = json_encode($item);
        
        //!!!!因为发布和上传是分开的操作，所以这里S3文件URL不管传不传都需要计算出来
        $s3Url = $this->getS3UrlByS3Key([$s3Key, $lang, $fileType, $siteCode, $pipeline]);
        
        $log['log_type'] = PagePublishLogModel::LOG_TYPE_CREATE;
        $logData[] = $log;
        
        return [$this->getRelativePathByLocalPath($localPath), $s3Url];
    }
    
    /**
     * 推送任务到redis队列中
     *
     * @param array $data 存储到Redis中的值
     *
     * @return bool
     */
    private function pushTaskToRedis($data)
    {
        if (!empty($data) && \is_array($data)) {
            $redisLength = app()->redis->rpush(CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getPushTaskRedisKey(), ...$data);
            \Yii::info('发布任务push进redis：' . \count($data) . '-' . json_encode($data), __METHOD__);
            
            return \is_int($redisLength);
        }
        
        return true;
    }
    
    /**
     * 将js和css拼接到html中
     *
     * @param array $params
     *
     * @return bool|string
     * @throws \yii\base\ViewNotFoundException
     */
    private function buildHtmlWithJsAndCss($params)
    {
        list($pageId, $lang, $siteCode, $html, $cssS3, $jsS3, $isPublish) = $params;
        
        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::getById($pageId);
        $activityPageType = PageModel::getActivityPageType($pageModel);
        
        //对于html内容，需要将头尾拼上
        $componentStatic = ['css' => '', 'js' => ''];
        $cssVersion = app()->params['css_version'];
        $componentStatic['css'] = $this->getHeadExtraCss($pageId, $lang, $cssVersion, $siteCode, $activityPageType, $isPublish);
        if (!empty($cssS3)) {
            $componentStatic['css'] .= /** @lang html */
                '<link rel="stylesheet" href="' . $cssS3 . '?version=' . $cssVersion . '">';
        }
        
        $componentStatic['js'] = $this->getHeadExtraJs($cssVersion, $lang, $siteCode, $activityPageType, $isPublish);
        if (!empty($jsS3)) {
            $componentStatic['js'] .= /** @lang html 解决站点组件JS未加载的问题 */
                '<script src="' . $jsS3 . '?version=' . $cssVersion . '"></script>';
        }
    
        $componentStatic['js'] .= '<script src="//geshopcss.logsss.com/vue/vue.min.js"></script>';
        $componentStatic['js'] .= '<script src="' . app()->url->assets->clientJs('initial', $isPublish) . '"></script>';
        
        //获取头尾html(将生成的css和js文件插入头尾位置)
        $headerAndFooter = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic);
        if (empty($headerAndFooter)) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, '平台头尾未找到', [
                'lang'     => $lang,
                'siteCode' => $siteCode
            ]);
            
            return false;
        }
        
        $main = '/<!--\s*geshop\s*main\s*start\s*-->/';
        preg_match($main, $headerAndFooter, $matches);
        if (!empty($matches[0])) {
            $html = str_replace($matches[0], $matches[0] . $html, $headerAndFooter);
        }
        
        return $html;
    }
    
    /**
     * 从数据库获取缓存
     *
     * @param array $params
     *
     * @return string
     * @throws \yii\base\ViewNotFoundException
     */
    private function getContentCache($params)
    {
        list($pageId, $lang, $siteCode, $fileType, $cssS3, $jsS3) = $params;
        $content = '';
        $publishCacheModel = PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
        if ($publishCacheModel) {
            $content = $publishCacheModel[ $fileType ];
            
            if ($fileType === $this->htmlFileType
                && false !== ($result = $this->buildHtmlWithJsAndCss([$pageId, $lang, $siteCode, $content, $cssS3, $jsS3, true]))
            ) {
                //对于html内容，需要将头尾拼上
                $content = $result;
            }
        }
        
        return $content;
    }
    
    
    /**
     * 存储文件到S3上
     *
     * @param array $data = [
     *                    string $localPath 本地文件路径
     *                    string $s3Key 文件在S3上存储key
     *                    string $lang 语言代码简称
     *                    array $logData 日志数据
     *                    ]
     *
     * @return array
     * @throws \Exception
     */
    public function putObjectToS3($data)
    {
        $redisKey = $data['redis_key'];
        $localPath = $data['local_path'];
        $s3Key = $data['s3_key'];
        $logData = $data['log_data'];
        $lang = $data['lang'];
        $pageId = (int)$data['page_id'];
        $siteCode = $data['site_code'];
        $fileType = $data['file_type'];
        $version = $data['version'];
        $cssS3 = $data['css_s3'];
        $jsS3 = $data['js_s3'];
        $pipeline = $data['pipeline'];
    
        //内容写入文件
        $content = app()->redis->get($redisKey);
        if (empty($content)) {
            //内容过期后，从数据库去获取
            $content = $this->getContentCache([$pageId, $lang, $siteCode, $fileType, $cssS3, $jsS3]);
        }
    
        if (false === file_put_contents($localPath, $content) || !file_exists($localPath)) {
            Yii::error('文件内容写入失败' . $localPath, __METHOD__);
            return [false, '文件内容写入失败：' . $localPath];
        }
    
        if ($fileType === $this->htmlFileType) {
            $s3Res = app()->s3->putObject($localPath, $s3Key, null,  ['CacheControl' => 'max-age=' . app()->params['cdnCacheControl'],
                'Expires'=> gmdate('D, d M Y H:i:s T', strtotime(app()->params['cdnExpires']))
            ]);
        } else {
            $s3Res = app()->s3PublishStatic->putObject($localPath, $s3Key);
        }
    
        $s3Url = \is_string($s3Res) ? '' : $s3Res->get('ObjectURL');
    
        /** @var \Aws\Result $s3Res */
        Yii::info('静态文件推送s3结果：' . $s3Url . '|||||||||||' . $s3Res, __METHOD__);
    
        $res = false;
        if (!empty($s3Url)) {
            $res = true;
            clearstatcache();//清理filesize等函数的缓存
            $fileSize = filesize($localPath);
            $fileHash = hash_file('md5', $localPath);
        
            //更新本地文件生成记录的file_size和file_hash
            $resUpdate = PagePublishLogModel::updateAll([
                'file_size' => $fileSize,
                'file_hash' => $fileHash
            ], [
                'log_type' => PagePublishLogModel::LOG_TYPE_CREATE,
                'page_id' => $pageId,
                'lang' => $lang,
                'file_type' => $fileType,
                'version' => $version
            ]);
        
            //上传成功后，记录日志
            $logData['s3_url'] = $s3Url;
            $logData['file_size'] = $fileSize;
            $logData['file_hash'] = $fileHash;
        
            $resSave = $this->savePublishLog([$logData]);
        
            //清理CDN缓存
            $clearUrl = $this->getS3UrlByS3Key([$s3Key, $lang, $fileType, $siteCode, $pipeline, $pageId]);
            $this->clearCDNCache($clearUrl);
        
            if (!$resSave || !$resUpdate) {
                return [false, '文件上传后更新本地记录出错'];
            }
        
            //清理redis缓存记录
            app()->redis->del($redisKey);
        }
    
        return [$res, $s3Url];
    }
    
    /**
     * 清理CDN缓存
     *
     * @param string $url 待清理的URL
     */
    private function clearCDNCache($url)
    {
        $api = app()->params['clearCDNAPI'];
        if (empty($api)) {
            //未配置clearCDNAPI的则无需清理CDN缓存
            return;
        }

        $api .= $url;
        $responseData = [];
        try {
            $curl = new BaseResponseCurl();
            $response = $curl->slient()->request('get', $api);
            $response && $responseData = json_decode($response->getBody() . '', true);
            \Yii::info('CDN缓存清理返回原始值：' . $api . '----' . json_encode($responseData), __METHOD__);
        } catch (GuzzleException $e) {
            Yii::error('CDN缓存清理GuzzleException：' . $e->getMessage(), __METHOD__);
        } catch (\Exception $e) {
            Yii::error('CDN缓存清理Exception：' . $e->getMessage(), __METHOD__);
        }

        if (!(isset($responseData['results']) && isset($responseData['results'][1])
            && $responseData['results'][1]['result'] && $responseData['results'][1]['result']['status']
            && $responseData['results'][1]['result']['status'] === $this->msgSuccess)
        ) {
            Yii::error('CDN缓存清理失败：' . $api . '---' . json_encode($responseData), __METHOD__);
        }
    }
    
    /**
     * 保存本地缓存文件生成日志记录、文件发布到S3上去的日志记录
     *
     * @param array $data
     *
     * @return int
     * @throws \yii\db\Exception
     */
    private function savePublishLog(array $data)
    {
        return PagePublishLogModel::insertAllData($data);
    }
    
    /**
     * 获取文本比对结果
     *
     * @param string $oldContent 旧文本内容
     * @param string $newContent 新文本内容
     *
     * @return string
     */
    public function getDiffContent($oldContent, $newContent)
    {
        // Include two sample files for comparison
        $a = explode("\n", $oldContent);
        $b = explode("\n", $newContent);
        
        // Options for generating the diff
        $options = [
            //'ignoreWhitespace' => true,
            //'ignoreCase' => true,
        ];
        
        // Initialize the diff class
        $diff = new Diff($a, $b, $options);
        
        $renderer = new Diff_Renderer_Html_SideBySide;
        
        return $this->packageDiffContent($diff->render($renderer));
    }
    
    /**
     * 将diff内容打包成html
     *
     * @param string $html diff内容html
     *
     * @return string
     */
    private function packageDiffContent($html)
    {
        return /** @lang html */
            '<html><head><title>文件内容差异比对</title></head><body>' . $html . '</body></html>';
    }
    
    /**
     * 得到最后一次生成的文件记录
     *
     * @param int    $pageId   页面ID
     * @param string $lang     语言代码简称
     * @param string $fileType 文件后缀名
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    private function getLastFileContent($pageId, $lang, $fileType)
    {
        return PagePublishLogModel::find()->where([
            'log_type'  => PagePublishLogModel::LOG_TYPE_CREATE,
            'page_id'   => $pageId,
            'lang'      => $lang,
            'file_type' => $fileType
        ])->orderBy('id DESC')->asArray()->one();
    }
    
    /**
     * 根据文件在S3上存储的key得到S3上访问URL
     *
     * @param array $params = [
     *                      string $s3Key    文件在S3上的路径
     *                      string $lang     语言代码简称
     *                      string $fileType 文件存储后缀
     *                      string $siteCode 站点siteCode
     *                      ]
     *
     * @return string
     */
    private function getS3UrlByS3Key($params)
    {
    
        list($s3Key, $lang, $fileType, $siteCode, $pipeline) = $params;
        $siteConf = app()->params['sites'][ $siteCode ];
    
        if ($fileType === $this->htmlFileType) {
            $delimiter = $this->getHtmlFilePathPreOnS3($siteCode, $lang, $pipeline);
            $domain = $siteConf['secondary_domain'][ $pipeline ][ $lang ];
        } else {
            $delimiter = app()->params['s3PublishConf']['staticKeyPre'] . '/'
                . $this->getStaticsFilePathPreOnS3($siteCode, $lang, $pipeline);
            $domain = app()->params['s3PublishConf']['staticDomain'] . $delimiter;
        }
    
        $explode = explode($delimiter, $s3Key);
    
        return $explode && isset($explode[1]) ? $domain . $explode[1] : '';
    }
    
    /**
     * 根据文件在本地存储的绝对路径，获取对应的在S3上存储的key
     *
     * @param string $path     文件在本地存储的绝对路径
     * @param string $fileType 文件存储后缀
     *
     * @return string
     */
    private function getS3KeyByLocalPath($path, $fileType)
    {
        $keyPre = $fileType === $this->htmlFileType ? '' : app()->params['s3PublishConf']['staticKeyPre'];
        
        return $keyPre . $this->getRelativePathByLocalPath($path);
    }
    
    /**
     * 获取文件在本地存储的绝对路径
     *
     * @param array $params = [
     *                      int    $pageId   页面ID
     *                      string $lang     语言代码简称
     *                      string $fileType 文件存储后缀
     *                      string $siteCode 站点siteCode
     *                      ]
     *
     * @return string
     */
    private function getLocalPath($params)
    {
        list($pageId, $lang, $fileType, $siteCode, $pipeline) = $params;
        $path = $this->getPathByFileType($fileType, $lang, $siteCode, $pipeline);
        
        if ('home' === app()->controller->module->module->id) {
            $urlName = 'index';
        } else {
            if ($fileType === $this->htmlFileType && !empty($this->pageArr[ $pageId ]['langList'][ $lang ]['url_name'])) {
                $suffix = !empty(app()->params['sites'][ $siteCode ]['isTest']) ? '_' . md5($pageId) : '';
                $specialId = PageGroupModel::findOne(['page_id' => $pageId])->getAttribute('special_id');
                if ($specialId > 0) {
                    $urlName = $this->pageArr[ $pageId ]['langList'][ $lang ]['url_name'] . $suffix;
                    $urlName .= "-special-{$specialId}";
                } else {
                    $urlName = $this->pageArr[ $pageId ]['langList'][ $lang ]['url_name'] . $suffix;
                }
            } else {
                $urlName = md5($pageId . '.' . $fileType);
            }
        }
        $path .= $urlName . '.' . $fileType;
        
        //js和css需要重命名文件名来防止缓存
        if ($fileType !== $this->htmlFileType) {
            $fileName = pathinfo($path, PATHINFO_BASENAME);
            $path = str_replace($fileName, md5($fileName . microtime()) . '.' . $fileType, $path);
        }
        
        return $path;
    }
    
    /**
     * 根据文件在本地绝对路径返回相对路径
     *
     * @param string $localPath 文件在本地绝对路径
     *
     * @return string
     */
    private function getRelativePathByLocalPath($localPath)
    {
        $explode = !empty($localPath) ? explode('runtime', $localPath) : [];
        
        return $explode && isset($explode[1]) ? $explode[1] : '';
    }
    
    /**
     * 根据文件在本地相对路径返回绝对路径
     *
     * @param string $relativePath 文件在本地相对路径
     *
     * @return string
     */
    private function getLocalPathByRelativePath($relativePath)
    {
        return app()->basePath . DIRECTORY_SEPARATOR . 'runtime' . $relativePath;
    }
    
    /**
     * 根据文件类型获取文件在本地缓存的路径
     *
     * @param string $fileType 文件类型
     * @param string $lang     语言代码简称
     * @param string $siteCode
     * @param string $pipeline
     *
     * @return string
     */
    private function getPathByFileType($fileType, $lang, $siteCode, $pipeline)
    {
        $dir = $fileType === $this->htmlFileType
            ? $this->getHtmlFilePathPreOnS3($siteCode, $lang, $pipeline)
            : $this->getStaticsFilePathPreOnS3($siteCode, $lang, $pipeline);
        $path = app()->basePath . DIRECTORY_SEPARATOR
            . 'runtime' . DIRECTORY_SEPARATOR
            . $dir . DIRECTORY_SEPARATOR;
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            return '';
        }
        
        return $path;
    }
    
    /**
     * 根据站点SiteCode和语言lang获取html文件在S3上存储路径的前缀
     *
     * @param $siteCode
     * @param $lang
     * @param $pipeline
     *
     * @return mixed
     */
    private function getHtmlFilePathPreOnS3($siteCode, $lang, $pipeline)
    {
        $pathKey = 's3PublishPath';
        if ('home' === app()->controller->module->module->id) {
            $pathKey = 's3HomePublishPath';
        }
        $siteConf = app()->params['sites'][ $siteCode ][ $pathKey ];
        
        return $siteConf[ $pipeline ][ $lang ];
    }
    
    /**
     * 根据站点SiteCode和语言lang获取statics文件在S3上存储路径的前缀
     *
     * @param  string $siteCode
     * @param  string $lang
     * @param  string $pipeline
     *
     * @return mixed
     */
    private function getStaticsFilePathPreOnS3($siteCode, $lang, $pipeline)
    {
        $siteConf = app()->params['sites'][ $siteCode ]['s3StaticPath'];
        
        return $siteConf[ $pipeline ][ $lang ] ?? '';
    }
    
    /**
     * 添加自动刷新任务（若存在则会更新）
     *
     * @param int $pageId      页面ID
     * @param int $refreshTime 自动刷新时间间隔
     *
     * @return bool
     */
    public function zaddRefreshTask($pageId, $refreshTime)
    {
        $key = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getRefreshTaskRedisKey();
        try {
            if ($refreshTime) {
                app()->redis->zadd($key, time() + $refreshTime, $pageId);
            } else {
                app()->redis->zrem($key, $pageId);
            }
        } catch (\Exception $e) {
            \Yii::info('自动刷新队列处理错误：' . $pageId . '-' . $refreshTime, __METHOD__);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 设置跨站点siteCode的错误信息
     *
     * @param array $pageIds
     * @param int   $errorCount
     */
    protected function setSiteCodeCrossSiteError(array $pageIds, int &$errorCount)
    {
        foreach ($pageIds as $pageId) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, '每次发布仅能同一站点下一起发布');
        }
        $errorCount = \count($pageIds);
    }
    
    /**
     * 设置未找到siteCode的错误信息
     *
     * @param array $pageIds
     * @param int   $errorCount
     */
    protected function setSiteCodeNotFoundError(array $pageIds, int &$errorCount)
    {
        foreach ($pageIds as $pageId) {
            $this->createHtmlErrors[] = $this->createErrorItem($pageId, '站点site_code错误');
        }
        $errorCount = \count($pageIds);
    }
    
    /**
     * 设置未找到页面的错误信息
     *
     * @param array $pageIds
     * @param int   $errorCount
     */
    protected function setPageNotFoundError(array $pageIds, int &$errorCount)
    {
        $errors = 0;
        foreach ($pageIds as $pageId) {
            if (!isset($this->pageArr[ $pageId ])) {
                $errors++;
                $this->createHtmlErrors[] = $this->createErrorItem($pageId, '未找到页面ID对应的信息');
            }
        }
        $errorCount = $errors;
    }
    
    /**
     * 组建error错误信息结构
     *
     * @param       $pageId
     * @param       $message
     * @param array $data
     *
     * @return array
     */
    protected function createErrorItem($pageId, $message, array $data = [])
    {
        return [
            'page_id' => $pageId,
            'message' => $message,
            'data'    => $data
        ];
    }
    
}
