<?php

namespace app\modules\common\dl\components;

use app\components\site\dl\PagePreview;
use app\modules\common\dl\models\{
    PageLanguageModel,
    PageModel,
    ActivityModel,
    PageLayoutModel,
    PageUiComponentDataModel,
    PageUiModel,
    PageUiTemplateModel
};

use app\modules\common\dl\traits\{
    CommonVerifyStatusTrait, CommonPublishTrait, CommonConvertTrait
};

use app\base\SiteConstants;
use app\modules\base\components\AccessLogComponent;
use app\modules\component\dl\components\ManagerComponent;
use app\modules\component\models\ComponentModel;
use ego\base\JsonResponseException;
use yii\helpers\ArrayHelper;
use app\base\SitePlatform;
use yii\db\Exception;
use yii\web\Response;

/**
 * 页面装修设计-整个页面相关
 *
 * @property \app\modules\common\dl\components\CommonCrontabComponent $CrontabComponent
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
    const TYPE_PC = ActivityModel::TYPE_PC;
    const TYPE_MOBILE = ActivityModel::TYPE_MOBILE;
    const TYPE_APP = ActivityModel::TYPE_APP;

    /**
     * @var array 包含SKU的ui组件key
     */
    public $uiKeyIncludeSku = ['U000001', 'U000031', 'U000041', 'U000182', 'U000190', 'U000207', 'U000208', 'U000203'];

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
            'activity/dl/design/index',
            'activity/dl/design/preview',
            'activity/dl/design/snapshot',
            'home/dl/design/index',
            'home/dl/design/preview',
            'home/dl/design/snapshot',
            'advertisement/dl/design/index',
            'advertisement/dl/design/preview',
            'advertisement/dl/design/snapshot',
        ];
        $this->changeRefreshIgnoreRoute = [
            'activity/dl/design/index',
            'activity/dl/design/preview',
            'activity/dl/design/release',
            'activity/dl/design/get-setting',
            'activity/dl/layout-design/get-layout-form',
            'activity/dl/ui-design/get-form',
            'home/dl/design/index',
            'home/dl/design/preview',
            'home/dl/design/release',
            'home/dl/design/get-setting',
            'home/dl/layout-design/get-layout-form',
            'home/dl/ui-design/get-form',
        ];

        $moduleId = strtolower(app()->controller->module->module->id);
        if (!empty($_REQUEST[static::FIELD_PAGE_ID]) && \in_array($moduleId, ['activity', 'advertisement'], true)) {
            $this->pageInfo = PageModel::getPageActivityInfo((int)$_REQUEST[static::FIELD_PAGE_ID]);
        }

        if (!empty($_REQUEST[static::FIELD_PAGE_ID]) && 'home' === $moduleId) {
            $this->pageInfo = PageModel::getById((int)$_REQUEST[static::FIELD_PAGE_ID]);
        }

        if (!empty($this->pageInfo)) {
            // 访问日志关联页面id
            AccessLogComponent::addPageId($this->pageInfo->id);
        }
    }

    /**
     * 检查访问权限
     *
     * @param int $pageId 页面ID
     * @param string $route 访问路由
     * @param string $lang 语言
     * @return bool
     * @throws JsonResponseException
     */
    public function checkAuth($pageId, $route, $lang = '')
    {
        if ($this->ignoreCheckRoute($route)) {
            return true;
        }

        if (empty($pageId)) {
            throw new JsonResponseException($this->codeFail, 'page_id不能为空');
        }

        $redisKey = app()->redisKey->getDresslilySiteKey(app()->redisKey->getDesignPageLockKey($pageId, $lang));
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
                . '，请于' . (int)(static::AUTH_CACHE_TIME / 60) . '分钟后再尝试操作';
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
     * @param int $pageId 页面ID
     * @param string $route 访问路由
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
     * 获取用户组件模板列表
     *
     * @param string $username
     * @param string $siteCode
     * @param int $placeType
     * @param string $lang
     */
    public function getUserUiTemplateList($username, $siteCode, $placeType, $lang = NULL)
    {
        //获取模板列表（private 我的模板， public 系统模板）
        return PageUiTemplateModel::getUserTemplateList($username, $siteCode, $placeType, $lang);
    }

    /**
     * 获取页面关联关系
     *
     * @param int $pageId
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
            'list'    => $this->getConvertRelationList($pageId, $lang, $pageActivityInfo->site_code)
        ]);
    }

    /**
     * 预览页面
     *
     * @param string $pid 页面32位ID
     * @param string $lang 语种
     *
     * @param bool $useCache
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

        $pagePreview = new PagePreview();
        return $pagePreview->getDesignPreview($pageModel, $lang, $useCache);
    }

    /**
     * 活动页面发布(生成page的html文件)
     *
     * @param int $pageId
     * @param string $lang
     * @param string $langList
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function activityRelease($pageId, $lang, $type = '', $langList = '')
    {
        ignore_user_abort(true);

        $checkRes = $this->beforeVerifyRelease($pageId);
        if ($checkRes['code']) {
            return $checkRes;
        }

        // 访问日志记录关联页面id
        AccessLogComponent::addPageId($pageId);

        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = $checkRes['data'];
        $pageModel->status = PageModel::PAGE_STATUS_HAS_ONLINE;
        $pageModel->auto_refresh = PageModel::AUTO_REFRESH;
        $pageModel->verify_user = app()->user->username;
        $pageModel->verify_time = time();

        $langList = $langList ? explode(',', $langList) : [];
        //页面上线，生成上线文件并推送S3
        if (PageModel::PAGE_STATUS_HAS_ONLINE === $pageModel->status) {
	        try {
		        defined('PUSHSYNC') or define('PUSHSYNC', app()->swoole->init()->isConnected());
	        } catch (\Exception $e) {
		        app()->rms->reportS3PushError($e->getMessage());
		        defined('PUSHSYNC') or define('PUSHSYNC', false);
	        }
            list($success, $errorMsg) = $this->batchCreateOnlinePageHtml(
                [$pageId], $pageModel->activity_id, false, false, false, $type, SiteConstants::HOME_PAGE_TYPE_UNKNOWN, $langList
            );
            
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

        $data = PageModel::getPageUrls($pageModel->activity_id, $pageModel->id, $lang);
        return app()->helper->arrayResult($this->codeSuccess, '发布成功', $data);
    }

    /**
     * 首页面上线(生成page的html文件)
     *
     * @param int $pageId
     * @param string $lang
     * @param string $langList
     * @return array
     * @throws \yii\db\StaleObjectException
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function homeOnline($pageId, $lang, $langList)
    {
        ignore_user_abort(true);
        set_time_limit(60);

        $checkRes = $this->beforeVerifyRelease($pageId);
        if ($checkRes['code']) {
            return $checkRes;
        }
        
        /** @var \app\modules\common\dl\models\PageModel $pageModel */
        $pageModel = $checkRes['data'];
        if ($pageModel->status === PageModel::PAGE_STATUS_HAS_PUSH) {
            return app()->helper->arrayResult($this->codeFail, '页面当前正在推送中，请稍候');
        }

        $configLanguages = SitePlatform::getSiteHomePageSupportLanguages($pageModel->site_code);
        $supportLangKeys = array_column($configLanguages, 'key');
        $publishLangKes = !empty($langList) ? explode(',', $langList) : $supportLangKeys;

        // 检测是否装修
        $this->checkHomePageDesign($pageModel);

        // 检查第一次发布语是否是所有语言
        $this->checkHomeFirstPublish($pageModel, $publishLangKes);

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            $pageModel->status = $pageModel::PAGE_STATUS_HAS_PUSH;
            $pageModel->verify_user = app()->user->username;
            $pageModel->verify_time = $_SERVER['REQUEST_TIME'];

            //页面上线，生成上线文件并推送S3
            if ($pageModel::PAGE_STATUS_HAS_PUSH === $pageModel->status) {
                // 生成网红首页
                list($kolSuccess, $kolErrorMsg) = $this->batchCreateOnlinePageHtml([$pageId], 0, false, false, false, false, SiteConstants::HOME_PAGE_TYPE_KOL, $publishLangKes);
                if (!$kolSuccess) {
                    return app()->helper->arrayResult($this->codeFail, '首页设置失败：页面上线失败', [], $kolErrorMsg);
                }

                // 生成正式首页
                list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], 0, false, false, false, false, SiteConstants::HOME_PAGE_TYPE_INDEX, $publishLangKes);
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

        $data = $pageModel::getPageUrls($pageModel->activity_id, $pageModel->id, $lang);
        return app()->helper->arrayResult($this->codeSuccess, '首页设置成功', $data);
    }

    /**
     * 首页面上线(生成page的html文件)
     *
     * @param int $pageId
     * @param string $lang
     * @param string $langList
     * @return array
     * @throws \yii\db\StaleObjectException
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function homeOnlineB($pageId, $lang, $langList)
    {
        ignore_user_abort(true);
        set_time_limit(60);

        $checkRes = $this->beforeVerifyRelease($pageId);
        if ($checkRes['code']) {
            return $checkRes;
        }

        /** @var \app\modules\common\dl\models\PageModel $pageModel */
        $pageModel = $checkRes['data'];
        if ($pageModel->status === PageModel::PAGE_STATUS_HAS_PUSH) {
            return app()->helper->arrayResult($this->codeFail, '页面当前正在推送中，请稍候');
        }

        if ($pageModel->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
            return app()->helper->arrayResult($this->codeFail, '正在使用的首页无法设置为首页B');
        }

        $configLanguages = SitePlatform::getSiteHomePageSupportLanguages($pageModel->site_code);
        $supportLangKeys = array_column($configLanguages, 'key');
        $publishLangKes = !empty($langList) ? explode(',', $langList) : $supportLangKeys;

        // 检测是否装修
        $this->checkHomePageDesign($pageModel);

        // 检查第一次发布语是否是所有语言
        $this->checkHomeFirstPublish($pageModel, $publishLangKes);

        //开启事务
        $transaction = app()->db->beginTransaction();
        try {

            $pageModel->status = $pageModel::PAGE_STATUS_HAS_PUSH;
            $pageModel->verify_user = app()->user->username;
            $pageModel->verify_time = $_SERVER['REQUEST_TIME'];

            //页面上线，生成上线文件并推送S3
            if ($pageModel::PAGE_STATUS_HAS_PUSH === $pageModel->status) {
                list($success, $errorMsg) = $this->batchCreateOnlinePageHtml([$pageId], 0, false, false, false, '', SiteConstants::HOME_PAGE_TYPE_INDEX_B, $publishLangKes);
                if (!$success) {
                    return app()->helper->arrayResult($this->codeFail, '首页B设置失败：页面上线失败', [], $errorMsg);
                }
            }

            $pageModel->update(true);
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            return app()->helper->arrayResult($this->codeFail, '首页B设置失败：操作失败');
        }

        $data = $pageModel::getPageUrls($pageModel->activity_id, $pageModel->id, $lang);
        return app()->helper->arrayResult($this->codeSuccess, '首页B设置成功', $data);
    }

    /**
     * 检测页面是否装修
     *
     * @param \app\modules\common\dl\models\PageModel $pageModel
     * @throws JsonResponseException
     */
    private function checkHomePageDesign($pageModel)
    {
        // 获取页面支持的所有语言
        $configLanguages = SitePlatform::getSiteHomePageSupportLanguages($pageModel->site_code);
        $supportLanguages = array_column($configLanguages, NULL, 'key');

        // 获取页面所有语言时候有layout组件
        $rows = PageLayoutModel::find()->select('page_id, lang')
            ->where(['page_id' => $pageModel->id])->groupBy('lang')->indexBy('lang')->asArray()->all();

        $noDesignLanguages = [];
        if (empty($rows)) {
            $noDesignLanguages = array_column($supportLanguages, 'name');
        } else {
            foreach ($supportLanguages as $langCode => $langInfo) {
                if (!isset($rows[$langCode])) {
                    $noDesignLanguages[] = $langInfo['name'];
                }
            }
        }

        if (!empty($noDesignLanguages)) {
            $message = sprintf('%s页面为空页面，所有页面必须不可为空，发布失败。', join('、', $noDesignLanguages));
            throw new JsonResponseException($this->codeFail, $message);
        }

    }

    /**
     * 检测页面是否装修
     *
     * @param \app\modules\common\dl\models\PageModel $pageModel
     * @param array $publishLangKeys 要发布的语言数组
     * @throws JsonResponseException
     */
    private function checkHomeFirstPublish($pageModel, $publishLangKeys)
    {
        // 检查是否第一次发布，要发布所有语言
        $firstPublishStatus = [PageModel::PAGE_STATUS_TO_BE_ONLINE, PageModel::PAGE_STATUS_HAS_RELEASE];
        if (in_array($pageModel->status, $firstPublishStatus, true)) {
            // 获取页面支持的所有语言
            $configLanguages = SitePlatform::getSiteHomePageSupportLanguages($pageModel->site_code);
            $supportLangKeys = array_column($configLanguages, 'key');
            if (!empty(array_diff($supportLangKeys, $publishLangKeys))) {
                throw new JsonResponseException($this->codeFail, '首页第一次发布需要发布所有语言!');
            }
        }
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
            'style_type' => $generalData['style_type'] ?? 0,
            'bg_color' => !empty($generalData['background_color']) ? trim($generalData['background_color']) : '',
            'bg_image' => !empty($generalData['background_image']) ? trim($generalData['background_image']) : '',
            'bg_position' => !empty($generalData['background_position']) ? trim($generalData['background_position']) : '',
            'bg_repeat' => !empty($generalData['background_repeat']) ? trim($generalData['background_repeat']) : '',
            'custom_css' => !empty($generalData['custom_css']) ? trim($generalData['custom_css']) : ''
        ];
        $multiTimeStyle = empty($customData['list']) ? [] : $customData['list'];

        !$pageLanguageModel && $pageLanguageModel = new PageLanguageModel();
        $pageLanguageModel->page_id = $id;
        $pageLanguageModel->lang = trim($data['lang']);
        $pageLanguageModel->background_color = $generalStyle['bg_color'];
        $pageLanguageModel->background_image = $generalStyle['bg_image'];
        $pageLanguageModel->background_position = $generalStyle['bg_position'];
        $pageLanguageModel->background_repeat = $generalStyle['bg_repeat'];
        $pageLanguageModel->custom_css = $generalStyle['custom_css'];
        $pageLanguageModel->style_type = $generalStyle['style_type'];
        $pageLanguageModel->multi_time_style = json_encode($multiTimeStyle);

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
            'general'       => [
                'style_type'            => $pageLanguageModel->style_type,
                'background_color'      => $pageLanguageModel->background_color,
                'background_image'      => $pageLanguageModel->background_image,
                'background_position'   => $pageLanguageModel->background_position,
                'background_repeat'     => $pageLanguageModel->background_repeat,
                'custom_css'            => $pageLanguageModel->custom_css
            ],
            'list' => json_decode($pageLanguageModel->multi_time_style, true)

        ] : [];

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
        if (empty($data['lang']) || empty($data['page_id'])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }

        $enPage = $this->getPageLayoutAndUiByPageId($data['page_id'], app()->params['en_lang']);
        $langPage = $this->getPageLayoutAndUiByPageId($data['page_id'], $data['lang']);

        $this->checkPageSku($enPage, $langPage, ['fromLang' => app()->params['en_lang'], 'toLang' => $data['lang']]);

        return app()->helper->arrayResult($this->codeSuccess, '同步成功');
    }

    /**
     * 检查页面商品组件是否满足复制的需求
     * @param array $enPage
     * @param array $langPage
     * @param array $langEqual
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
     * @param array $orderedLayouts
     * @param array $uiListByLayoutPosition
     * @param array $uiInOrder
     */
    protected function getIncludeSkuUiInOrder(array $orderedLayouts, array $uiListByLayoutPosition, array &$uiInOrder)
    {
        if (!empty($orderedLayouts)) {
            foreach ($orderedLayouts as $layout) {
                if (!empty($uiListByLayoutPosition[$layout['id']])) {
                    /** @var array[] $uiListByLayoutPosition */
                    foreach ($uiListByLayoutPosition[$layout['id']] as $uiList) {
                        /** @var array $uiList */
                        $uiList = $this->getOrderedComponents($uiList);
                        foreach ($uiList as $ui) {
                            if (\in_array($ui['component_key'], $this->uiKeyIncludeSku, true)) {
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
     * @param array $enUiInOrder
     * @param array $langUiInOrder
     * @param array $uiEqualList
     * @throws JsonResponseException
     */
    protected function checkSkuUiEqual(array $enUiInOrder, array $langUiInOrder, array &$uiEqualList)
    {
        if (empty($enUiInOrder)) {
            throw new JsonResponseException($this->codeFail, '英文版页面没有商品组件');
        }

        if (empty($langUiInOrder)) {
            throw new JsonResponseException($this->codeFail, '当前语言页面没有商品组件');
        }

        $hasEqualed = [];
        foreach ($enUiInOrder as $enUi) {
            foreach ($langUiInOrder as $langUi) {
                if ($enUi['component_key'] === $langUi['component_key']
                    && !\in_array($langUi['id'], $hasEqualed, false)) {
                    $uiEqualList[] = [
                        'fromId' => $enUi['id'],
                        'fromTplId' => $enUi['tpl_id'],
                        'toId' => $langUi['id'],
                        'toTplId' => $langUi['tpl_id']
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
        if (empty($data['lang']) || empty($data[ static::FIELD_PAGE_ID ])) {
            throw new JsonResponseException($this->codeFail, '参数不正确');
        }

        //页面样式数据复制
        $oldPage = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $data[ static::FIELD_PAGE_ID ],
            'lang'                => app()->params['en_lang']
        ]);
        $newPage = PageLanguageModel::findOne([
            static::FIELD_PAGE_ID => $data[ static::FIELD_PAGE_ID ],
            'lang'                => $data['lang']
        ]);
        if (!$oldPage || !$newPage) {
            throw new JsonResponseException($this->codeFail, '未能找到正确的pageLanguage数据');
        }

        $newPage->background_color = $oldPage->background_color;
        $newPage->background_image = $oldPage->background_image;
        $newPage->background_position = $oldPage->background_position;
        $newPage->background_repeat = $oldPage->background_repeat;
        $newPage->custom_css = $oldPage->custom_css;
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            $newPage->save();

            $page_layouts = PageLayoutModel::find()->select("id")->where(['page_id'=>$data['page_id'],"lang"=>$data['lang']])->asArray()->all();
            foreach ($page_layouts as $page_layout){
                $ui_ids =  PageUiModel::find()->where(['layout_id' => $page_layout])->asArray()->all();
                $ui_ids = empty($ui_ids) ? [] : array_column($ui_ids,'lang','id');
                foreach ($ui_ids as $id =>$lang){
                    $sync_data['del_info'][] = [
                        'geshop_component_ui_id' => $id
                    ];
                }
            }

            $this->clearComponent($data['page_id'], $data['lang']);
            $this->copyComponent($data['page_id'], $data['lang']);
            $tr->commit();


            //同步删除IPS子活动
            $activity = ActivityModel::getActivityByPageId($data['page_id']);
            $sync_data['geshop_activity_id'] = $activity->id;

            \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);

            //同步创建ips活动
            //查找目标页面需要绑定到ips的组件
            $datas = [];
            $page_layout = PageLayoutModel::find()->where(['page_id' => $data['page_id'],"lang"=>$data['lang']])->asArray()->one();
            $layout_id = $page_layout['id'];
            $lang = $page_layout['lang'];
            $page_ui_lists = PageUiModel::find()->where(['layout_id' => $layout_id])
                ->asArray()->all();
            foreach ($page_ui_lists as $page_ui_list) {
                $page_ui = PageUiModel::findOne($page_ui_list['id']);
                if(empty($page_ui)){
                    continue;
                }
                $ui_datas = PageUiComponentDataModel::find()->where(['component_id'=>$page_ui_list['id'],"tpl_id"=>$page_ui->tpl_id])->asArray()->all();
                $is_need_sync = false;
                $is_ips = false;
                $is_rule = false;
                $ipsFilterInfo = [];
                foreach ($ui_datas as $ui_data){
                    if($ui_data['key']=="goodsDataFrom" &&
                        $ui_data['value'] == 2){
                        $is_ips= true;
                    }
                    if($ui_data['key']=="ipsMethods" &&
                        $ui_data['value'] == 3){
                        $is_rule = true;
                    }
                    if($ui_data['key'] == "ipsFilterInfo"){
                        if(!empty(json_decode($ui_data['value'],1))){
                            $ipsFilterInfo = json_decode($ui_data['value'],1);
                        }
                    }
                }
                $is_need_sync = $is_ips && $is_rule;
                if($is_need_sync && $ipsFilterInfo){
                    //规则选品需同步
                    $tmp['page_id'] = $data['page_id'];
                    $tmp['lang'] = $data['lang'];
                    $tmp['id'] = $page_ui_list['id'];
                    $tmp['tpl_id'] = $page_ui_list['tpl_id'];
                    $tmp['is_auto_activity'] = 2;
                    $tmp['ips_activity_child_id'] = $ipsFilterInfo["ips_activity_child_id"];
                    $datas[] = $tmp;
                }

            }
            \app\modules\common\components\CommonActivityComponent::batchCreateActivityToIps($datas);


            return app()->helper->arrayResult($this->codeSuccess, '复制成功');
        } catch (Exception $e) {
            $tr->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage() ?: '复制失败');
        }
    }

    /**
     * 清除页面组件
     *
     * @param  int      $pageId  页面ID
     * @param  string   $lang    页面语言
     */
    private function clearComponent($pageId, $lang)
    {
        PageLayoutModel::deletePageLayouts($pageId, $lang);
    }

    /**
     * 复制页面组件（英语的）
     * @param int $pageId 页面ID
     * @param string $lang 页面语言
     *
     * @return bool
     */
    private function copyComponent($pageId, $lang)
    {
        list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->PageTplComponent->getPageInfo($pageId, app()->params['en_lang']);
        if (empty($layoutInfo) || empty($uiInfo))
            return true;

        $pageArr = [
            'layout'      => json_encode($layoutInfo),
            'layout_data' => json_encode($layoutData),
            'ui'          => json_encode($uiInfo),
            'ui_data'     => json_encode($uiData)
        ];
        $this->PageTplComponent->copyPage($pageId, $pageArr, $lang);

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

        //获取页面模板html数据
        $pagePreview = new PagePreview();
        $html = $pagePreview->getPageTemplatePreview($id, $pid, $lang);
        if (\is_array($html)) {
            return $html;
        }

        app()->response->format = Response::FORMAT_HTML;
        return $html;
    }

    /**
     * 页面模板-查看页面
     *
     * @param int $id
     * @return array
     */
    public function clearCache(int $id)
    {
        $key = app()->redisKey->getDresslilySiteKey(app()->redisKey->getPageTplRedisKey($id));

        app()->redis->del($key);

        return app()->helper->arrayResult($this->codeSuccess, '清理成功');
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
        $key = app()->redisKey->getDresslilySiteKey(app()->redisKey->getPageTplRedisKey($id));
        $time = 864000; // 缓存时间 10天

        return app()->redis->setex($key, $time, $html);
    }

    /**
     * 设置货币COOKIE用于活动页面设计预览时正常显示商品价格
     */
    public function setCurrencyCookie()
    {
        if (!headers_sent()) {
            $expire = time() + 60*60*24*30; // 30天
            setcookie("bizhong", 'USD', $expire, '/', DOMAIN); // 用于ZF站点
        }
    }
}
