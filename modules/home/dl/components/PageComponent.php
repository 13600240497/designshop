<?php

namespace app\modules\home\dl\components;

use app\base\SiteConstants;
use app\modules\common\dl\models\{
    PageModel, PageLanguageModel, PagePublishCacheModel, PagePublishLogModel
};
use app\modules\base\models\AdminModel;
use app\base\Pagination;
use app\base\SitePlatform;
use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;
use app\modules\common\dl\components\CommonPageComponent;

/**
 * 首页组件
 */
class PageComponent extends CommonPageComponent
{
    const ATTR_SITE_CODE   = 'site_code';
    const SITE_CODE_PC     = '-pc';
    const SITE_CODE_MOBILE = '-wap';
    const SITE_CODE_APP    = '-app';

    /**
     * 首页装修列表
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lists(array $params)
    {
        if (empty($params['site_code'])
            || !SitePlatform::isCurrentSiteGroupPlatformSite($params['site_code'])
        ) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        $siteCode = $params['site_code'];
        $query = PageModel::find()->alias('h')
            ->select(
                'h.id, h.pid, a.realname as create_name, h.create_user, h.update_time, h.status, h.is_lock,
                h.site_code, l.page_url, b.realname as update_user,h.create_time'
            )
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'h.id = l.page_id')
            ->leftJoin(AdminModel::tableName() . ' a', 'h.create_user = a.username')
            ->leftJoin(AdminModel::tableName() . ' b', 'h.update_user = b.username')
            ->where([
                'h.is_delete' => PageModel::NOT_DELETE,
                'h.activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
                'h.site_code' => $siteCode
            ])
            ->andWhere(
                ['not in', 'h.status', [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_ONLINE_UPDATE]]
            );

        if (!empty($params['keywords'])) {
            $query->andFilterWhere(['like', 'l.title', $params['keywords']]);
        }
        $query = $query->groupBy('h.id');
        //分页
        $total = $query->count();
        $pagination = Pagination::new($total);
        $pageList = $query->orderBy('h.id DESC')->limit($pagination->limit)->offset($pagination->offset)->all();
        $pageList = $pageList ? ArrayHelper::toArray($pageList) : [];

        $langList = [];
        $_langList = SitePlatform::getSiteHomePageSupportLanguages($siteCode);
        foreach ($_langList as $_langInfo) {
            $langList[] = [
                'key' => $_langInfo['key'],
                'name' => ['code' => $_langInfo['code'], 'name' => $_langInfo['name'],]
            ];
        }

        //组装数据
        if (!empty($pageList) && \is_array($pageList)) {
            $pageIds = array_column($pageList, 'id');
            $pageLangList = PageLanguageModel::find()
                ->where(['page_id' => $pageIds, 'lang' => array_column($langList, 'key')])
                ->orderBy('id ASC')
                ->all();
            $pageLangList = ArrayHelper::toArray($pageLangList);
            $pageList = $this->buildListData($pageList, $pageLangList);
        }

        $data = ['topPage' => [], 'list' => $pageList, 'langList' => $langList, 'total' => $total];
        if (empty($params['keywords']) && ($params['pageNo'] < 2)) {
            $data['topPage'] = $this->getTopPage($langList, $siteCode);
        }

        return app()->helper->arrayResult($this->codeSuccess, 'success', $data);
    }

    /**
     * 获取正在使用的首页
     *
     * @param array  $langList
     * @param string $siteCode
     *
     * @return array
     */
    public function getTopPage($langList, $siteCode)
    {
        $topPage = PageModel::find()->alias('h')
            ->select(
                'h.id, h.pid, a.realname as create_name, h.create_user, h.update_time, h.status, h.is_lock,
                h.site_code, l.page_url, h.create_time,a2.realname as update_user'
            )
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'h.id = l.page_id')
            ->leftJoin(AdminModel::tableName() . ' a', 'h.create_user = a.username')
            ->leftJoin(AdminModel::tableName() . ' a2', 'h.update_user = a2.username')
            ->where(
                [
                    'h.status'      => [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_ONLINE_UPDATE],
                    'h.is_delete'   => PageModel::NOT_DELETE,
                    'h.activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
                    'h.site_code'   => $siteCode
                ]
            )
            ->orderBy('h.id DESC, l.id ASC')
            ->asArray()->one();
        //组装数据
        if (!empty($topPage) && \is_array($topPage)) {
            $pageLangList = PageLanguageModel::find()
                ->where(['page_id' => $topPage['id'], 'lang' => array_column($langList, 'key')])
                ->orderBy('id ASC')
                ->all();
            $pageLangList = ArrayHelper::toArray($pageLangList);
            $topPage = $this->buildListData([$topPage], $pageLangList);
        }

