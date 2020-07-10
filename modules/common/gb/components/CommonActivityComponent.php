<?php

namespace app\modules\common\gb\components;

use app\modules\common\gb\models\{
    ActivityModel, ActivityGroupModel, PageGroupModel, PageLanguageModel, PageModel
};
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\modules\base\components\MenuComponent;
use app\modules\common\gb\traits\{
    CommonVerifyStatusTrait, CommonPublishTrait
};
use app\modules\base\models\AdminModel;
use app\modules\soa\models\SoaObsGoodsModel;
use ego\base\JsonResponseException;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\soa\components\ObsComponent;

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
     * @param array $params
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     */
    public function lists($params)
    {
        if (empty($params[ static::ATTR_SITE_CODE ])
            || !SitePlatform::isCurrentSiteGroupPlatformSite($params[ static::ATTR_SITE_CODE ])
        ) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }
        
        if (empty($params[ static::ATTR_SITE_CODE ])) {
            $sites = MenuComponent::getUserSites(app()->user->admin->is_super);
            $params[ static::ATTR_SITE_CODE ] = $sites[0][ MenuComponent::SHORT_NAME ];
        }
        
        if (!empty($params['page_id'])) {
            $pageModel = PageModel::getById($params['page_id']);
            if (!$pageModel) {
                throw new JsonResponseException($this->codeFail, '无效的子页面');
            }
            $activityId = $pageModel->activity_id;
        }
        $activityQuery = ActivityModel::find()->alias('a')
            //->select('a.*, u.realname as create_name,u2.realname as update_user')
            ->select('a.*, u.realname as create_name')
            ->leftJoin(AdminModel::tableName() . ' as u', 'a.create_user = u.username')
            //->leftJoin(AdminModel::tableName() . ' as u2', 'a.update_user = u2.username')
            ->leftJoin(PageGroupModel::tableName() . ' as pg', 'a.group_id = pg.activity_group_id')
            ->where(['a.is_delete' => 0])
            ->andFilterWhere(['u.realname' => !empty($params['create_name']) ? $params['create_name'] : ''])
            ->andFilterWhere(['like', 'a.name', !empty($params['name']) ? $params['name'] : ''])
            ->andFilterWhere(['a.site_code' => $params[ static::ATTR_SITE_CODE ]])
            ->andFilterWhere(['a.type' => !empty($params['type']) ? $params['type'] : ''])
            ->andFilterWhere(['a.id' => isset($activityId) ? $activityId : ''])
            ->andFilterWhere(['pg.special_id' => $params['special_id'] ?? ''])
            ->andFilterWhere(['or like', 'a.pipeline', (!empty($params['searchChannel']) ? $params['searchChannel'] : [])])
            ->andFilterWhere(['like', 'a.theme_name', (!empty($params['searchObs']) ? trim($params['searchObs']) : '')])
            ->groupBy('a.id');
        $count = $activityQuery->count();
        $pagination = Pagination::new($count);
        
        $activityList = $activityQuery
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
    private function buildListData($params, &$data)
    {
        list($pages, $siteConf, $activityGroupMap) = $params;
        $ObsComponent = new ObsComponent();
        foreach ($data as $i => $item) {
            
            //分组信息
            $activityGroupInfo = $activityGroupMap[ $item['group_id'] ] ?? [];
            
            $platformList = explode(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform_list']);
            $supportPlatform = SitePlatform::getGbSupportPlatforms();
            $userSupportPlatform = array_column(MenuComponent::getUserSites(app()->user->admin->is_super), 'name');
            
            foreach ($supportPlatform as $platformCode) {
                
                if (!in_array('gearbest-' . $platformCode, $userSupportPlatform)) {
                    continue;
                }
                $data[ $i ]['group_info']['platform_list'][] = [
                    'code'     => $platformCode,
                    'name'     => SitePlatform::getPlatformNameByCode($platformCode),
                    'selected' => in_array($platformCode, $platformList) ? 1 : 0,
                ];
            }
            
            $data[ $i ]['group_info']['pipelineList'] = ActivityModel::getPipelineListByPipelineString($item['site_code'], $activityGroupInfo['lang_list']);
            $data[ $i ]['pipelineList'] = SitePlatform::getPipelineNameByPipelineCode(explode(',', $item['pipeline']), $item['site_code']);
            $data[ $i ]['platformList'] = array_map(function ($platformCode) {
                return SitePlatform::getPlatformNameByCode($platformCode);
            }, $platformList);
            //设置预览地址和二维码
            $data[ $i ]['preview'] = $data[ $i ]['qrcode'] = '';
            if (!empty($pages[ $item['id'] ]['page_url'])) {
                $pipeline = $pages[ $item['id'] ]['pipeline'] ?? '';
                $lang = $pages[ $item['id'] ]['lang'] ?? '';
                $domain = !empty($siteConf['secondary_domain'][ $pipeline ][ $lang ])
                    ? $siteConf['secondary_domain'][ $pipeline ][ $lang ]
                    : $siteConf['secondary_domain']['GB']['en'];
                $data[ $i ]['preview'] = $domain . $pages[ $item['id'] ]['page_url'];
                $data[ $i ]['qrcode'] = Url::to([
                    '/activity/gb/qr-code/create',
                    'url' => $data[ $i ]['preview']
                ], true);
            }
            $data[ $i ]['themeList'] = $ObsComponent->getThemeByActivity($item['id']); //obs选择活动
            
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
     * @param array $params post参数
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupAdd($params)
    {
        $activityGroupInfo = [
            'platform' => [],
            'pipeline' => []
        ];
        
        if (empty($params['platform_list'])) {
            throw new JsonResponseException($this->codeFail, '参数不全');
        }
        
        $missCount = $params['miss_count'] ?? 0;
        $missInfo = ['platform' => [], 'pipeline' => []];
        $validPlatformParams = [];
        $supportPlatforms = SitePlatform::getGbSupportPlatforms();
        $siteCode = '';
        foreach ($supportPlatforms as $platformCode) {
            $platformList = json_decode($params['platform_list'], true);
            if (!isset($platformList[ $platformCode ])) {
                continue;
            }
            $pipelineList = $platformList[ $platformCode ]['pipeline'] ?? [];
            unset($platformList[ $platformCode ]['pipeline']);
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
            
            //渠道字段检查(pipeline: GB,GBES)
            if (empty($pipelineList)) {
                $errorMsg = sprintf("应用端口 %s 没有选择渠道", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }
            
            $siteCode = $platformParams[ static::ATTR_SITE_CODE ];
            //剔除不支持的渠道和语言
            $platformParams['pipeline'] = SitePlatform::getSiteSpecialPageValidPipelines($siteCode, $pipelineList);
            
            if (empty($platformParams['pipeline'])) {
                //用户继续忽略提示，继续提交忽略掉没有没有语言信息的活动
                if ($missCount > 1) {
                    continue;
                } else {
                    $missInfo['platform'][] = SitePlatform::getPlatformNameByCode($platformCode);
                    foreach ($platformParams['pipeline'] as $key => $item) {
                        $missInfo['pipeline'][] = $key;
                    }
                }
            }
            $activityGroupInfo['platform'][] = $platformCode;
            $activityGroupInfo['pipeline'] = array_merge($activityGroupInfo['pipeline'], $platformParams['pipeline']);
            $platformParams['pipeline'] = join(SiteConstants::CHAR_COMMA, array_keys($platformParams['pipeline']));
            $validPlatformParams[ $platformCode ] = $platformParams;
            
        }
        
        if (!empty($missInfo['platform'])) {
            $missCount++;
            $missInfo['pipeline'] = array_unique($missInfo['pipeline']);
            $errorMessage = sprintf("%s端无%s渠道，所以不会生成活动呦！", join('/', $missInfo['platform']), join('、', $missInfo['pipeline']));
            throw new JsonResponseException(101, $errorMessage, ['miss_count' => $missCount]);
        }
        
        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '没有活动需要生成，请检查提交数据！');
        }
        
        $groupPipelineList = [];
        $site = SitePlatform::getSiteBySiteCode($siteCode);
        $configSitePipeline = app()->params['soa'][ $site ]['pipeline'] ?? [];
        $configSiteLang = app()->params['lang'];
        foreach ($configSitePipeline as $key => $row) {
            if (isset($activityGroupInfo['pipeline'][ $key ])) {
                $langList = [];
                foreach ($configSiteLang as $lang => $item) {
                    if (in_array($lang, $activityGroupInfo['pipeline'][ $key ])) {
                        $langList[] = $lang;
                    }
                }
                $groupPipelineList[ $key ] = $langList;
            }
        }
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            $activityGroupModel = new ActivityGroupModel();
            $activityGroupModel->platform_list = join(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform']);
            $activityGroupModel->lang_list = json_encode($groupPipelineList);
            if (!$activityGroupModel->insert(true)) {
                throw new Exception('添加活动分组失败');
            }
            $groupId = $activityGroupModel->id;
            
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
        if (empty($params['platForm'])) {
            throw new JsonResponseException($this->codeFail, '应用端口不能空');
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
                $platformCode = SitePlatform::getPlatformCodeByPlatformType($activityModel->type);
                if ($activityModel->is_delete === ActivityModel::IS_DELETE || strpos($params['platForm'], $platformCode) === false) {
                    continue;
                }
                $activityModel->name = $params['name'];
                $activityModel->description = $params['description'];
                $activityModel->theme_name = $params['obsName'] ?? '';
                if (false === $activityModel->update(true)) {
                    throw new Exception($model->flattenErrors(', '));
                }
                if (isset($params['obsId']) && isset($params['obsName'])) {
                    $obsComponent = new ObsComponent();
                    $obsComponent->saveActivity($params['obsId'], $activityModel->id, $params['obsName']); //更新obs
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
     *
     * @param array   $data          活动数据
     * @param boolean $runValidation 添加数据时是否验证数据
     *
     * @return int
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
        $model->theme_name = $data['obsName'] ?? '';
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
        
        return $model->id;
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
        ObsComponent::deleteActivity($id); //删除活动需要删除obs关联关系
        
        return app()->helper->arrayResult(0, '删除成功');
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
            $pageResult = PageModel::find()->select('id,pipeline')->where([
                'activity_id' => $activityId,
                'status'      => [
                    PageModel::PAGE_STATUS_HAS_ONLINE
                ],
                'is_delete'   => PageModel::NOT_DELETE
            ])->asArray()->all();
            if ($pageResult) {
                $pageIds = array_column($pageResult, 'id');
                if (PageModel::updateAll([
                    'status'        => PageModel::PAGE_STATUS_HAS_OFFLINE,
                    'verify_status' => PageModel::VERIFY_STATUS_PASS_TO_OFFLINE,
                    'verify_user'   => app()->user->username,
                    'verify_time'   => $verifyTime
                ], ['id' => $pageIds])
                ) {
                    foreach ($pageResult as $item) {
                        $pages[ $item['pipeline'] ][] = $item['id'];
                    }
                    foreach ($pages as $pipeline => $row) {
                        list($success, $data) = $this->batchCreateOfflinePageHtml($row, $activityId, $pipeline);
                        if (!$success) {
                            return app()->helper->arrayResult(1, '页面下线所需的跳转HTML文件生成失败，请重试', $data);
                        }
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
     * @param   object $model
     * @param   bool   $runValidation
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
     * 三端合一, 编辑活动并同步其他端
     *
     * @param array $params POST数据
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupEditAdd($params)
    {
        if (empty($params['id']) || !is_numeric($params['id'])) {
            throw new JsonResponseException($this->codeFail, '无效的参数id');
        }
        if (empty($params['platform_list'])) {
            throw new JsonResponseException($this->codeFail, '应用端口不能空');
        }
        $params['platform_list'] = json_decode($params['platform_list'], true);
        $activityId = $params['id'];
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
        $lang_list = [];
        $platform_list = '';
        $supportPlatforms = SitePlatform::getGbSupportPlatforms();
        //剔除不支持的端口/渠道/语言
        foreach ($params['platform_list'] as $platform => $pipelineList) {
            $siteCode = SitePlatform::getSiteCodeByPlatformCode($platform);
            $supportPipeline = app()->params['sites'][ $siteCode ]['secondary_domain'] ?? [];
            if (!in_array($platform, $supportPlatforms)) {
                unset($params['platform_list'][ $platform ]);
                continue;
            }
            foreach ($pipelineList as $pipeline => $langList) {
                if (!isset($supportPipeline[ $pipeline ]) || empty($langList)) {//不支持的渠道
                    unset($params['platform_list'][ $platform ][ $pipeline ]);
                    continue;
                }
                $supportLang = $supportPipeline[ $pipeline ];
                foreach ($langList as $key => $lang) {
                    if (!isset($supportLang[ $lang ])) {//不支持的语言
                        unset($params['platform_list'][ $platform ][ $pipeline ][ $key ]);
                        continue;
                    }
                    if (empty($lang_list[ $pipeline ]) || !in_array($lang, $lang_list[ $pipeline ])) {
                        $lang_list[ $pipeline ][] = $lang;
                    }
                }
            }
        }
        try {
            foreach ($params['platform_list'] as $platform => $pipelineList) {
                $siteCode = SitePlatform::getSiteCodeByPlatformCode($platform);
                $supportPipeline = app()->params['sites'][ $siteCode ]['secondary_domain'] ?? [];
                $platform_list .= $platform . ',';
                $activityModel = ActivityModel::find()->where(['group_id' => $model->group_id, 'site_code' => $siteCode])->one();
                if (!empty($activityModel)) {
                    $selectPipeline = explode(',', $activityModel->pipeline);
                    $activityModel->pipeline = join(',', array_keys($pipelineList));
                    if (false === $activityModel->update(true)) {
                        throw new Exception($platform . ' 更新渠道数据失败');
                    }
                    $otherDefaultPipeline = PageModel::find()
                        ->where(['activity_id' => $activityModel->id, 'site_code' => $siteCode, 'pipeline' => $selectPipeline[0]])
                        ->asArray()->orderBy('id asc')->all();
                    
                    if (empty($otherDefaultPipeline)) {
                        continue;
                    }
                    
                    foreach ($pipelineList as $pipeline => $langList) {
                        if (!in_array($pipeline, $selectPipeline)) {//新增渠道（保存渠道子页面）
                            foreach ($otherDefaultPipeline as $item) {
                                $pageGroup = PageGroupModel::find()->select('special_id, page_group_id')->where(['page_id' => $item['id']])->one();
                                $page_group_id = $pageGroup->page_group_id ? $pageGroup->page_group_id : PageGroupModel::generatePageGroupId();
                                $pageModel = new PageModel();
                                $pageModel->load((array) $item, '');
                                $pageModel->pipeline = $pipeline;
                                $pageModel->defaultLanguage = $langList[0] ?? '';
                                $pageModel->site_code = $siteCode;
                                $pageModel->group_id = $item['group_id'];
                                if (false === $pageModel->insert(true)) {
                                    throw new Exception($pipeline . '添加子页面失败');
                                }
                                $pageGroupModel = new PageGroupModel();
                                $pageGroupModel->activity_group_id = $model->group_id;
                                $pageGroupModel->page_group_id = $page_group_id;
                                $pageGroupModel->platform_type = SitePlatform::getPlatformTypeByPlatformCode($platform);
                                $pageGroupModel->page_id = $pageModel->id;
                                $pageGroupModel->pipeline = $pipeline;
                                $pageGroupModel->special_id = $pageGroup['special_id'];
                                if (false === $pageGroupModel->insert(true)) {
                                    throw new Exception($platform . $pipeline . '添加子页面分组失败');
                                }
                                $defaultLang = PageLanguageModel::find()
                                    ->where(['page_id' => $item['id'], 'lang' => $item['defaultLanguage']])
                                    ->asArray()
                                    ->one();
                                
                                foreach ($langList as $lang) {
                                    $pageLanguageModel = new PageLanguageModel();
                                    $pageLanguageModel->load($defaultLang, '');
                                    $pageLanguageModel->lang = $lang;
                                    $pageLanguageModel->pipeline = $pipeline;
                                    $pageLanguageModel->page_id = $pageModel->id;
                                    
                                    if (false === $pageLanguageModel->insert(true)) {
                                        throw new Exception($platform . ' > ' . $pipeline . ' > ' . $lang . ' 添加子页面失败');
                                    }
                                    
                                    $obsModel = SoaObsGoodsModel::find()->where(['pid' => $defaultLang['page_id'], 'lang' => $defaultLang['lang']])->asArray()->one();
                                    if (!empty($obsModel)) {
                                        if (false === (new ObsComponent())->savePageData([
                                                $pageModel->id,
                                                $lang,
                                                $obsModel['page_id'],
                                                $obsModel['page_name'],
                                                $obsModel['platform'],
                                                $obsModel['activity_id'],
                                                $obsModel['site_code'],
                                                $obsModel['pipeline']
                                            ])
                                        ) {
                                            throw new Exception($platform . ' > ' . $pipeline . ' > ' . $lang . ' 添加子页面OBS信息失败');
                                        }
                                    }
                                }
                            }
                        } else {
                            $defaultPipeline = PageModel::find()->where(['activity_id' => $activityModel->id, 'site_code' => $siteCode, 'pipeline' => $pipeline])->all();
                            foreach ($defaultPipeline as $item) {
                                $pipelineLangList = PageLanguageModel::find()->indexBy('lang')->where(['page_id' => $item['id']])->asArray()->all();
                                foreach ($langList as $lang) {
                                    if (!isset($pipelineLangList[ $lang ]) && !empty($pipelineLangList)) { //新增语言（新建语言子页面）
                                        $defaultLang = $item['defaultLanguage'];
                                        $default = $pipelineLangList[ $defaultLang ];
                                        $pageLanguageModel = new PageLanguageModel();
                                        $pageLanguageModel->load((array) $default, '');
                                        $pageLanguageModel->lang = $lang;
                                        
                                        if (false === $pageLanguageModel->insert(true)) {
                                            throw new Exception($platform . ' > ' . $pipeline . ' > ' . $lang . ' 添加子页面失败');
                                        }
                                        $obsModel = SoaObsGoodsModel::find()->where(['pid' => $default['page_id'], 'lang' => $default['lang']])->asArray()->one();
                                        if (!empty($obsModel)) {
                                            if (false === (new ObsComponent())->savePageData([
                                                    $obsModel['pid'],
                                                    $lang,
                                                    $obsModel['page_id'],
                                                    $obsModel['page_name'],
                                                    $obsModel['platform'],
                                                    $obsModel['activity_id'],
                                                    $obsModel['site_code'],
                                                    $obsModel['pipeline'],
                                                ])
                                            ) {
                                                throw new Exception($platform . ' > ' . $pipeline . ' > ' . $lang . ' 添加子页面OBS信息失败');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {//新增端口 （不创建子页面）
                    $activityModel = new ActivityModel();
                    $activityModel->load(ArrayHelper::toArray($model), '');
                    $pipelineList = array_intersect(array_keys($params['platform_list'][ $platform ]), array_keys($supportPipeline));
                    $activityModel->type = SitePlatform::getPlatformTypeByPlatformCode($platform);
                    $activityModel->site_code = $siteCode;
                    $activityModel->pipeline = join(',', $pipelineList);
                    
                    if (false === $activityModel->insert(true)) {
                        throw new Exception($platform . '端保存活动失败');
                    }
                    if (!empty($model->theme_name)) {
                        $activity_id = $model->id;
                        $obsComponent = new ObsComponent();
                        $data = SoaObsGoodsModel::find()->where(['activity_id' => $activity_id])->asArray()->one();
                        if (!empty($data)) {
                            $obsComponent->saveActivity($data['theme_id'], $activity_id, $data['theme_name']);
                        }
                        unset($data, $obsComponent);
                    }
                }
            }
            //更新活动分组信息
            $activityGroupModel = ActivityGroupModel::find()->where(['id' => $model->group_id])->one();
            $activityGroupModel->platform_list = trim($platform_list, ',');
            $activityGroupModel->lang_list = json_encode($lang_list);
            if (false === $activityGroupModel->update(true)) {
                throw new Exception('保存活动分组信息失败');
            }
            $transaction->commit();
            
            return app()->helper->arrayResult($this->codeSuccess, '保存成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            
            return app()->helper->arrayResult($this->codeFail, sprintf('保存失败,错误：%s', $e->getMessage()));
        }
        
    }
}
