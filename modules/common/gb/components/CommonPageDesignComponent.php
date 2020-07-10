<?php

namespace app\modules\common\gb\components;

use app\modules\common\gb\models\{
    PageLanguageModel, PageModel, ActivityModel, PageLayoutModel, PageUiComponentDataModel
};

use app\modules\common\gb\traits\{
    CommonVerifyStatusTrait, CommonPublishTrait, CommonConvertTrait
};

use app\modules\component\components\ManagerComponent;
use app\modules\component\models\ComponentModel;
use ego\base\JsonResponseException;
use yii\helpers\ArrayHelper;
use yii\db\Exception;
use yii\web\Response;
use Yii;

/**
 * 页面装修设计-整个页面相关
 *
 */
class CommonPageDesignComponent extends Component
{
    use CommonVerifyStatusTrait, CommonPublishTrait, CommonConvertTrait;

    //表单文件名
    const FORM_NAME = 'form.twig';

    //参数验证正确返回的消息
    const SUCCESS_MSG = '';

    //权限检查缓存key前缀
    const AUTH_CACHE_KEY_PRE = 'geshop::page::auth::';

    //权限检查缓存时间
    const AUTH_CACHE_TIME = 60;

    //page_id字段
    const FIELD_PAGE_ID = 'page_id';

    //权限检查缓存key前缀
    const PAGE_TPL_CACHE_KEY_PRE = 'geshop::page::tpl::cache::';

    //为了让在controller中不直接引用model，这里转换下
    const TYPE_PC      = ActivityModel::TYPE_PC;
    const TYPE_MOBILE  = ActivityModel::TYPE_MOBILE;
    const TYPE_APP     = ActivityModel::TYPE_APP;
    const TYPE_IOS     = ActivityModel::TYPE_IOS;
    const TYPE_IPAD    = ActivityModel::TYPE_IPAD;
    const TYPE_ANDROID = ActivityModel::TYPE_ANDROID;
    
    /**
     * @var array 包含SKU的ui组件key
     */
    public $uiKeyIncludeSku = ['U000001', 'U000031', 'U000041'];

    /**
     * @var \app\modules\common\models\PageModel 页面信息
     */
    public $pageInfo;

    //忽略权限检查的路由
    private $authCheckIgnoreRoute;

    //忽略修改页面自动刷新属性的路由
    private $changeRefreshIgnoreRoute;

    public function init()
    {
        parent::init();
        $this->authCheckIgnoreRoute = [
            'activity/gb/design/index',
            'activity/gb/design/preview',
            'activity/gb/design/copy-page',
            'activity/gb/design/copy-sku',
            'home/gb/design/index',
            'home/gb/design/preview',
        ];
        $this->changeRefreshIgnoreRoute = [
            'activity/gb/design/index',
            'activity/gb/design/preview',
            'activity/gb/design/release',
            'activity/gb/design/get-setting',
            'activity/gb/layout-design/get-layout-form',
            'activity/gb/ui-design/get-form',
        ];
        if (!empty($_REQUEST[ static::FIELD_PAGE_ID ])) {
            $this->pageInfo = PageModel::getDesignPageInfo((int) $_REQUEST[ static::FIELD_PAGE_ID ]);
        }
    }
    
    /**
     * 检查访问权限
     *
     * @param int    $pageId 页面ID
     * @param string $route  访问路由
     *
     * @return bool
     * @throws JsonResponseException
     */
    public function checkAuth($pageId, $route, $lang)
    {
        if ($this->ignoreCheckRoute($route)) {
            return true;
        }

        if (empty($pageId)) {
            throw new JsonResponseException($this->codeFail, 'page_id不能为空');
        }

        $redisKey = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getDesignPageLockKey($pageId, $lang);
        $data = json_decode(app()->redis->get($redisKey), true);
        if (empty($data['id'])) {
            $user = ArrayHelper::toArray(app()->user->identity);
            if (!empty($user['admin'])) {
                app()->redis->setex($redisKey, static::AUTH_CACHE_TIME, json_encode($user['admin']));
            }
        } elseif ($data['id'] === app()->user->id) {
            app()->redis->expire($redisKey, static::AUTH_CACHE_TIME);
        } else {
            //只返回给前端有限的字段信息，敏感信息不返回
            $returnData = [
                'id'            => $data['id'],
                'department_id' => $data['department_id'],
                'username'      => $data['username'],
                'realname'      => $data['realname'],
                'user_no'       => $data['user_no']
            ];
            $message = '当前页面有其他人正在操作：' . ($returnData['realname'] ?: $returnData['username'])
                . '，请于' . (int) (static::AUTH_CACHE_TIME / 60) . '分钟后再尝试操作';
            throw new JsonResponseException($this->codeFail, $message, $returnData);
        }

        return true;
    }

