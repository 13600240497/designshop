<?php

namespace app\modules\common\dl\components;

use app\modules\common\dl\models\ActivityModel;
use app\modules\common\dl\models\ActivityGroupModel;
use app\modules\common\dl\models\PageLanguageModel;
use app\modules\common\dl\models\PageModel;
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\modules\base\components\MenuComponent;
use app\modules\common\dl\traits\CommonVerifyStatusTrait;
use app\modules\common\dl\traits\CommonPublishTrait;
use app\modules\base\models\AdminModel;
use ego\base\JsonResponseException;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * 自定义活动组件
 */
class CommonAdvertisementComponent extends Component
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
     * @param array $params
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     */
    public function lists($params)
    {
        if (empty($params[ static::ATTR_SITE_CODE ])
                || !SitePlatform::isCurrentSiteGroupPlatformSite($params[ static::ATTR_SITE_CODE ])) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        if (empty($params[ static::ATTR_SITE_CODE ])) {
            $sites = MenuComponent::getUserSites(app()->user->admin->is_super);
            $params[ static::ATTR_SITE_CODE ] = $sites[0][ MenuComponent::SHORT_NAME ];
        }

        $activityQuery = ActivityModel::find()->alias('a')
            ->select('a.*, u.realname as create_name')
            ->leftJoin(AdminModel::tableName() . ' as u', 'a.create_user = u.username')
            ->where(['a.is_delete' => 0,'a.mold'=>2])
            ->andFilterWhere(['u.realname' => !empty($params['create_name']) ? $params['create_name'] : ''])
            ->andFilterWhere(['like', 'a.name', !empty($params['name']) ? $params['name'] : ''])
            ->andFilterWhere(['a.site_code' => $params[ static::ATTR_SITE_CODE ]])
            ->andFilterWhere(['a.type' => !empty($params['type']) ? $params['type'] : '']);

        $count = $activityQuery->count();
        $pagination = Pagination::new($count);

        $activityList = $activityQuery
            ->groupBy('a.id')
            ->orderBy('a.id desc')
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
                $activityGroupMap = array_column(ArrayHelper::toArray($activityGroupList), NULL, 'id');
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
    private function buildListData($params, &$data)
    {
        list($pages, $siteConf, $activityGroupMap) = $params;
        foreach ($data as $i => $item) {
            //获取语言
            $data[ $i ]['langList'] = ActivityModel::getLangListByLangString($item['lang']);
            $lang = current($data[ $i ]['langList'])['key'];

            //分组信息
            $activityGroupInfo = $activityGroupMap[$item['group_id']] ?? [
                    'platform_list' => SitePlatform::getPlatformCodeBySiteCode($item['site_code']),
                    'lang_list' => $item['lang']
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
                $domain = $siteConf['secondary_domain'][ $lang ];
                $data[ $i ]['preview'] = $domain . $pages[ $item['id'] ]['page_url'];
                $data[ $i ]['qrcode'] = Url::to([
                    '/activity/qr-code/create',
                    'url' => $data[ $i ]['preview']
                ], true);
            }
        }
    }

    /**
     * 三端合一, 添加子页面时关联其他端的活动选择列表
     *
     * @param array $params get参数
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function userActivityList($params)
    {
        if (empty($params[static::ATTR_SITE_CODE])
                || !SitePlatform::isCurrentSiteGroupPlatformSite($params[static::ATTR_SITE_CODE])) {
            throw new JsonResponseException($this->codeFail, '参数site_code无效');
        }

        if (empty($params['lang'])) {
            throw new JsonResponseException($this->codeFail, '参数lang无效');
        }

        $activityList = ActivityModel::find()->where([
                'is_delete' => ActivityModel::NOT_DELETE,
                'site_code' => $params[static::ATTR_SITE_CODE]
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
                        'id' => $activityInfo->id,
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
     * @param array $params post参数
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupAdd($params)
    {
        $activityGroupInfo = [
            'platform' => [],
            'lang' => []
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
            if (!isset($platformList[$platformCode])) {
                continue;
            }

            $platformParams = array_map('trim', $platformList[$platformCode]);
            // 检查参数合法性
            if (empty($platformParams[static::ATTR_SITE_CODE])
                    || !SitePlatform::isCurrentSiteGroupPlatformSite($platformParams[static::ATTR_SITE_CODE])) {
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

            $siteCode = $platformParams[static::ATTR_SITE_CODE];

            //剔除不支持的语言
            $postLangKeys = explode(SiteConstants::CHAR_COMMA, $platformParams['lang']);
            $validLangKeys = SitePlatform::getSiteSpecialPageValidLanguageKeys($siteCode, $postLangKeys);
            $platformParams['lang'] = join(SiteConstants::CHAR_COMMA, $validLangKeys);

            if (empty( $platformParams['lang'])) {
                //用户继续忽略提示，继续提交忽略掉没有没有语言信息的活动
                if ($missCount > 1) {
                    continue;
                } else {
                    $missInfo['platform'][] = SitePlatform::getPlatformNameByCode($platformCode);
                    $supportLanguages = SitePlatform::getSiteSpecialPageSupportLanguages($siteCode);
                    foreach ($supportLanguages as $_langInfo) {
                        $missInfo['lang'][] = $_langInfo['name'];
                    }
                }
            }

            $activityGroupInfo['platform'][] = $platformCode;
            $activityGroupInfo['lang'] = array_merge($activityGroupInfo['lang'], $postLangKeys);
            $validPlatformParams[$platformCode] = $platformParams;
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
     * 三端合一, 编辑活动并同步其他端
     *
     * @param array $params POST数据
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupEdit($params) {
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
            }
            

            $transaction->commit();
            return app()->helper->arrayResult($this->codeSuccess, '修改成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return app()->helper->arrayResult($this->codeFail, sprintf('修改失败,错误：%s', $e->getMessage()));
        }

    }

    /**
     * 添加单个活动
     * @param array $data 活动数据
     * @param boolean $runValidation 添加数据时是否验证数据
     * @throws \yii\base\Exception
     * @since v1.4.0
     */
    private function addActivity($data, $runValidation = true)
    {
        if (empty($data[self::ATTR_SITE_CODE])) {
            throw new Exception('没有设置site_code');
        }
        unset($data['id']);

        $siteCode = $data[self::ATTR_SITE_CODE];
        $model = new ActivityModel();
        $model->load($data, '');

        $model->type = $this->getTypeBySiteCode($siteCode);

        //状态值初始化
        $model->status = ActivityModel::STATUS_TO_BE_ONLINE;
        $model->verify_status = ActivityModel::VERIFY_STATUS_NOT_COMMIT;
        $model->is_delete = ActivityModel::NOT_DELETE;
        $model->is_lock = ActivityModel::UN_LOCK;
        $model->mold   = 2;
        if (!$model->insert($runValidation)) {
            throw new Exception($model->flattenErrors(', '));
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

        //删除活动同时删除活动下的页面（删除只是修改是否删除状态值）
        PageModel::updateAll(['is_delete' => PageModel::IS_DELETE], ['activity_id' => $id]);
        return app()->helper->arrayResult(0, '删除成功');
    }

    /**
     * 活动审核(status可为2/4)
     *
     * @param int $activityId 活动ID
     * @param int $status 活动要变更的状态
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
                PageModel::updateAll([
                    'status'        => PageModel::PAGE_STATUS_HAS_OFFLINE,
                    'verify_status' => PageModel::VERIFY_STATUS_PASS_TO_OFFLINE,
                    'verify_user'   => app()->user->username,
                    'verify_time'   => $verifyTime
                ], ['id' => $pageIds]);       
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
     * @param      $model
     * @param bool $runValidation
     *
     * @return array
     * @throws JsonResponseException
     */
    public function doLock($model, $runValidation = true)
    {
        if (0 === (int)$model->is_lock) {
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
}
