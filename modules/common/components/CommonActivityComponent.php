<?php

namespace app\modules\common\components;

use app\modules\common\models\ActivityModel;
use app\modules\common\models\ActivityGroupModel;
use app\modules\common\models\PageLanguageModel;
use app\modules\common\models\PageModel;
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\modules\base\components\MenuComponent;
use app\modules\common\models\PageUiComponentDataModel;
use app\modules\common\models\PageUiModel;
use app\modules\common\traits\CommonVerifyStatusTrait;
use app\modules\common\traits\CommonPublishTrait;
use app\modules\base\models\AdminModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\soa\models\SoaIpsGoodsModel;
use ego\base\JsonResponseException;
use ego\curl\StandardResponseCurl;
use linslin\yii2\curl\Curl;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\soa\components\ObsComponent;
use app\modules\common\zf\models\PageModel as ZfPageModel;
use app\modules\common\zf\models\ActivityModel as ZfActivityModel;
use app\modules\common\zf\models\PageLanguageModel as ZfPageLanguageModel;
use app\modules\common\dl\models\PageModel as DlPageModel;
use app\modules\common\dl\models\ActivityModel as DlActivityModel;
use app\modules\common\dl\models\PageLanguageModel as DlPageLanguageModel;
use app\modules\common\models\PageModel as RgPageModel;
use app\modules\common\models\ActivityModel as RgActivityModel;
use app\modules\common\models\PageLanguageModel as RgPageLanguageModel;
use app\modules\base\components\AccessLogComponent;

/**
 * 自定义活动组件
 */
class CommonActivityComponent extends Component
{
    use CommonVerifyStatusTrait;
    use CommonPublishTrait;

    const ATTR_SITE_CODE           = 'site_code';
    const DEFAUTE_HOME_TITLE       = 'Home';
    const DEFAUTE_HOME_KEYWORDS    = 'Home';
    const DEFAUTE_HOME_DESCRIPTION = 'This is the description of the homepage.';
    const DEFAUTE_URL_NAME         = 'home';