    /**
     * 是否忽略权限检查
     *
     * @param string $route 访问路由
     *
     * @return bool
     */
    private function ignoreCheckRoute($route)
    {
        if (\in_array($route, $this->authCheckIgnoreRoute, true)) {
            return true;
        }

        return false;
    }

    /**
     * 检查访问权限
     *
     * @param int    $pageId 页面ID
     * @param string $route  访问路由
     */
    public function changePageAutoRefresh($pageId, $route)
    {
        if (!\in_array($route, $this->changeRefreshIgnoreRoute, true)) {
            $pageModel = PageModel::findOne($pageId);
            if ($pageModel) {
                $pageModel->auto_refresh = 0;
                $pageModel->save(true);
            }
        }
    }

    /**
     * 获取首页需要的数据
     *
     * @param $pageId
     * @param $type
     * @param $siteCode
     * @param $lang
     * @param $place
     *
     * @return array
     * @throws \Throwable
     */
    public function getDesignData($pageId, $type, $siteCode, $lang, $place)
    {
        //获取可用的组件列表
        $component = new ManagerComponent();
        $data = $component->getAvailableList([$type, ComponentModel::RANGE_RESPONSIVE], $place, $siteCode, $lang);

        //自定义布局组件编码
        $customKey = empty($data['custom']) ? 0 : $data['custom']->component_key;

        //解析页面
        $pageHtml = $this->parsePage($pageId, $lang);

        return ['data' => $data, 'customKey' => $customKey, 'pageHtml' => $pageHtml];
    }

    /**
     * 获取页面关联关系
     *
     * @param int    $pageId
     * @param string $lang
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function getRelationList(int $pageId, string $lang)
    {
        $pageActivityInfo = PageModel::getPageActivityInfo($pageId);
        $siteSuffix = $pageActivityInfo ? explode('-', $pageActivityInfo->site_code)[1] : '';

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, [
            'current' => $siteSuffix,
            'list'    => $this->getConvertRelationList($pageId, $lang, $pageActivityInfo->site_code, $pageActivityInfo->pipeline)
        ]);
    }

    /**
     * 预览页面
     *
     * @param string $pid  页面32位ID
     * @param string $lang 语种
     *
     * @param bool   $useCache
     *
     * @return string
     * @throws \Throwable
     */
    public function preview($pid, $lang, $useCache = false)
    {
        if (empty($pid) || empty($lang)) {
            return '参数不全';
        }

        if (!($pageModel = PageModel::getByPId($pid))) {
            return '页面不存在或已被删除';
        }

        $activityPageType = PageModel::getActivityPageType($pageModel);
        $pageId = $pageModel->id;

        // 先获取页面html、js、css
        $pageData = $this->parsePage($pageId, $lang, true, $useCache, false);

        $cssVersion = app()->params['css_version'];
        $componentStatic['css'] = $this->getHeadExtraCss($pageId, $lang, $cssVersion, $pageModel->site_code, $activityPageType);
        $componentStatic['js'] = $this->getHeadExtraJs($cssVersion, $lang, $pageModel->site_code, $activityPageType);

        //!!!预览页特殊处理，预览页JS和CSS未打包，所以JS和CSS放到头尾位置!!!
        $componentStatic['css'] .= '<style type="text/css">' . $pageData['css'] . '</style>';
        $componentStatic['js'] .= '<script src="//geshopcss.logsss.com/vue/vue.min.js"></script>';
        $componentStatic['js'] .= '<script src="' . app()->url->assets->clientJs('initial') . '"></script>';
        $componentStatic['js'] .= '<script defer="defer">' . $pageData['js'] . '</script>';
        
        //获取头尾
        $result = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic);