        return empty($topPage) ? [] : $topPage;
    }

    /**
     * 三端合一, 新增首页
     *
     * @param array $params post参数
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function multiPlatformAdd(array $params)
    {
        $validPlatformParams = [];
        //首页活动支持Web端
        $supportPlatforms = [SitePlatform::PLATFORM_CODE_WEB];
        foreach ($supportPlatforms as $platformCode) {
            if (!isset($params[ $platformCode ])) {
                continue;
            }

            $platformParams = json_decode($params[ $platformCode ], true);

            // 检查参数合法性
            $siteCode = SitePlatform::getSiteCodeByPlatformCode($platformCode);
            if (!SitePlatform::isCurrentSiteGroupPlatformSite($siteCode)) {
                $errorMsg = sprintf("无效的应用端口 %s ", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            $platformParams['site_code'] = $siteCode;
            $supportLanguageKeys = SitePlatform::getSiteHomePageSupportLanguageKeys($siteCode);
            foreach ($platformParams['list'] as $langKey => $pageLangInfo) {
                //剔除不支持的语言
                if (!in_array($langKey, $supportLanguageKeys, true)) {
                    unset($platformParams['list'][ $langKey ]);
                    continue;
                }

                if (empty($pageLangInfo['title'])) {
                    $errorMsg = sprintf("应用端口 %s 名称为必填项", SitePlatform::getPlatformNameByCode($platformCode));
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }

                if (empty($pageLangInfo['seo_title'])) {
                    $errorMsg = sprintf("应用端口 %s SEO标题为必填项", SitePlatform::getPlatformNameByCode($platformCode));
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }
            }

            // 目前只有首页的英语是默认必填的
            if (empty($platformParams['list']) || (false === array_key_exists(app()->params['en_lang'], $platformParams['list']))) {
                $errorMsg = sprintf("应用端口 %s 未设置英语语言项", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            $validPlatformParams[ $platformCode ] = $platformParams;
        }

        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '无效的提交数据');
        }

        foreach ($validPlatformParams as $platformCode => $platformParams) {
            $result = $this->platformAdd($platformParams);
            if ($result['code'] == $this->codeFail) {
                $result['message'] = sprintf("应用端口 %s 错误信息: %s", SitePlatform::getPlatformNameByCode($platformCode), $result['message']);

                return $result;
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '添加成功');
    }

    /**
     * 三端合一,新增单个平台首页业务逻辑参考 batchEdit
     *
     * @param array $params 参数
     *
     * @return array
     * @see   \app\modules\home\components\PageComponent::batchEdit()
     * @since v1.4.0
     */
    private function platformAdd(array $params)
    {
        $siteCode = $params['site_code'];

        /** @var \app\modules\common\dl\models\PageModel $pageModel */
        $pageModel = new PageModel();
        $pageModel->create_user = app()->user->admin->username;
        $pageModel->create_time = $_SERVER['REQUEST_TIME'];
        $pageModel->type = $this->getTypeBySiteCode($siteCode);
        $pageModel->site_code = $siteCode;
        $pageModel->activity_id = SiteConstants::HOME_PAGE_ACTIVITY_ID;

        return $this->doHomeBatchEdit($pageModel, $params);
    }

    /**
     * 批量编辑页面属性
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     */
    public function batchEdit(array $params)
    {
        if (empty($params['page_id']) || !is_numeric($params['page_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的page_id');
        }

        if (empty($params['site_code'])) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        $params['list'] = !empty($params['data']) ? \GuzzleHttp\json_decode($params['data'], true) : [];
        if (empty($params['list']) || (false === array_key_exists(app()->params['en_lang'], $params['list']))) {
            throw new JsonResponseException($this->codeFail, '未设置英语语言项');
        }

        foreach ($params['list'] as $pageLangInfo) {
            if (empty($pageLangInfo['title'])) {
                throw new JsonResponseException($this->codeFail, '名称为必填项');
            }
        }

        $siteCode = $params['site_code'];
        $pageModel = PageModel::getById($params['page_id']);
        if (!$pageModel) {
            throw new JsonResponseException($this->codeFail, '页面不存在');
        }

        $pageModel->update_user = app()->user->admin->username;
        $pageModel->update_time = $_SERVER['REQUEST_TIME'];
        $pageModel->type = $this->getTypeBySiteCode($siteCode);
        $pageModel->site_code = $siteCode;
        $pageModel->activity_id = 0;

        return $this->doHomeBatchEdit($pageModel, $params);
    }

    /**
     * 删除首页
     *
     * @param int $id
     *
     * @return array
     */
    public function delete(int $id)
    {
        $model = PageModel::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, '自定义页面不存在');
        }

        //检查页面是否加锁，并判断权限
        if (2 === (int) $model->is_lock && app()->user->admin->is_super < 1) {
            return app()->helper->arrayResult($this->codeFail, '只有页面创建者才具有此权限');
        }

        // 先判断是否在线
        if ($model->status === PageModel::PAGE_STATUS_HAS_ONLINE) {
            return app()->helper->arrayResult(1, '页面仍在线，请先做下线处理');
        }

        return $this->doDelete($model);
    }

    /**
     * 首页加解锁
     *
     * @param int $id
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lock(int $id)
    {
        $model = PageModel::getById($id);
        if (!$model) {
            return app()->helper->arrayResult(1, '自定义页面不存在');
        }

        //检查页面是否加锁，并判断权限
        if (app()->user->admin->is_super < 1 && app()->user->admin->username !== $model['create_user']) {
            return app()->helper->arrayResult($this->codeFail, '只有页面创建者才具有此权限');
        }

        return $this->doLock($model);
    }

    /**
     * 根据站点编码获取活动类型
     *
     * @param string $siteCode 站点编码简称
     *
     * @return int
     */
    private function getTypeBySiteCode($siteCode)
    {
        return SitePlatform::getPlatformTypeBySiteCode($siteCode);
    }

    /**
     * 检查页面是否发布过
     *
     * @param PageModel $pageModel
     * @param array     $data
     *
     * @throws JsonResponseException
     */
    public function checkPagePublished($pageModel, &$data)
    {
        if ($pageModel->activity_id) {
            throw new JsonResponseException($this->codeFail, '活动页请勿调用首页接口', $data);
        }

        if (!in_array($pageModel->status,
            [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_OFFLINE, PageModel::PAGE_STATUS_HAS_ONLINE_UPDATE, PageModel::PAGE_STATUS_HAS_ONLINE_B])
        ) {
            throw new JsonResponseException($this->codeFail, '页面还未设置过为首页，无访问链接', $data);
        }
    }

    /**
     * 获取domainKey
     */
    public function getDomainKey()
    {
        return 'home_secondary_domain';
    }

    /**
     * 获取页面和活动信息
     *
     * @param int $pageId
     *
     * @return PageModel|array|null
     */
    public function getPageActivityInfo(int $pageId)
    {
        return PageModel::findOne($pageId);
    }

    /**
     * 查看版本详情
     *
     * @param int    $pageId
     * @param string $version
     * @param string $lang
     *
     * @return string
     * @throws \yii\base\ViewNotFoundException
     */
    public function viewVersion(int $pageId, string $version, string $lang)
    {
        $log = PagePublishLogModel::findOne(['page_id' => $pageId, 'version' => $version, 'lang' => $lang]);
        if (!$log) {
            return '未找到对应版本的发布记录';
        }

        $cache = PagePublishCacheModel::findOne(['page_id' => $pageId, 'version' => $version, 'lang' => $lang]);
        if (!$cache) {
            return '未找到对应版本的缓存记录';
        }

        $pageHtml = /** @lang html */
            $cache->html . '<style type="text/css">' . $cache->css . '</style>'
            . '<script defer="defer">' . $cache->js . '</script>';

        $cssVersion = app()->params['css_version'];
        $componentStatic['css'] = $this->getHeadExtraCss(
            $pageId,
            $lang,
            $cssVersion,
            $log['site_code'],
            SiteConstants::ACTIVITY_PAGE_TYPE_HOME,
            true
        );
        //解决站点组件JS未加载的问题
        $componentStatic['js'] = $this->getHeadExtraJs(
            $cssVersion,
            $lang,
            $log['site_code'],
            SiteConstants::ACTIVITY_PAGE_TYPE_HOME,
            true
        );

        //获取头尾
        $result = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic);

        //页面预览时，过滤掉可拖拽属性
        $html = str_replace(static::$dragClass, '', $pageHtml);
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
     * 回滚首页版本
     *
     * @param array $params
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidArgumentException
     * @throws JsonResponseException
     */
    public function rollback(array $params)
    {
        $rules = [
            [['site_code', 'page_id', 'version'], 'required'],
            [['site_code', 'version'], 'string'],
            ['page_id', 'integer']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }

        $page = PageModel::findOne([
            'id'          => $params['page_id'],
            'site_code'   => $params['site_code'],
            'activity_id' => 0
        ]);
        if (!$page) {
            throw new JsonResponseException($this->codeFail, '非法的page_id');
        }
        if ($page->is_delete === PageModel::IS_DELETE) {
            throw new JsonResponseException($this->codeFail, '当前页面已被删除，回滚到此版本会导致线上内容无法被管理');
        }

        $currentVersion = PageModel::getHomePageOnlineVersion($params['site_code']);
        if ($params['version'] === $currentVersion) {
            throw new JsonResponseException($this->codeFail, '当前版本已是线上版本，无需回滚');
        }

        $pageLangCount = PageLanguageModel::find()->where(['page_id' => $params['page_id']])->count();
        $cacheCount = PagePublishCacheModel::find()->where(['page_id' => $params['page_id'], 'version' => $params['version']])->count();
        if (!$cacheCount) {
            throw new JsonResponseException($this->codeFail, '未找到对应版本的缓存记录');
        }
        if ($pageLangCount !== $cacheCount) {
            throw new JsonResponseException($this->codeFail, '对应版本缓存语言数和页面语言数不相等');
        }

        list($rollRes, $rollErrors) = $this->rollbackPageHtml($params['page_id'], 0, $params['version']);
        if ($rollRes === false) {
            return app()->helper->arrayResult($this->codeFail, '操作失败', $rollErrors);
        }

        return app()->helper->arrayResult($this->codeSuccess, '操作成功');
    }
}