    /**
     * 活动列表
     *
     * @param array $params       http GET参数
     * @param int   $activityType 活动类型，默认专题活动
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     */
    public function lists($params, $activityType = SiteConstants::ACTIVITY_TYPE_SPECIAL)
    {
        if (empty($params[ static::ATTR_SITE_CODE ])
            || !SitePlatform::isCurrentSiteGroupPlatformSite($params[ static::ATTR_SITE_CODE ])
        ) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        $siteCode = $params[ static::ATTR_SITE_CODE ];
        list($websiteCode, ) = SitePlatform::splitSiteCode($siteCode);
        $allowWebsiteCode = [
            SiteConstants::SITE_GROUP_CODE_RG,
            SiteConstants::SITE_GROUP_CODE_RW,
            SiteConstants::SITE_GROUP_CODE_GB,
            SiteConstants::SITE_GROUP_CODE_SUK,
            SiteConstants::SITE_GROUP_CODE_ZF,
            SiteConstants::SITE_GROUP_CODE_DL,
            'test'
        ];
        if (!in_array($websiteCode, $allowWebsiteCode)) {
            throw new JsonResponseException($this->codeFail, '无效站点，请切换站点后在从管理菜单进入列表!');
        }

        if (!empty($params['searchType']) && $params['searchType'] == 2 && !empty($params['id'])) {//搜索子活动ID
            $pageModel = PageModel::getById($params['id']);
            if (!$pageModel) {
                throw new JsonResponseException($this->codeFail, '无效的子页面');
            }
            $params['id'] = $pageModel->activity_id;
        }

        $activityQuery = ActivityModel::find()->alias('a')
            ->select('a.*, u.realname as create_name, u2.realname as update_user')
            ->leftJoin(AdminModel::tableName() . ' as u', 'a.create_user = u.username')
            ->leftJoin(AdminModel::tableName() . ' as u2', 'a.update_user = u2.username')
            ->where(['a.is_delete' => 0, 'a.mold' => $activityType])
            ->andFilterWhere(['u.realname' => !empty($params['create_name']) ? $params['create_name'] : ''])
            ->andFilterWhere(['like', 'a.name', !empty($params['name']) ? $params['name'] : ''])
            ->andFilterWhere(['a.site_code' => $params[ static::ATTR_SITE_CODE ]])
            ->andFilterWhere(['a.type' => !empty($params['type']) ? $params['type'] : ''])
            ->andFilterWhere(['a.id' => !empty($params['id']) ? intval($params['id']) : ''])
            ->andFilterWhere(['a.is_frequently'=> !empty($params['is_frequently']) ? intval($params['is_frequently']) : '']);

        if (!empty($params['url_name'])) {
            $rows = PageLanguageModel::find()->alias('pl')->select('p.id, p.activity_id')
                ->leftJoin(PageModel::tableName() . ' as p', 'p.id = pl.page_id')
                ->where(['pl.url_name' => $params['url_name']])
                ->groupBy('activity_id')
                ->asArray()
                ->all();
            if (!empty($rows)) {
                $activityIds = array_column($rows, 'activity_id');
                $activityQuery->andWhere(['a.id' => $activityIds]);
            } else {
                $activityQuery->andWhere(['a.id' => -1]);
            }
        }

        $count = $activityQuery->count();
        $pagination = Pagination::new($count);

        $activityList = $activityQuery
            ->groupBy('a.id')
            ->orderBy(['a.id' => SORT_DESC])
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all();

        $data = [];
        if ($activityList) {
            $data = ArrayHelper::toArray($activityList);
            $actIds = array_column($data, 'id');

            //活动分组Map
            $activityGroupMap = [];
            $groupIds = array_filter(array_column($data, 'group_id'));
            if (!empty($groupIds)) {
                $activityGroupList = ActivityGroupModel::getActivityGroupByGroupIds($groupIds);
                $activityGroupMap = array_column(ArrayHelper::toArray($activityGroupList), null, 'id');
            }

            //查询活动页面URL
            $pages = PageLanguageModel::getPageUrlList($actIds);
            $pages = $pages ? array_column($pages, null, 'activity_id') : [];

            //站点配置
            $siteConf = app()->params['sites'][ $params[ static::ATTR_SITE_CODE ] ];

            //组装数据
            if ($data) {
                $this->buildListData([$pages, $siteConf, $activityGroupMap], $data);
            }
            unset($pages, $users, $siteConf);
        }

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'list'       => $data,
                'pagination' => [
                    $pagination->pageParam     => (int) $pagination->page + 1,
                    $pagination->pageSizeParam => (int) $pagination->pageSize,
                    'totalCount'               => (int) $pagination->totalCount
                ]
            ]
        );
    }

    /**
     * 组装list数据
     *
     * @param array $params 参数
     * @param array $data   活动信息数组
     *
     * @throws \yii\base\InvalidArgumentException
     */
    protected function buildListData($params, &$data)
    {
        list($pages, $siteConf, $activityGroupMap) = $params;
        $ObsComponent = new ObsComponent();
        foreach ($data as $i => $item) {
            //获取语言
            $data[ $i ]['langList'] = ActivityModel::getLangListByLangString($item['lang']);
            $lang = current($data[ $i ]['langList'])['key'];

            //分组信息
            $activityGroupInfo = $activityGroupMap[ $item['group_id'] ] ?? [
                    'platform_list' => SitePlatform::getPlatformCodeBySiteCode($item['site_code']),
                    'lang_list'     => $item['lang']
                ];

            $platformList = explode(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform_list']);
            foreach ($platformList as $platformCode) {
                $data[ $i ]['group_info']['platform_list'][] = [
                    'code' => $platformCode,
                    'name' => SitePlatform::getPlatformNameByCode($platformCode)
                ];
            }

            $data[ $i ]['group_info']['lang_list'] = ActivityModel::getLangListByLangString($activityGroupInfo['lang_list']);

            //设置预览地址和二维码
            $data[ $i ]['preview'] = $data[ $i ]['qrcode'] = '';
            if (!empty($pages[ $item['id'] ]['page_url'])) {
                $domain = $siteConf['secondary_domain'][ $lang ] ?? '';
                $data[ $i ]['preview'] = $domain . $pages[ $item['id'] ]['page_url'];
                $data[ $i ]['qrcode'] = Url::to([
                    '/activity/qr-code/create',
                    'url' => $data[ $i ]['preview']
                ], true);
            }
            $data[ $i ]['themeList'] = $ObsComponent->getThemeByActivity($item['id']); //obs选择活动
            //$data[ $i ]['has_page'] = isset($list[ $item['id'] ]) ? 1 : 0; //有无子页面 1-有， 0-无
        }
    }

    /**
     * 三端合一, 添加子页面时关联其他端的活动选择列表
     *
     * @param array $params get参数
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function userActivityList($params)
    {
        if (empty($params[ static::ATTR_SITE_CODE ])
            || !SitePlatform::isCurrentSiteGroupPlatformSite($params[ static::ATTR_SITE_CODE ])
        ) {
            throw new JsonResponseException($this->codeFail, '参数site_code无效');
        }

        if (empty($params['lang'])) {
            throw new JsonResponseException($this->codeFail, '参数lang无效');
        }

        $activityList = ActivityModel::find()->where([
            'is_delete' => ActivityModel::NOT_DELETE,
            'site_code' => $params[ static::ATTR_SITE_CODE ]
        ])
            ->andWhere("FIND_IN_SET(:lang, lang)", [':lang' => $params['lang']])
            ->orderBy('id desc')
            ->all();

        $userActivityList = [];
        if (!empty($activityList)) {
            /** @var \app\modules\common\models\ActivityModel $activityInfo */
            foreach ($activityList as $activityInfo) {
                //过滤掉加锁非本人创建的活动
                $currentUsername = app()->user->username;
                $isLockedOwner = (($activityInfo->is_lock === ActivityModel::IS_LOCK) && ($currentUsername == $activityInfo->create_user))
                    ? true : false;
                if (($activityInfo->is_lock === ActivityModel::UN_LOCK) || $isLockedOwner) {
                    $userActivityList[] = [
                        'id'   => $activityInfo->id,
                        'name' => $activityInfo->name
                    ];
                }
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, 'success', $userActivityList);
    }

    /**
     * 三端合一, 新增活动
     *
     * @param array $params       post参数
     * @param int   $activityType 活动类型，默认专题活动
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupAdd($params, $activityType = SiteConstants::ACTIVITY_TYPE_SPECIAL)
    {
        $activityGroupInfo = [
            'platform' => [],
            'lang'     => []
        ];

        if (empty($params['platform_list'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }

        $missCount = $params['miss_count'] ?? 0;
        $missInfo = ['platform' => [], 'lang' => []];
        $validPlatformParams = [];
        $supportPlatforms = SitePlatform::getAllSupportPlatforms();
        foreach ($supportPlatforms as $platformCode) {
            $platformList = json_decode($params['platform_list'], true);
            if (!isset($platformList[ $platformCode ])) {
                continue;
            }

            $platformParams = array_map('trim', $platformList[ $platformCode ]);
            // 检查参数合法性
            if (empty($platformParams[ static::ATTR_SITE_CODE ])
                || !SitePlatform::isCurrentSiteGroupPlatformSite($platformParams[ static::ATTR_SITE_CODE ])
            ) {
                $errorMsg = sprintf("应用端口 %s 参数site_code无效", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            if (empty($platformParams['name'])) {
                $errorMsg = sprintf("应用端口 %s 名称为必填项", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            //语言字段检查(lang: en,es)
            if (empty($platformParams['lang'])) {
                $errorMsg = sprintf("应用端口 %s 没有选择语言", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            $siteCode = $platformParams[ static::ATTR_SITE_CODE ];

            //剔除不支持的语言
            $postLangKeys = explode(SiteConstants::CHAR_COMMA, $platformParams['lang']);
            $validLangKeys = $this->getPageValidLanguageKeys($siteCode, $postLangKeys);
            $platformParams['lang'] = join(SiteConstants::CHAR_COMMA, $validLangKeys);

            if (empty($platformParams['lang'])) {
                //用户继续忽略提示，继续提交忽略掉没有没有语言信息的活动
                if ($missCount > 1) {
                    continue;
                } else {
                    $missInfo['platform'][] = SitePlatform::getPlatformNameByCode($platformCode);
                    $supportLanguages = $this->getPageSupportLanguages($siteCode);
                    foreach ($supportLanguages as $_langInfo) {
                        $missInfo['lang'][] = $_langInfo['name'];
                    }
                }
            }

            $activityGroupInfo['platform'][] = $platformCode;
            $activityGroupInfo['lang'] = array_merge($activityGroupInfo['lang'], $postLangKeys);
            $validPlatformParams[ $platformCode ] = $platformParams;
        }

        if (!empty($missInfo['platform'])) {
            $missCount++;
            $missInfo['lang'] = array_unique($missInfo['lang']);
            $errorMessage = sprintf("%s端无%s语言，所以不会生成活动呦！", join('/', $missInfo['platform']), join('、', $missInfo['lang']));
            throw new JsonResponseException(101, $errorMessage, ['miss_count' => $missCount]);
        }

        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '没有活动需要生成，请检查提交数据！');
        }

        $activityGroupInfo['lang'] = array_unique($activityGroupInfo['lang']);
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            $groupId = ActivityGroupModel::NO_RELATED_GROUP_ID;
            //如果选择一个以上平台
            if (count($activityGroupInfo['platform']) > 1) {
                $activityGroupModel = new ActivityGroupModel();
                $activityGroupModel->platform_list = join(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform']);
                $activityGroupModel->lang_list = join(SiteConstants::CHAR_COMMA, $activityGroupInfo['lang']);
                if (!$activityGroupModel->insert(true)) {
                    throw new Exception('添加活动分组失败');
                }
                $groupId = $activityGroupModel->id;
            }

            //保存数据
            foreach ($validPlatformParams as $platformCode => $platformParams) {
                $platformParams['group_id'] = $groupId;
                $platformParams['mold'] = $activityType; //活动类型
                $this->addActivity($platformParams);
            }

            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '添加成功');
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, sprintf('添加失败,错误：%s', $e->getMessage()));
        }

    }

    /**
     * 获取活动有效支持语言简码
     *
     * @param string $siteCode
     * @param array  $postLangKeys
     *
     * @return array
     */
    protected function getPageValidLanguageKeys($siteCode, $postLangKeys)
    {
        return SitePlatform::getSiteSpecialPageValidLanguageKeys($siteCode, $postLangKeys);
    }

    /**
     * 获取活动支持语言简码
     *
     * @param string $siteCode
     *
     * @return array
     */
    protected function getPageSupportLanguages($siteCode)
    {
        return SitePlatform::getSiteSpecialPageSupportLanguages($siteCode);
    }

    /**
     * 三端合一, 编辑活动并同步其他端
     *
     * @param array $params POST数据
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupEdit($params)
    {
        if (empty($params['id']) || !is_numeric($params['id'])) {
            throw new JsonResponseException($this->codeFail, '无效的参数id');
        }

        if (empty($params['name'])) {
            throw new JsonResponseException($this->codeFail, '活动名称不能空');
        }

        $activityId = $params['id'];
        /** @var \app\modules\common\models\ActivityModel $model */
        $model = ActivityModel::getById($activityId);
        if ((!$model) || ActivityModel::IS_DELETE === (int) $model->is_delete) {
            throw new JsonResponseException($this->codeFail, '活动不存在');
        }

        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($model)) {
            return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
        }

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            $activityModelList = [$model];
            if ($model->group_id > 0) {
                $activityModelList = ActivityModel::getActivitiesByGroupId($model->group_id);
            }

            /** @var \app\modules\common\models\ActivityModel $activityModel */
            foreach ($activityModelList as $activityModel) {
                if ($activityModel->is_delete === ActivityModel::IS_DELETE)
                    continue;

                $activityModel->name = $params['name'];
                $activityModel->description = $params['description'];
                if (false === $activityModel->update(true)) {
                    throw new Exception($model->flattenErrors(', '));
                }
                if (isset($params['obsId']) && isset($params['obsName'])) {
                    $obsComponent = new ObsComponent();
                    $obsComponent->saveActivity($params['obsId'], $activityModel->id, $params['obsName']); //更新obs
                }
            }


            $transaction->commit();

            foreach ($activityModelList as $_activityModel) {
                if ($_activityModel->is_delete === ActivityModel::IS_DELETE)
                    continue;

                $platformCode = SitePlatform::getPlatformCodeBySiteCode($_activityModel->site_code);
                $platformName = SitePlatform::getPlatformNameByCode($platformCode);

                //修改活动信息需要同步到IPS
                $sync_data['geshop_activity_id'] = $_activityModel->id;
                $sync_data['geshop_activity_name'] = $params['name']."_".$platformName;
                $sync_data['activity_des'] = $params['description'];
                \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);
            }

            return app()->helper->arrayResult($this->codeSuccess, '修改成功');
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, sprintf('修改失败,错误：%s', $e->getMessage()));
        }

    }

    /**
     * 添加单个活动
     *
     * @param array   $data          活动数据
     * @param boolean $runValidation 添加数据时是否验证数据
     *
     * @throws \yii\base\Exception
     * @since v1.4.0
     */
    private function addActivity($data, $runValidation = true)
    {
        if (empty($data[ self::ATTR_SITE_CODE ])) {
            throw new Exception('没有设置site_code');
        }
        unset($data['id']);

        $siteCode = $data[ self::ATTR_SITE_CODE ];
        $model = new ActivityModel();
        $model->load($data, '');

        $model->type = $this->getTypeBySiteCode($siteCode);

        //状态值初始化
        $model->status = ActivityModel::STATUS_TO_BE_ONLINE;
        $model->verify_status = ActivityModel::VERIFY_STATUS_NOT_COMMIT;
        $model->is_delete = ActivityModel::NOT_DELETE;
        $model->is_lock = ActivityModel::UN_LOCK;

        if (!$model->insert($runValidation)) {
            throw new Exception($model->flattenErrors(', '));
        }

        if (isset($data['obsId']) && isset($data['obsName'])) {
            $activity_id = $model->id;
            $obsComponent = new ObsComponent();
            $obsComponent->saveActivity($data['obsId'], $activity_id, $data['obsName']);
        }

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
     * 删除自定义活动(同时删除活动下页面)
     *
     * @param int  $id
     * @param bool $runValidation
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id, $runValidation = true)
    {
        $model = ActivityModel::getById($id);

        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($model)) {
            return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
        }

        if (!$model) {
            throw new JsonResponseException($this->codeFail, '自定义活动不存在');
        }

        //先判断是否在线
        if ($model->status === ActivityModel::STATUS_HAS_ONLINE) {
            throw new JsonResponseException($this->codeFail, '活动仍在线，请先做下线处理');
        }

        //只修改状态
        $model->is_delete = ActivityModel::IS_DELETE;
        if (false === $model->update($runValidation)) {
            throw new JsonResponseException($this->codeFail, '删除失败');
        }

        // 访问日志记录关联页面id
        $pageIds = PageModel::find()->select('id')->where(['activity_id' => $id])->column();
        AccessLogComponent::addPageId($pageIds);

        //删除活动同时删除活动下的页面（删除只是修改是否删除状态值）
        PageModel::updateAll(['is_delete' => PageModel::IS_DELETE], ['activity_id' => $id]);
        ObsComponent::deleteActivity($id); //删除活动需要删除obs关联关系

        //同步删除IPS活动
        $sync_data['geshop_activity_id'] = $id;
        $sync_data['del_info'] = [[
            'geshop_activity_id' => $id
        ]];
        \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);

        return app()->helper->arrayResult(0, '删除成功');
    }

    /**
     * 设置/取消常用活动
     *
     * @param int $id 活动ID
     * @return array
     * @throws JsonResponseException
     */
    public function frequently($id)
    {
        $model = ActivityModel::getById($id);
        if (!$model) {
            throw new JsonResponseException($this->codeFail, '活动不存在');
        }

        // 检查活动是否加锁，并判断权限
        if (1 === (int)$model->is_lock && app()->user->admin->username !== $model->create_user) {
            return app()->helper->arrayResult($this->codeFail, '活动锁定没有此权限');
        }

        if (empty($model->is_frequently)) {
            $model->is_frequently = 1;
        } else {
            $model->is_frequently = 0;
        }

        if (!$model->save()) {
            return app()->helper->arrayResult($this->codeFail, '更新活动常用状态失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, 'ok');
    }

    /**
     * 活动审核(status可为2/4)
     *
     * @param int  $activityId 活动ID
     * @param int  $status     活动要变更的状态
     * @param bool $runValidation
     *
     * @return array
     * @throws \yii\db\StaleObjectException
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function verify($activityId, $status, $runValidation = true)
    {
        ignore_user_abort(true);

        //提交审核状态值检查
        $checkRes = $this->beforeVerifyActivity((int) $activityId, (int) $status);
        if ($checkRes['code']) {
            return $checkRes;
        }

        /** @var \app\modules\common\models\ActivityModel $model */
        $model = $checkRes['data']['model'];
        $model->status = $status;
        $verifyTime = time();
        $model->verify_user = app()->user->username;
        $model->verify_time = $verifyTime;

        //若活动要下线，需要将已上线的页面也下线
        if (ActivityModel::STATUS_HAS_OFFLINE === $model->status) {
            $pageIds = PageModel::find()->select('id')->where([
                'activity_id' => $activityId,
                'status'      => [
                    PageModel::PAGE_STATUS_HAS_ONLINE
                ],
                'is_delete'   => PageModel::NOT_DELETE
            ])->asArray()->all();
            if ($pageIds) {
                $pageIds = array_column($pageIds, 'id');
                // 访问日志记录关联页面id
                AccessLogComponent::addPageId($pageIds);

                if (PageModel::updateAll([
                    'status'        => PageModel::PAGE_STATUS_HAS_OFFLINE,
                    'verify_status' => PageModel::VERIFY_STATUS_PASS_TO_OFFLINE,
                    'verify_user'   => app()->user->username,
                    'verify_time'   => $verifyTime
                ], ['id' => $pageIds])
                ) {
                    list($success, $data) = $this->batchCreateOfflinePageHtml($pageIds, $activityId);
                    if (!$success) {
                        return app()->helper->arrayResult(1, '页面下线所需的跳转HTML文件生成失败，请重试', $data);
                    }
                }
            }
        }

        if (false === $model->update($runValidation)) {
            return app()->helper->arrayResult(2, '操作失败');
        }

        return app()->helper->arrayResult(0, '操作成功');
    }

    /**
     * 获取活动信息
     *
     * @param int $id
     *
     * @return array
     * @throws JsonResponseException
     */
    public function get($id)
    {
        $data = ActivityModel::getActivityInfo((int) $id);
        if (!$data) {
            throw new JsonResponseException($this->codeFail, '活动不存在或已被删除');
        }

        return app()->helper->arrayResult(0, 'success', $data);
    }

    /**
     * 活动权限加/解锁
     *
     * @param  \app\modules\common\models\ActivityModel $model
     * @param bool                                      $runValidation
     *
     * @return array
     * @throws JsonResponseException
     */
    public function doLock($model, $runValidation = true)
    {
        if (0 === (int) $model->is_lock) {
            $actMsg = '加锁';
            $model->is_lock = ActivityModel::IS_LOCK;
        } else {
            $actMsg = '解锁';
            $model->is_lock = ActivityModel::UN_LOCK;
        }

        if (false === $model->update($runValidation)) {
            throw new JsonResponseException($this->codeFail, "{$actMsg}失败");
        }

        return app()->helper->arrayResult(0, "{$actMsg}成功");
    }

    /**
     * 到ips创建活动
     * @param array $params
     * @return array
     * @author yuanwenguang 2019/4/17 10:53
     */
    public static function createActivityToIps($params =[]){
        $page_id = empty($params['page_id']) ? null : $params['page_id']; // 页面id
        $lang = empty($params['lang']) ? null : $params['lang']; //语言
        $component_id = empty($params['id']) ? null : $params['id']; //组件位置id
        $tpl_id = empty($params['tpl_id']) ? null : $params['tpl_id'];//组件id
        $is_auto_activity = empty($params['is_auto_activity']) ? 2 : $params['is_auto_activity'];//是否自动选品,1手动，2自动，默认自动
        $site_code = SitePlatform::getCurrentSiteGroupCode();
        $site_code = strtolower($site_code);
        $page_model = null;
        $activity_model = null;
        $page_language_model = null;
        if($site_code == SiteConstants::SITE_GROUP_CODE_ZF || $site_code == SiteConstants::SITE_GROUP_CODE_RG){
            //zf
            $page_model =  ZfPageModel::findOne($page_id);
            if(!empty($page_model)){
                $activity_model = ZfActivityModel::findOne($page_model->activity_id);
            }
            if(!empty($page_model)){
                $page_language_model = ZfPageLanguageModel::findOne(["page_id"=>$page_id,"lang"=>$lang]);
            }
        }elseif($site_code == SiteConstants::SITE_GROUP_CODE_DL){
            //dl
            $page_model = DlPageModel::findOne($page_id);
            if(!empty($page_model)){
                $activity_model = DlActivityModel::findOne($page_model->activity_id);
            }
            if(!empty($page_model)){
                $page_language_model = DlPageLanguageModel::findOne(["page_id"=>$page_id,"lang"=>$lang]);
            }
        }elseif ($site_code == SiteConstants::SITE_GROUP_CODE_RG){
            //rg
            $page_model = RgPageModel::findOne($page_id);
            if(!empty($page_model)){
                $activity_model = RgActivityModel::findOne($page_model->activity_id);
            }
            if(!empty($page_model)){
                $page_language_model = RgPageLanguageModel::findOne(["page_id"=>$page_id,"lang"=>$lang]);
            }
        }else{
            return app()->helper->arrayResult(1, "暂未支持{$site_code}站点");
        }
        if(empty($page_model) || empty($activity_model) || empty($page_language_model)){
            return app()->helper->arrayResult(1, "未找到页面或者活动");
        }

        $platfrom = SitePlatform::getPlatformNameByCode(explode('-',$page_model->site_code)[1]);;
        $ui_tpl = UiTplModel::find()->alias('tpl')
            ->select('tpl.*, ui.name as ui_name, ui.component_key')
            ->leftJoin(UiModel::tableName() . ' as ui', 'tpl.component_key = ui.component_key')
            ->where([
                'tpl.id'        => $tpl_id,
                'tpl.is_delete' => UiTplModel::NOT_DELETE,
                'ui.is_delete'  => UiModel::NOT_DELETE
            ])->asArray()->one();
        if(!empty($ui_tpl['ui_name'])){
            $geshop_component_name = $ui_tpl['ui_name'];
            if(!empty($ui_tpl['name'])){
                $geshop_component_name .= "-".$ui_tpl['name'];
            }
        }else{
            return app()->helper->arrayResult(1, "模板不存在");
        }
        $ips_params = [
            "geshop_activity_id" => $page_model->activity_id,
            "geshop_activity_name" => $activity_model->name."_".$platfrom,
            "site_code" => strtoupper($site_code),
            "activity_des" => $activity_model->description,
            "geshop_activity_child_id" => $page_id,
            "geshop_activity_child_name" =>isZufulSite($site_code) ? $page_language_model->title."_".$page_model->pipeline : $page_language_model->title."_".$lang,
            "geshop_offline_time" => empty($page_model->end_time) ? 365 * 24 * 3600 + time() : $page_model->end_time,
            "geshop_component_ui_id" => $component_id,
            "geshop_component_name" => $geshop_component_name,
            "create_user" => $page_model->create_user,
            "update_user" => $page_model->update_user,
            "is_auto_activity" => $is_auto_activity, //自动选品
            'page_id' => $page_id,
            'lang' => $lang,
            'component_key' => $ui_tpl['component_key']
        ];

        //处理rg，D网多语言情况
        if(isDresslilySite($site_code)){
            $ips_params['geshop_activity_child_id'] = $page_id . "_" . $lang;
        }elseif (isZufulSite($site_code)){

        }else{
            //rg
            $ips_params['geshop_activity_child_id'] = $page_id . "_" . $lang;
        }


        $token_data = json_encode($ips_params);
        $post_data['sn'] = app()->params['soa']['ips']['sn'];
        $post_data["token"] = md5(app()->params['soa']['ips']['key'] . $token_data . $post_data['sn']);
        $post_data['data'] = $token_data;

        $url = app()->params['soa']['ips']['apiUrlPrefix']."/activity-child/receive-geshop-activity";
        $curl = new StandardResponseCurl();
        $response = $curl->slient()->asArray()->request(
            "POST",
            $url,
            ['form_params' => $post_data]
        );

        if($response["code"] == 0){
            $activity_child_id = $response["data"]['activity_child_id'];
            $activity_id = $response['data']['activity_id'];
            if(!empty($activity_child_id) && !empty($activity_id)){
                if($is_auto_activity == 1){
                    $url = app()->params['soa']['ips']['host']."/frontend/filter/puzzling?activityChild=".$activity_child_id."&activitymain=".$activity_id."&from=geshop";
                }else{
                    //
                    $url = app()->params['soa']['ips']['host']."/frontend/auto-select-rule?id=".$activity_child_id."&from=geshop";
                }

                //需要绑定geshop和ips活动id
                $soa_ips_goods_model = SoaIpsGoodsModel::findOne([
                        'ips_activity_id' => $activity_child_id,
                        'website_code' => $site_code,
                        'page_id' => $page_id,
                        'lang' => $lang,
                        'component_id' => $component_id
                    ]
                );

                if(empty($soa_ips_goods_model)){
                    //空的话，则需要新增
                    $soa_ips_goods_model = new SoaIpsGoodsModel();
                    $soa_ips_goods_model->ips_activity_id = $activity_child_id;
                    $soa_ips_goods_model->website_code = $site_code;
                    $soa_ips_goods_model->page_id = $page_id;
                    $soa_ips_goods_model->lang = $lang;
                    $soa_ips_goods_model->component_id = $component_id;
                    $soa_ips_goods_model->component_key = $ui_tpl['component_key'];
                    $soa_ips_goods_model->goods_sku = '';
                    $soa_ips_goods_model->last_update_time = time();
                }

                $soa_ips_goods_model->rule_type = $is_auto_activity == 2 ? SoaIpsGoodsModel::RULE_TYPE_IPS_RULE : SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER;
                $soa_ips_goods_model->save();

                return app()->helper->arrayResult(0, "成功",["url"=>$url,"ips_activity_child_id"=>$activity_child_id]);
            }else{
                return app()->helper->arrayResult(1, "ips系统错误");
            }
        }else{
            return app()->helper->arrayResult(1,"ips返回错误信息：".$response['message']);
        }
    }
    /**
     * 批量到ips创建活动
     * @param array $params
     * @return array
     * @author yuanwenguang 2019/4/17 10:53
     */
    public static function batchCreateActivityToIps($params_data =[]){
        $ips_params = [];
        $site_code = SitePlatform::getCurrentSiteGroupCode();
        $site_code = strtolower($site_code);
        if($site_code != SiteConstants::SITE_GROUP_CODE_DL && $site_code != SiteConstants::SITE_GROUP_CODE_ZF && $site_code != SiteConstants::SITE_GROUP_CODE_RG){
            return app()->helper->arrayResult(1, "暂未支持{$site_code}站点");
        }

        $api_params = [];
        foreach ($params_data as $params){
            $page_id = empty($params['page_id']) ? null : $params['page_id']; // 页面id
            $lang = empty($params['lang']) ? null : $params['lang']; //语言
            $component_id = empty($params['id']) ? null : $params['id']; //组件位置id
            $tpl_id = empty($params['tpl_id']) ? null : $params['tpl_id'];//组件id
            $is_auto_activity = empty($params['is_auto_activity']) ? 2 : $params['is_auto_activity'];//是否自动选品,1手动，2自动，默认自动
            $ips_activity_child_id = empty($params['ips_activity_child_id']) ? null : $params['ips_activity_child_id'];//之前存的ips子活动id
            $page_model = null;
            $activity_model = null;
            $page_language_model = null;
            if($site_code == SiteConstants::SITE_GROUP_CODE_ZF || $site_code == SiteConstants::SITE_GROUP_CODE_RG){
                //zf
                $page_model =  ZfPageModel::findOne($page_id);
                if(!empty($page_model)){
                    $activity_model = ZfActivityModel::findOne($page_model->activity_id);
                }
                if(!empty($page_model)){
                    $page_language_model = ZfPageLanguageModel::findOne(["page_id"=>$page_id,"lang"=>$lang]);
                }
            }elseif($site_code == SiteConstants::SITE_GROUP_CODE_DL){
                //dl
                $page_model = DlPageModel::findOne($page_id);
                if(!empty($page_model)){
                    $activity_model = DlActivityModel::findOne($page_model->activity_id);
                }
                if(!empty($page_model)){
                    $page_language_model = DlPageLanguageModel::findOne(["page_id"=>$page_id,"lang"=>$lang]);
                }
            }elseif ($site_code == SiteConstants::SITE_GROUP_CODE_RG){
                //rg
                $page_model = RgPageModel::findOne($page_id);
                if(!empty($page_model)){
                    $activity_model = RgActivityModel::findOne($page_model->activity_id);
                }
                if(!empty($page_model)){
                    $page_language_model = RgPageLanguageModel::findOne(["page_id"=>$page_id,"lang"=>$lang]);
                }
            }
            if(empty($page_model) ){
                $messages[] = "页面id：" . $page_id . " 未找到页面";
                continue;
            }
            if(empty($activity_model) ){
                $messages[] = "活动id：" .$page_model->activity_id  . " 未找活动";
                continue;
            }
            if(empty($page_language_model)){
                $messages[] = "页面id：" . $page_id ."语言：" . $lang . " 未找到页面或者活动";
                continue;
            }
            $platfrom = SitePlatform::getPlatformNameByCode(explode('-',$page_model->site_code)[1]);

            $ui_tpl = UiTplModel::find()->alias('tpl')
                ->select('tpl.*, ui.name as ui_name, ui.component_key')
                ->leftJoin(UiModel::tableName() . ' as ui', 'tpl.component_key = ui.component_key')
                ->where([
                    'tpl.id'        => $tpl_id,
                    'tpl.is_delete' => UiTplModel::NOT_DELETE,
                    'ui.is_delete'  => UiModel::NOT_DELETE
                ])->asArray()->one();
            if(!empty($ui_tpl['ui_name'])){
                $geshop_component_name = $ui_tpl['ui_name'];
                if(!empty($ui_tpl['name'])){
                    $geshop_component_name .= "-".$ui_tpl['name'];
                }
            }else{
                $messages[] = "模板id：" . $tpl_id  . " 模板不存在";
                continue;
            }
             $ips_params = [
                "geshop_activity_id" => $page_model->activity_id,
                "geshop_activity_name" => $activity_model->name."_".$platfrom,
                "site_code" => strtoupper($site_code),
                "activity_des" => $activity_model->description,
                "geshop_activity_child_id" => $page_id,
                "geshop_activity_child_name" => isZufulSite($site_code) ? $page_language_model->title."_".$page_model->pipeline : $page_language_model->title."_".$lang,
                "geshop_offline_time" => empty($page_model->end_time) ? 365 * 24 * 3600 + time() : $page_model->end_time,
                "geshop_component_ui_id" => $component_id,
                "geshop_component_name" => $geshop_component_name,
                "create_user" => $page_model->create_user,
                "update_user" => $page_model->update_user,
                "is_auto_activity" => $is_auto_activity,//自动选品
                'page_id' => $page_id,
                'lang' => $lang,
                'component_key' => $ui_tpl['component_key'],
                 'ips_activity_child_id' => $ips_activity_child_id,
            ];
            //处理rg，D网多语言情况
            if(isDresslilySite($site_code)){
                $ips_params['geshop_activity_child_id'] = $page_id . "_" . $lang;
            }elseif (isZufulSite($site_code)){

            }else{
                //rg
                $ips_params['geshop_activity_child_id'] = $page_id . "_" . $lang;
            }

            $api_params[] = $ips_params;
        }

        $post_data['data']['method'] = 2;
        $post_data['data']['api_data'] = $api_params;
        $token_data = json_encode($post_data['data']);
        $post_data['sn'] = app()->params['soa']['ips']['sn'];
        $post_data["token"] = md5(app()->params['soa']['ips']['key'] . $token_data . $post_data['sn']);
        $post_data['data'] = $token_data;
        $url = app()->params['soa']['ips']['apiUrlPrefix']."/activity-child/receive-geshop-activity";
        $curl = new StandardResponseCurl();
        $response = $curl->slient()->asArray()->request(
            "POST",
            $url,
            ['form_params' => $post_data]
        );
        if($response["code"] == 0){
            $response_datas = $response['data'];
            foreach ($response_datas as $response_data){
                foreach ($api_params as $api_param){
                    if($response_data['geshop_component_ui_id'] ==
                        $api_param['geshop_component_ui_id']){
                        //找到组件id对应
                        //需要绑定geshop和ips活动id
                        $soa_ips_goods_model = SoaIpsGoodsModel::findOne([
                                'ips_activity_id' => $response_data['ips']['activity_id'],
                                'website_code' => $site_code,
                                'page_id' => $api_param['page_id'],
                                'lang' => $api_param['lang'],
                                'component_id' => $api_param['geshop_component_ui_id']
                            ]
                        );

                        if(empty($soa_ips_goods_model)){
                            //空的话，则需要新增
                            $soa_ips_goods_model = new SoaIpsGoodsModel();
                            $soa_ips_goods_model->ips_activity_id = $response_data['ips']['activity_child_id'];
                            $soa_ips_goods_model->website_code = $site_code;
                            $soa_ips_goods_model->page_id = $api_param['page_id'];
                            $soa_ips_goods_model->rule_type = $api_param['is_auto_activity'] == 2 ? SoaIpsGoodsModel::RULE_TYPE_IPS_RULE : SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER;
                            $soa_ips_goods_model->lang = $api_param['lang'];
                            $soa_ips_goods_model->component_id = $api_param['geshop_component_ui_id'];
                            $soa_ips_goods_model->component_key = $api_param['component_key'];
                            $soa_ips_goods_model->goods_sku = '';
                            $soa_ips_goods_model->last_update_time = time();
                            $soa_ips_goods_model->save();
                        }
                        //同步修改组件的数据
                        if(isZufulSite($site_code)){
                           $page = \app\modules\common\zf\models\PageUiModel::findOne($api_param['geshop_component_ui_id']);
                           if(!empty($page)){
                               $page_ui_component_datas = \app\modules\common\zf\models\PageUiComponentDataModel::find()->where(['component_id'=>$api_param['geshop_component_ui_id'],'tpl_id'=>$page->tpl_id])->asArray()->all();
                               $is_ips = false;
                               $is_auto = false;
                               foreach ($page_ui_component_datas as $page_ui_component_data){
                                   if($page_ui_component_data['key'] == "goodsDataFrom" && $page_ui_component_data['value']== "2"){
                                       $is_ips = true;
                                   }
                                   if($page_ui_component_data['key'] == "ipsMethods" && $page_ui_component_data['value'] == "3"){
                                       $is_auto = true;
                                   }
                               }

                               if($is_ips && $is_auto){
                                   //需要修改ips_activity_child_id值

                                   if(!empty($page)){
                                       $ui_data_model = \app\modules\common\zf\models\PageUiComponentDataModel::find()->where(['component_id'=>$api_param['geshop_component_ui_id'],"key"=>"ipsFilterInfo","tpl_id"=>$page->tpl_id])->one();
                                       if(!empty($ui_data_model) && isset($response_data['ips']['activity_child_id'])){
                                           $ui_data_ips_filter_info = json_decode($ui_data_model->value,1);
                                           $ui_data_ips_filter_info['ips_activity_child_id'] = $response_data['ips']['activity_child_id'];
                                           $ui_data_ips_filter_info = json_encode($ui_data_ips_filter_info);
                                           $ui_data_model->value = $ui_data_ips_filter_info;
                                           $ui_data_model->save();
                                       }
                                   }
                               }
                           }

                        }
                        //同步修改组件的数据
                        elseif(isDresslilySite($site_code)){
                            $page = PageUiModel::findOne($api_param['geshop_component_ui_id']);
                            if(!empty($page)){
                                $page_ui_component_datas = \app\modules\common\dl\models\PageUiComponentDataModel::find()->where(['component_id'=>$api_param['geshop_component_ui_id'],"tpl_id"=>$page->tpl_id])->asArray()->all();
                                $is_ips = false;
                                $is_auto = false;
                                foreach ($page_ui_component_datas as $page_ui_component_data){
                                    if($page_ui_component_data['key']== "goodsDataFrom" && $page_ui_component_data['value']== "2"){
                                        $is_ips = true;
                                    }
                                    if($page_ui_component_data['key'] == "ipsMethods" && $page_ui_component_data['value'] == "3"){
                                        $is_auto = true;
                                    }
                                }

                                if($is_ips && $is_auto){
                                    //需要修改ips_activity_child_id值
                                        $ui_data_model = \app\modules\common\dl\models\PageUiComponentDataModel::find()->where(['component_id'=>$api_param['geshop_component_ui_id'],"key"=>"ipsFilterInfo","tpl_id"=>$page->tpl_id])->one();
                                        if(!empty($ui_data_model) && isset($response_data['ips']['activity_child_id'])){
                                            $ui_data_ips_filter_info = json_decode($ui_data_model->value,1);
                                            $ui_data_ips_filter_info['ips_activity_child_id'] = $response_data['ips']['activity_child_id'];
                                            $ui_data_ips_filter_info = json_encode($ui_data_ips_filter_info);
                                            $ui_data_model->value = $ui_data_ips_filter_info;
                                            $ui_data_model->save();
                                        }
                                }
                            }
                        }
                        //同步修改组件的数据
                        else{
                            $page = PageUiModel::findOne($api_param['geshop_component_ui_id']);
                            if(!empty($page)) {
                                $page_ui_component_datas = PageUiComponentDataModel::find()->where(['component_id'=>$api_param['geshop_component_ui_id'],"tpl_id"=>$page->tpl_id])->asArray()->all();
                                $is_ips = false;
                                $is_auto = false;
                                foreach ($page_ui_component_datas as $page_ui_component_data){
                                    if($page_ui_component_data['key'] == "goodsDataFrom" && $page_ui_component_data['value'] == "2"){
                                        $is_ips = true;
                                    }
                                    if($page_ui_component_data['key'] == "ipsMethods" && $page_ui_component_data['value'] == "3"){
                                        $is_auto = true;
                                    }
                                }

                                if($is_ips && $is_auto){
                                    //需要修改ips_activity_child_id值

                                    $ui_data_model = PageUiComponentDataModel::find()->where(['component_id' => $api_param['geshop_component_ui_id'], "key" => "ipsFilterInfo","tpl_id"=>$page->tpl_id])->one();
                                    if (!empty($ui_data_model) && isset($response_data['ips']['activity_child_id'])) {
                                        $ui_data_ips_filter_info = json_decode($ui_data_model->value, 1);
                                        $ui_data_ips_filter_info['ips_activity_child_id'] = $response_data['ips']['activity_child_id'];
                                        $ui_data_ips_filter_info = json_encode($ui_data_ips_filter_info);
                                        $ui_data_model->value = $ui_data_ips_filter_info;
                                        $ui_data_model->save();
                                    }
                                }

                            }
                        }


                    }
                }
            }

        }else{
            return app()->helper->arrayResult(1,"ips返回错误信息：".$response['message']);
        }
    }
    /**
     * 同步修改，删除活动信息到ips系统
     * @param $params
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author yuanwenguang 2019/4/19 11:46
     */
    public static function SyncActivityToIps($params)
    {
        $geshop_activity_id = empty($params["geshop_activity_id"]) ? null : $params['geshop_activity_id']; //geshop活动id
        $geshop_activity_name = !isset($params['geshop_activity_name']) ? null : (empty($params['geshop_activity_name']) ? "" : $params['geshop_activity_name']); //geshop活动名称
        $activity_des = !isset($params['activity_des']) ? null : (empty($params['activity_des']) ? "" : $params['activity_des']); //主活动活动描述
        $geshop_activity_child_id = empty($params['geshop_activity_child_id']) ? null : $params['geshop_activity_child_id']; //geshop子活动id
        $geshop_activity_child_name = !isset($params['geshop_activity_child_name']) ? null : (empty($params['geshop_activity_child_name']) ? '' : $params['geshop_activity_child_name']); //geshop子活动名称
        $geshop_offline_time = empty($params['geshop_offline_time']) ? null : $params['geshop_offline_time']; // geshop下线时间
        $geshop_component_ui_id = empty($params['geshop_component_ui_id']) ? null : $params['geshop_component_ui_id']; //组件位置id
        $geshop_component_name = empty($params['geshop_component_name']) ? null : $params['geshop_component_name']; //组件名称
        $del_info = empty($params['del_info']) ? null : $params['del_info'];//删除信息 {"geshop_activity_id":111,"geshop_activity_child_id":222,"geshop_component_ui_id":333}
        $update_user = app()->user->username;//修改人

        // 如果没有活动ID（比如首页活动页面）
        if (empty($geshop_activity_id)) {
            return;
        }

        $data = [];
        if (!empty($geshop_activity_id)) {
            $data['geshop_activity_id'] = $geshop_activity_id;
        }
        if (!is_null($geshop_activity_name)) {
            $data['geshop_activity_name'] = $geshop_activity_name;
        }
        if (!is_null($activity_des)) {
            $data['activity_des'] = $activity_des;
        }
        if (!empty($geshop_activity_child_id)) {
            $data['geshop_activity_child_id'] = $geshop_activity_child_id;
        }
        if (!is_null($geshop_activity_child_name)) {
            $data['geshop_activity_child_name'] = $geshop_activity_child_name;
        }
        if (!empty($geshop_offline_time)) {
            $data['geshop_offline_time'] = $geshop_offline_time;
        }
        if (!empty($geshop_component_ui_id)) {
            $data['geshop_component_ui_id'] = $geshop_component_ui_id;
        }
        if (!empty($geshop_component_name)) {
            $data['geshop_component_name'] = $geshop_component_name;
        }
        if (!empty($del_info)) {
            $data['del_info'] = $del_info;
        }
        $data['update_user'] = $update_user;

        try {

            // 从Cookie中获取网站简码
            $websiteCode = SitePlatform::getCurrentSiteGroupCode();
            if (empty($websiteCode)) {
                return;
            }

            //查询此活动是否绑定选品系统[规则添加（自动）/筛选器添加（手动）]
            if (!empty($geshop_activity_child_id)) {
                $soa_ips_goods_model = SoaIpsGoodsModel::findOne([
                        'website_code' => $websiteCode,
                        'rule_type'    => [SoaIpsGoodsModel::RULE_TYPE_IPS_RULE, SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER],
                        'page_id'      => $geshop_activity_child_id,
                    ]
                );

                if (empty($soa_ips_goods_model)) {
                    return;
                }
            }

            if (!empty($geshop_component_ui_id)) {
                $soa_ips_goods_model = SoaIpsGoodsModel::findOne([
                        'website_code' => $websiteCode,
                        'rule_type'    => [SoaIpsGoodsModel::RULE_TYPE_IPS_RULE, SoaIpsGoodsModel::RULE_TYPE_IPS_FILTER],
                        'component_id' => $geshop_component_ui_id
                    ]
                );

                if (empty($soa_ips_goods_model)) {
                    return;
                }
            }

            $token_data = json_encode($data);
            $post_data['sn'] = app()->params['soa']['ips']['sn'];
            $post_data["token"] = md5(app()->params['soa']['ips']['key'] . $token_data . $post_data['sn']);
            $post_data['data'] = $token_data;
            $url = app()->params['soa']['ips']['apiUrlPrefix'] . "/activity-child/sync-geshop-activity";
            $curl = new StandardResponseCurl();
            $response = $curl->slient()->asArray()->request("POST", $url, ['form_params' => $post_data]);
            if (!empty($response)) {
                if (isset($response['code']) && $response['code'] != 0) {
                    \Yii::info($response["message"], __METHOD__);
                }
            } else {
                throw new \Exception("请求IPS失败");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