        //页面预览时，过滤掉可拖拽属性
        $html = str_replace(self::$dragClass, '', $pageData['html']);
        //页面的content用div包起来
        $html = $this->packageContent($html);

        if (!empty($result)) {
            $main = '/<!--\s*geshop\s*main\s*start\s*-->/';
            preg_match($main, $result, $matches);
            if (!empty($matches[0])) {
                $html = str_replace($matches[0], $matches[0] . $html, $result);
            }
        }

        return $html;
    }

    /**
     * 活动页面发布(生成page的html文件)
     *
     * @param        $pageId
     * @param string $lang
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function activityRelease($pageId, $lang)
    {
        ignore_user_abort(true);

        $checkRes = $this->beforeVerifyRelease($pageId);
        if ($checkRes['code']) {
            return $checkRes;
        }

        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = $checkRes['data'];
        $pageModel->status = PageModel::PAGE_STATUS_HAS_ONLINE;
        $pageModel->auto_refresh = PageModel::AUTO_REFRESH;
        $pageModel->verify_user = app()->user->username;
        $pageModel->verify_time = time();

        //页面上线，生成上线文件并推送S3
        if (PageModel::PAGE_STATUS_HAS_ONLINE === $pageModel->status) {
            list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], $pageModel->activity_id,
                $pageModel->pipeline, false, false, false);
            if (!$success) {
                return app()->helper->arrayResult($this->codeFail, '发布失败：页面上线失败', [], $errorMsg);
            }
            //活动上线
            if (false === ActivityModel::changeOnlineActivity(
                    $pageModel->activity_id,
                    PageModel::PAGE_STATUS_HAS_ONLINE)
            ) {
                return app()->helper->arrayResult($this->codeFail, '发布失败：活动上线失败');
            }
        }

        if (false === $pageModel->update(true)) {
            return app()->helper->arrayResult($this->codeFail, '发布失败：操作失败');
        }

        $data = PageModel::getPageUrls($pageModel->group_id, $lang);

        return app()->helper->arrayResult($this->codeSuccess, '发布成功', $data);
    }

    /**
     * 首页面上线(生成page的html文件)
     *
     * @param int    $pageId
     * @param string $lang
     *
     * @return array
     * @throws \yii\db\StaleObjectException
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function homeOnline($pageId, $lang)
    {
        ignore_user_abort(true);

        $checkRes = $this->beforeVerifyRelease($pageId);
        if ($checkRes['code']) {
            return $checkRes;
        }

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            /** @var \app\modules\common\models\PageModel $pageModel */
            $pageModel = $checkRes['data'];
            $pageModel->status = $pageModel::PAGE_STATUS_HAS_PUSH;
            $pageModel->verify_user = app()->user->username;
            $pageModel->verify_time = $_SERVER['REQUEST_TIME'];

            //页面上线，生成上线文件并推送S3
            if ($pageModel::PAGE_STATUS_HAS_PUSH === $pageModel->status) {
                list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], 0, $pageModel->pipeline);
                if (!$success) {
                    return app()->helper->arrayResult($this->codeFail, '首页设置失败：页面上线失败', [], $errorMsg);
                }
            }

            $pageModel->update(true);
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, '首页设置失败：操作失败');
        }
        $data = $pageModel::getPageUrls($pageModel->activity_id, $pageId, $lang);

        return app()->helper->arrayResult($this->codeSuccess, '首页设置成功', $data);
    }

    /**
     * 首页面发布
     *
     * @param $pageId
     *
     * @return array
     */
    public function homeRelease($pageId)
    {
        $pageModel = PageModel::getById($pageId);
        if ($pageModel->status == PageModel::PAGE_STATUS_HAS_ONLINE) {
            $pageModel->status = PageModel::PAGE_STATUS_HAS_ONLINE_UPDATE;
        } else {
            $pageModel->status = PageModel::PAGE_STATUS_HAS_RELEASE;
        }

        $pageModel->verify_user = app()->user->username;
        $pageModel->verify_time = $_SERVER['REQUEST_TIME'];
        if (false === $pageModel->update(true)) {
            return app()->helper->arrayResult($this->codeFail, '发布失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '发布成功');
    }

    /**
     * 页面设置（设置自定义样式等）
     *
     * @param int   $id 页面ID
     * @param array $data
     * @param bool  $runValidation
     *
     * @return array
     * @throws \Throwable
     * @throws \Exception
     */
    public function setting($id, $data, $runValidation = true)
    {
        if (!$id || empty($data['lang']) || empty($data['general'])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }

        /** @var \app\modules\common\models\PageLanguageModel $pageLanguageModel */
        $pageLanguageModel = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $id,
            'lang'                => trim($data['lang'])
        ]);

        $generalData = json_decode($data['general'], true);
        $customData = !empty($data['custom']) ? json_decode($data['custom'], true) : [];
        $generalStyle = [
            'style_type'    => $generalData['style_type'] ?? 0,
            'bg_color'      => !empty($generalData['background_color']) ? trim($generalData['background_color']) : '',
            'bg_image'      => !empty($generalData['background_image']) ? trim($generalData['background_image']) : '',
            'bg_position'   => !empty($generalData['background_position']) ? trim($generalData['background_position']) : '',
            'bg_repeat'     => !empty($generalData['background_repeat']) ? trim($generalData['background_repeat']) : '',
            'custom_css'    => !empty($generalData['custom_css']) ? trim($generalData['custom_css']) : '',
            'bg_attachment' => !empty($generalData['background_attachment']) ? trim($generalData['background_attachment']) : ''
        ];
        $multiTimeStyle = empty($customData['list']) ? [] : $customData['list'];

        !$pageLanguageModel && $pageLanguageModel = new PageLanguageModel();
        $pageLanguageModel->page_id = $id;
        $pageLanguageModel->lang = trim($data['lang']);
        $pageLanguageModel->background_color = $generalStyle['bg_color'];
        $pageLanguageModel->background_image = $generalStyle['bg_image'];
        $pageLanguageModel->background_position = $generalStyle['bg_position'];
        $pageLanguageModel->background_repeat = $generalStyle['bg_repeat'];
        $pageLanguageModel->background_attachment = $generalStyle['bg_attachment'];
        $pageLanguageModel->custom_css = $generalStyle['custom_css'];
        $pageLanguageModel->style_type = $generalStyle['style_type'];
        $pageLanguageModel->multi_time_style = json_encode($multiTimeStyle);

        if (!$pageLanguageModel->save($runValidation)) {
            return app()->helper->arrayResult($this->codeFail, $pageLanguageModel->flattenErrors(', '));
        }

        return app()->helper->arrayResult($this->codeSuccess, '保存成功');
    }

    /**
     * 商品类组件样式设置
     *
     * @param      $id
     * @param      $data
     * @param bool $runValidation
     *
     * @return array
     * @throws JsonResponseException
     */
    public function goodsComponentStyleSetting($id, $data, $runValidation = false)
    {
        if (!$id || empty($data['lang']) || empty($data['style_list'])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }
        
        /** @var \app\modules\common\models\PageLanguageModel $pageLanguageModel */
        $pageLanguageModel = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $id,
            'lang'                => trim($data['lang'])
        ]);
        
        !$pageLanguageModel && $pageLanguageModel = new PageLanguageModel();
        $pageLanguageModel->goods_component_style = $data['style_list'];
        
        if (!$pageLanguageModel->save($runValidation)) {
            return app()->helper->arrayResult($this->codeFail, $pageLanguageModel->flattenErrors(', '));
        }
        
        return app()->helper->arrayResult($this->codeSuccess, '保存成功');
    }
    
    /**
     * 获取页面设置（设置自定义样式等）
     *
     * @param int    $pageId 页面ID
     * @param string $lang   语言代码简称
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getSetting($pageId, $lang)
    {
        if (!$pageId || empty($lang)) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }
        $pageLanguageModel = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $pageId,
            'lang'                => trim($lang)
        ]);

        //只返回部分字段，有些关键信息不能返回
        $data = $pageLanguageModel ? [
            'general' => [
                'style_type'            => $pageLanguageModel->style_type,
                'background_color'      => $pageLanguageModel->background_color,
                'background_image'      => $pageLanguageModel->background_image,
                'background_position'   => $pageLanguageModel->background_position,
                'background_repeat'     => $pageLanguageModel->background_repeat,
                'background_attachment' => $pageLanguageModel->background_attachment,
                'custom_css'            => $pageLanguageModel->custom_css
            ],
            'list'    => json_decode($pageLanguageModel->multi_time_style, true)

        ] : [];

        return app()->helper->arrayResult($this->codeSuccess, '查询成功', $data);
    }
    
    /**
     * 取商品类组件样式数据
     *
     * @param $pageId
     * @param $lang
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getGoodsComponentStyleSetting($pageId, $lang)
    {
        if (!$pageId || empty($lang)) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }
        $data = PageLanguageModel::getGoodsComponentStyle($pageId, trim($lang));
        
        return app()->helper->arrayResult($this->codeSuccess, '查询成功', $data);
    }
    
    /**
     * 复制英文页面SKU
     *
     * @param $data
     *
     * @return array
     * @throws JsonResponseException
     * @throws Exception
     * @throws \Throwable
     */
    public function copySku($data)
    {
        $data = \GuzzleHttp\json_decode($data['data'], true);
        if (empty($data['source']) || empty($data['copy'])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }
        $copy = $data['copy'];
        $source = $data['source'];
        if (empty($source['lang']) || empty($source[ static::FIELD_PAGE_ID ])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }

        $enPage = $this->getPageLayoutAndUiByPageId($source['page_id'], $source['lang']);
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            foreach ($copy['pipeList'] as $page_id => $row) {
                foreach ($row as $lang) {
                    if ($source['page_id'] == $page_id && $lang == $source['lang']) {
                        continue;
                    }
                    $langPage = $this->getPageLayoutAndUiByPageId($page_id, $lang);
                    
                    $this->checkPageSku($enPage, $langPage, ['fromLang' => $source['lang'], 'toLang' => $lang]);
                }
            }
            $tr->commit();
        } catch (Exception $e) {
            $tr->rollBack();
            
            return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: '复制失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '同步成功');
    }

    /**
     * 检查页面商品组件是否满足复制的需求
     *
     * @param array $enPage
     * @param array $langPage
     * @param array $langEqual
     *
     * @throws JsonResponseException
     */
    protected function checkPageSku(array $enPage, array $langPage, array $langEqual)
    {
        list($enLayout, $enUi) = $enPage;
        list($langLayout, $langUi) = $langPage;

        //获取排好序的商品ui组件
        $enUiInOrder = $langUiInOrder = $uiEqualList = [];
        $this->getIncludeSkuUiInOrder($enLayout, $enUi, $enUiInOrder);
        $this->getIncludeSkuUiInOrder($langLayout, $langUi, $langUiInOrder);

        //检测商品组件个数是否相等
        $this->checkSkuUiEqual($enUiInOrder, $langUiInOrder, $uiEqualList);

        //复制SKU
        if (true !== ($copyRes = PageUiComponentDataModel::copySku($uiEqualList, $langEqual))) {
            throw new JsonResponseException($this->codeFail, '同步失败', [], $copyRes);
        }
    }

    /**
     * 获取排好序的包含sku的ui组件
     *
     * @param array $orderedLayouts
     * @param array $uiListByLayoutPosition
     * @param array $uiInOrder
     */
    protected function getIncludeSkuUiInOrder(array $orderedLayouts, array $uiListByLayoutPosition, array &$uiInOrder)
    {
        if (!empty($orderedLayouts)) {
            foreach ($orderedLayouts as $layout) {
                if (!empty($uiListByLayoutPosition[ $layout['id'] ])) {
                    /** @var array[] $uiListByLayoutPosition */
                    foreach ($uiListByLayoutPosition[ $layout['id'] ] as $uiList) {
                        /** @var array $uiList */
                        $uiList = $this->getOrderedComponents($uiList);
                        foreach ($uiList as $ui) {
                            if (!empty(config("soa.gb.uiGoodsInfoMethodMapping.{$ui['component_key']}"))) {
                                $uiInOrder[] = $ui;
                            }
                            
                        }
                    }
                }
            }
        }
    }

    /**
     * 检查SKU的ui是否个数相等
     *
     * @param array $enUiInOrder
     * @param array $langUiInOrder
     * @param array $uiEqualList
     *
     * @throws JsonResponseException
     */
    protected function checkSkuUiEqual(array $enUiInOrder, array $langUiInOrder, array &$uiEqualList)
    {

        if (empty($enUiInOrder)) {
            return false;
            //throw new JsonResponseException($this->codeFail, '英文版页面没有商品组件');
        }

        if (empty($langUiInOrder)) {
            return false;
            //throw new JsonResponseException($this->codeFail, '当前语言页面没有商品组件');
        }

        $hasEqualed = [];
        foreach ($enUiInOrder as $enUi) {
            foreach ($langUiInOrder as $langUi) {
                if ($enUi['component_key'] === $langUi['component_key']
                    && !\in_array($langUi['id'], $hasEqualed, false)
                ) {
                    $uiEqualList[] = [
                        'fromId'    => $enUi['id'],
                        'fromTplId' => $enUi['tpl_id'],
                        'toId'      => $langUi['id'],
                        'toTplId'   => $langUi['tpl_id']
                    ];
                    $hasEqualed[] = $langUi['id'];
                    break;
                }
            }

        }

        if (\count($enUiInOrder) !== \count($langUiInOrder) || \count($enUiInOrder) !== \count($uiEqualList)) {
            throw new JsonResponseException($this->codeFail, '商品列表组件数量不一致，不能同步');
        }
    }


    /**
     * 复制页面(英文的)
     *
     * @param $data
     *
     * @return array
     * @throws JsonResponseException
     * @throws Exception
     * @throws \Throwable
     */
    public function copyPage($data)
    {
        $data = \GuzzleHttp\json_decode($data['data'], true);
        if (empty($data['source']) || empty($data['copy'])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }
        $copy = $data['copy'];
        $source = $data['source'];
        if (empty($source['lang']) || empty($source[ static::FIELD_PAGE_ID ])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }
        //页面样式数据复制
        $oldPage = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $source[ static::FIELD_PAGE_ID ],
            'lang'                => $source['lang']
        ]);
        $enPage = $this->getPageLayoutAndUiByPageId($source['page_id'], $source['lang']);
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            foreach ($copy['pipeList'] as $page_id => $row) {
                foreach ($row as $lang) {
                    if ($source['page_id'] == $page_id && $lang == $source['lang']) {
                        continue;
                    }
                    $newPage = PageLanguageModel::findOne([
                        static::FIELD_PAGE_ID => $page_id,
                        'lang'                => $lang,
                    ]);
                    
                    if (!$oldPage || !$newPage) {
                        throw new JsonResponseException($this->codeFail, '未能找到正确的pageLanguage数据');
                    }
                    
                    $newPage->background_color = $oldPage->background_color;
                    $newPage->background_image = $oldPage->background_image;
                    $newPage->background_position = $oldPage->background_position;
                    $newPage->background_repeat = $oldPage->background_repeat;
                    $newPage->custom_css = $oldPage->custom_css;
                    $newPage->goods_component_style = $oldPage->goods_component_style;
                    
                    $newPage->save();
                    $this->clearComponent($page_id, $lang);
                    $this->copyComponent(['page_id' => $source['page_id'], 'lang' => $source['lang']], ['page_id' => $page_id, 'lang' => $lang]);
                    //复制产品
                    if (!empty($data['copyData'])) {
                        $langPage = $this->getPageLayoutAndUiByPageId($page_id, $lang);
                        $this->checkPageSku($enPage, $langPage, ['fromLang' => $source['lang'], 'toLang' => $lang]);
                    }
                }
            }
            $tr->commit();
        } catch (Exception $e) {
            $tr->rollBack();
            
            return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: '复制失败');
        }
        
        return app()->helper->arrayResult($this->codeSuccess, '复制成功');
        
    }

    /**
     * 清除页面组件
     *
     * @param  int    $pageId 页面ID
     * @param  string $lang   页面语言
     */
    private function clearComponent($pageId, $lang)
    {
        PageLayoutModel::deletePageLayouts($pageId, [$lang]);
    }

    /**
     * 复制页面组件（英语的）
     *
     * @param array $source 源页面数据[page_id,lang]
     * @param array $copy   复制页面数据[page_id,lang]
     *
     * @return bool
     */
    private function copyComponent($source, $copy)
    {
        list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->PageTplComponent->getPageInfo($source['page_id'], $source['lang']);
        //丢弃掉商品标题组合组件
        $this->GoodsManageComponent->discardGoodsTitleCompositeUiComponentWhenPageCopy($layoutInfo, $uiInfo);
        if (empty($layoutInfo) || empty($uiInfo))
            return true;
        $pageArr = [
            'layout'      => json_encode($layoutInfo),
            'layout_data' => json_encode($layoutData),
            'ui'          => json_encode($uiInfo),
            'ui_data'     => json_encode($uiData)
        ];
        Yii::info('组件复制' . var_export($source, true) . var_export($pageArr, true), __METHOD__);
        $this->PageTplComponent->copyPage($copy['page_id'], $pageArr, $copy['lang']);

        return true;

    }

    /**
     * checkLang
     *
     * @param $lang
     *
     * @throws JsonResponseException
     */
    public function checkLang($lang)
    {
        $langConf = app()->params['lang'];
        if (!array_key_exists($lang, $langConf)) {
            throw new JsonResponseException($this->codeFail, '非法的lang参数');
        }
    }

    /**
     * 页面模板-查看页面
     *
     * @param  array $params 参数数组
     *                       ['id']         int    页面模板id
     *                       ['pid']        string 页面32位长度pid
     *                       ['site_code']  string 站点简称
     *                       ['lang']       string 语种
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function look($params)
    {
        list($id, $pid, $siteCode, $lang) = $params;
        if (!isset($id, $pid, $siteCode, $lang)) {
            return app()->helper->arrayResult($this->codeFail, '参数错误');
        }

        $html = $this->getHtmlData($pid, $lang, $id);                        //获取页面模板html数据
        if (\is_array($html)) {
            return $html;
        }

        //生成页面模板
        $this->setPageHtmlToRedis($id, $html);

        app()->response->format = Response::FORMAT_HTML;

        return $html;
    }

    /**
     * 页面模板-查看页面
     *
     * @param int $id
     *
     * @return array
     */
    public function clearCache(int $id)
    {
        $key = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getPageTplRedisKey($id);

        app()->redis->del($key);

        return app()->helper->arrayResult($this->codeSuccess, '清理成功');
    }

    /**
     * 获取页面模板html数据
     *
     * @param string $pid  页面32位长度pid
     * @param string $lang 语种
     * @param int    $id   页面模板id
     *
     * @return mixed
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function getHtmlData($pid, $lang, $id)
    {
        $key = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getPageTplRedisKey($id);

        $html = app()->redis->get($key);
        if (!empty($html)) {
            return $html;
        }

        //获取头尾部
        $result = $this->getHeadAndFooterByTplId($id, $lang);

        //剔除头尾部，只保留样式
        $result = $this->delHeaderAndFooter($result);

        //解析模板
        $page = $this->parsePageTemplate($id);

        //页面预览时，过滤掉可拖拽属性
        $html = str_replace(self::$dragClass, '', $page);

        //页面的content用div包起来
        $html = $this->packageContent($html);

        if (!empty($result)) {
            $main = '/<!--\s*geshop\s*main\s*start\s*-->/';
            preg_match($main, $result, $matches);
            if (!empty($matches[0])) {
                $html = str_replace($matches[0], $matches[0] . $html, $result);
            }
        }

        return $html;
    }

    /**
     * 剔除头尾部
     *
     * @param string $html html数据
     *
     * @return array
     */
    private function delHeaderAndFooter($html)
    {
        preg_match_all('/<header(.*?)<\/header>/ies', $html, $match);
        $css = '<link rel="stylesheet" href="/resources/stylesheets/common/geshop-grid.css?' . date('YmdH') . '">';
        $html = str_replace($match[0], $css, $html);

        preg_match_all('/<footer(.*?)<\/footer>/ies', $html, $match);

        return str_replace($match[0], '', $html);
    }

    /**
     * 页面模板存入redis
     *
     * @param  int    $id   页面模板路径文件
     * @param  string $html html文件数据
     *
     * @return int
     */
    public function setPageHtmlToRedis($id, $html)
    {
        $key = CommonCrontabComponent::REDIS_PREFIX . app()->redisKey->getPageTplRedisKey($id);
        $time = 864000; // 缓存时间 10天

        return app()->redis->setex($key, $time, $html);
    }
    
    /**
     * 同步同渠道下的默认语言装修信息到其他语言
     *
     * @param  int $page_id 页面ID
     *
     * @return bool
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function pipelineDefaultLangToOtherLang($page_id)
    {
        $pageInfo = PageModel::findOne($page_id);
        if (empty($pageInfo)) {
            return true;
        }
        $defaultLang = $pageInfo->defaultLanguage;
        list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->PageTplComponent->getPageInfo($page_id, $defaultLang);
        if (empty($layoutInfo) && empty($uiInfo)) {
            return true;
        }
        $pageList = [];
        $languageList = PageLanguageModel::find()
            ->select('lang')
            ->where(['page_id' => $page_id])
            ->andWhere(['<>', 'lang', $defaultLang])
            ->asArray()->all();
        if (empty($languageList)) {
            return true;
        }
        foreach ($languageList as $item) {
            list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->PageTplComponent->getPageInfo($page_id, $item['lang']);
            if (empty($layoutInfo) && empty($uiInfo) && empty($layoutData) && empty($uiData)) {
                $pageList[] = $item['lang'];
            }
        }
        $oldPage = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $page_id,
            'lang'                => $defaultLang,
        ]);
        $enPage = $this->getPageLayoutAndUiByPageId($page_id, $defaultLang);
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            foreach ($pageList as $lang) {
                $newPage = PageLanguageModel::findOne([
                    static::FIELD_PAGE_ID => $page_id,
                    'lang'                => $lang,
                ]);
                
                if (!$oldPage || !$newPage) {
                    throw new JsonResponseException($this->codeFail, '未能找到正确的pageLanguage数据');
                }
                
                $newPage->background_color = $oldPage->background_color;
                $newPage->background_image = $oldPage->background_image;
                $newPage->background_position = $oldPage->background_position;
                $newPage->background_repeat = $oldPage->background_repeat;
                $newPage->custom_css = $oldPage->custom_css;
                
                $newPage->save();
                $this->clearComponent($page_id, $lang);
                $this->copyComponent(['page_id' => $page_id, 'lang' => $defaultLang], ['page_id' => $page_id, 'lang' => $lang]);
                //复制产品
                $langPage = $this->getPageLayoutAndUiByPageId($page_id, $lang);
                $this->checkPageSku($enPage, $langPage, ['fromLang' => $defaultLang, 'toLang' => $lang]);
                
            }
            $tr->commit();
        } catch (Exception $e) {
            $tr->rollBack();
        }
        
        return true;
    }
}
