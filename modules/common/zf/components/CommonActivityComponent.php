<?php

namespace app\modules\common\zf\components;

use app\modules\activity\zf\cache\PageCache;
use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\ActivityGroupModel;
use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\models\PageModel;
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\base\PipelineUtils;
use app\modules\base\components\MenuComponent;
use app\modules\common\zf\traits\CommonVerifyStatusTrait;
use app\modules\common\zf\traits\CommonPublishTrait;
use app\modules\base\models\AdminModel;
use ego\base\JsonResponseException;
use yii\db\Expression;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\base\components\AccessLogComponent;

/**
 * 自定义活动组件
 *
 * @property \app\modules\activity\zf\components\ActivityGroupDataComponent $ActivityGroupDataComponent
 */
abstract class CommonActivityComponent extends Component
{
    use CommonVerifyStatusTrait;
    use CommonPublishTrait;

    const ATTR_SITE_CODE           = 'site_code';
    const DEFAUTE_HOME_TITLE       = 'Home';
    const DEFAUTE_HOME_KEYWORDS    = 'Home';
    const DEFAUTE_HOME_DESCRIPTION = 'This is the description of the homepage.';
    const DEFAUTE_URL_NAME         = 'home';


    /**
     * 获取站点支持渠道和语言
     * @param string $siteCode
     * @return array
     */
    protected abstract function getConfigPipelineList($siteCode);

    /**
     * 过滤站点端口不支持的渠道和语言
     * @param string $siteCode
     * @param array $pipelineList
     * 格式：
     * ['ZF' => ['en','es']]
     * @return array
     */
    protected abstract function getPageValidPipelineList($siteCode, $pipelineList);

    /**
     * 活动列表
     *
     * @param array $params http GET参数
     * @param int $activityType 活动类型，默认专题活动
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     */
    public function lists($params, $activityType=SiteConstants::ACTIVITY_TYPE_SPECIAL)
    {
        if (empty($params[ static::ATTR_SITE_CODE ])
                || !SitePlatform::isCurrentSiteGroupPlatformSite($params[ static::ATTR_SITE_CODE ])) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }

        if (empty($params[ static::ATTR_SITE_CODE ])) {
            $sites = MenuComponent::getUserSites(app()->user->admin->is_super);
            $params[ static::ATTR_SITE_CODE ] = $sites[0][ MenuComponent::SHORT_NAME ];
        }
        if(!empty($params['searchType']) && $params['searchType'] == 2 && !empty($params['id']) ){//搜索子活动ID
            $activity = PageModel::find()
                ->select('activity_id')
                ->where(['id'=>$params['id']])
                ->asArray()
                ->one();

            if (empty($activity) || $activity['activity_id'] == 0) {//ID不存在或者ID为首页ID
                return app()->helper->arrayResult(0, 'success',['list' => [],'pagination' => []]);
            }

            $params['id'] = $activity['activity_id'] ?? -1;
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
            ->andFilterWhere(['a.id'=> !empty($params['id']) ? intval($params['id']) : ''])
            ->andFilterWhere(['a.is_frequently'=> !empty($params['is_frequently']) ? intval($params['is_frequently']) : '']);

        if (!empty($params['pipeline'])) {
            $activityQuery->andWhere(new Expression('FIND_IN_SET(:pipeline, pipeline)', [':pipeline' => $params['pipeline']]));
        }

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
                $activityGroupMap = array_column(ArrayHelper::toArray($activityGroupList), NULL, 'id');
            }

            //查询活动页面URL
            $pages = PageLanguageModel::getPageUrlList($actIds);
            $pages = $pages ? array_column($pages, null, 'activity_id') : [];

            //组装数据
            if ($data) {
                $this->buildListData([$pages, $activityGroupMap], $data);
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
        list($pages, $activityGroupMap) = $params;
        foreach ($data as $i => $item) {

            /** ------------------- 分组信息，包含端口、渠道列表 --------------------------------- */
            $item['group_id'] = (int)$item['group_id'];
            $platform = SitePlatform::getPlatformCodeBySiteCode($item['site_code']);
            //兼容RG无分组ID的老数据
            $activityGroupInfo = $activityGroupMap[$item['group_id']] ?? [
                    'platform_list' => $platform,
                    'lang_list'     => $item['lang'],
                    'support_list'  => json_encode([$platform => json_decode($item['lang'])])
                ];

            // 分组端口列表
            $platformList = explode(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform_list']);
            foreach ($platformList as $platformCode) {
                $data[ $i ]['group_info']['platform_list'][] = [
                    'code'    => $platformCode,
                    'name'    => SitePlatform::getPlatformNameByCode($platformCode),
                ];
            }

            // 分组渠道列表
            $groupPipelineList = PipelineUtils::getPipelineAndLangInfoList(
                $item['site_code'], json_decode($activityGroupInfo['lang_list'], true)
            );

            // url_prefix默认使用PC的
            $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
            $defualtSiteCode = app()->params['site'][$siteGroupCode]['platforms'][0];
            $configPcPipelineList = $this->getConfigPipelineList($siteGroupCode . '-' .$defualtSiteCode);
            if (!empty($groupPipelineList)) {
                foreach ($groupPipelineList as $_pipelineCode => $_langList) {
                    foreach ($_langList['lang_list'] as $_langCode => $_langInfo) {
                        $groupPipelineList[$_pipelineCode]['lang_list'][$_langCode]['url_prefix']
                            = $configPcPipelineList[$_pipelineCode][$_langCode] ?? '';
                    }
                }
            }
            $data[ $i ]['group_info']['pipeline_list'] = $groupPipelineList;

            // 分组支持列表
            $data[ $i ]['group_info']['support_list'] = [];
            $supportList = json_decode($activityGroupInfo['support_list'], true);
            foreach ($supportList as $_platformCode => $_pipelineList) {
                $data[ $i ]['group_info']['support_list'][$_platformCode] = PipelineUtils::getPipelineAndLangInfoList(
                    $item['site_code'], $_pipelineList
                );
            }

            /** ------------------- 当前端渠道信息 --------------------------------- */
            // 渠道列表
            $configPipelineList = $this->getConfigPipelineList($item['site_code']);
            $pipelineAndLangInfoList = PipelineUtils::getPipelineAndLangInfoList(
                $item['site_code'], json_decode($item['lang'], true)
            );
            if (!empty($pipelineAndLangInfoList)) {
                foreach ($pipelineAndLangInfoList as $_pipelineCode => $_langList) {
                    foreach ($_langList['lang_list'] as $_langCode => $_langInfo) {
                        $pipelineAndLangInfoList[$_pipelineCode]['lang_list'][$_langCode]['url_prefix']
                            = $configPipelineList[$_pipelineCode][$_langCode] ?? '';
                    }
                }
            }
            $data[ $i ]['pipeline_list'] = $pipelineAndLangInfoList;
            unset($data[ $i ]['lang']);

            // 设置预览地址和二维码
            $data[ $i ]['preview'] = $data[ $i ]['qrcode'] = '';
            if (!empty($pages[ $item['id'] ]['page_url'])) {
                $pipeline = $pages[ $item['id'] ]['pipeline'] ?? '';
                $lang = $pages[ $item['id'] ]['lang'] ?? '';
                $domain = $configPipelineList[$pipeline][ $lang ] ?? '';
                $data[ $i ]['preview'] = $domain . $pages[ $item['id'] ]['page_url'];
                $data[ $i ]['qrcode'] = Url::to(['/activity/zf/qr-code/create', 'url' => $data[ $i ]['preview']], true);
            }
        }
    }

    /**
     * 三端合一, 新增活动
     *
     * @param array $params post参数
     * @param int $activityType 活动类型，默认专题活动
     *
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupAdd($params, $activityType=SiteConstants::ACTIVITY_TYPE_SPECIAL)
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
        $supportPlatforms = app()->params['site'][SITE_GROUP_CODE]['platforms'] ?? [];
        $platformList = json_decode($params['platform_list'], true);
        foreach ($supportPlatforms as $platformCode) {
            if (!isset($platformList[$platformCode])) {
                continue;
            }

            $pipelineList = $platformList[$platformCode]['pipeline_list'] ?? [];
            unset($platformList[$platformCode]['pipeline_list']);
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

            //渠道字段检查(pipeline: ZF,ZFES)
            if (empty($pipelineList)) {
                $errorMsg = sprintf("应用端口 %s 没有选择渠道", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            $siteCode = $platformParams[static::ATTR_SITE_CODE];
            //剔除不支持的渠道和语言
            $platformParams['pipeline'] = $this->getPageValidPipelineList($siteCode, $pipelineList);

            //检查渠道下是否选择的语言
            if (!empty( $platformParams['pipeline'])) {
                foreach ($platformParams['pipeline'] as $_pipelineCode => $_langList) {
                    if (empty($_langList)) {
                        $errorMsg = sprintf(
                            "应用端口 %s 渠道 %s 没有选择语言",
                            SitePlatform::getPlatformNameByCode($platformCode),
                            $_pipelineCode
                        );
                        throw new JsonResponseException($this->codeFail, $errorMsg);
                    }
                }
            }

            if (empty( $platformParams['pipeline'])) {
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

            //排序
            if (!empty( $platformParams['pipeline'])) {
                $sortedPipelineList = [];
                $configPipelineList = $this->getConfigPipelineList($siteCode);
                foreach (array_keys($configPipelineList) as $_code) {
                    if (isset($platformParams['pipeline'][$_code])) {
                        $sortedPipelineList[$_code] = $platformParams['pipeline'][$_code];
                    }
                }
                $platformParams['pipeline'] = $sortedPipelineList;
            }

            //聚合数据
            $pipelineMergeList = ArrayHelper::merge($activityGroupInfo['pipeline'], $platformParams['pipeline']);
            $pipelineMergeList = array_map(function($langList) {
                return array_unique($langList);
            }, $pipelineMergeList);
            $activityGroupInfo['platform'][] = $platformCode;
            $activityGroupInfo['pipeline'] = $pipelineMergeList;
            $activityGroupInfo['support_list'][$platformCode] = $platformParams['pipeline'];

            $platformParams['lang'] = json_encode($platformParams['pipeline']);
            $platformParams['pipeline'] = join(SiteConstants::CHAR_COMMA, array_keys($platformParams['pipeline']));
            $validPlatformParams[$platformCode] = $platformParams;
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

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            $activityGroupModel = new ActivityGroupModel();
            $activityGroupModel->platform_list = join(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform']);
            $activityGroupModel->lang_list = json_encode($activityGroupInfo['pipeline']);
            $activityGroupModel->support_list = json_encode($activityGroupInfo['support_list']);
            if (!$activityGroupModel->insert(true)) {
                throw new Exception('添加活动分组失败');
            }
            $groupId = $activityGroupModel->id;

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
     * 三端合一, 编辑活动并同步其他端
     *
     * @param array $params POST数据
     * @return array
     * @throws JsonResponseException
     * @since v1.4.0
     */
    public function groupEdit($params)
    {
        set_time_limit(0);
        //ignore_user_abort(true);

        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        if (empty($params['id']) || !is_numeric($params['id'])) {
            throw new JsonResponseException($this->codeFail, '无效的参数id');
        }

        if (empty($params['platform_list'])) {
            throw new JsonResponseException($this->codeFail, '无效的参数platform_list');
        }

        $activityId = $params['id'];
        /** @var \app\modules\common\zf\models\ActivityModel $activityModel */
        $activityModel = ActivityModel::getById($activityId);
        if ((!$activityModel) || ActivityModel::IS_DELETE === (int) $activityModel->is_delete) {
            throw new JsonResponseException($this->codeFail, '活动不存在');
        }

        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($activityModel)) {
            return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
        }

        /** @var \app\modules\common\zf\models\ActivityGroupModel $activityGroupModel */
        $activityGroupModel = ActivityGroupModel::find()->where(['id' => $activityModel->group_id])->one();
        if (empty($activityGroupModel)) {
            throw new JsonResponseException($this->codeFail, sprintf('活动分组ID %d 不存在', $activityModel->group_id));
        }

        // 活动老数据
        $oldGroupSupportList = json_decode($activityGroupModel->support_list, true);

        // 接收，验证提交参数
        $srcPlatformsSyncInfo = []; // 端口原同步信息
        $targetPlatformsAddInfo = []; // 端口新增渠道信息
        $validPlatformParams = [];
        $supportPlatforms = app()->params['site'][SITE_GROUP_CODE]['platforms'] ?? [];
        $allPlatformParams = json_decode($params['platform_list'], true);
        foreach ($supportPlatforms as $platformCode) {
            if (!isset($allPlatformParams[$platformCode])) {
                continue;
            }

            // 不能新增端口
            if (!array_key_exists($platformCode, $oldGroupSupportList)) {
                continue;
            }

            $siteCode = SitePlatform::getSiteCodeByPlatformCode($platformCode);
            $pipelineList = $allPlatformParams[$platformCode]['pipeline_list'] ?? [];
            unset($allPlatformParams[$platformCode]['pipeline_list']);
            $platformParams = array_map('trim', $allPlatformParams[$platformCode]);

            if (empty($platformParams['name'])) {
                $errorMsg = sprintf("应用端口 %s 名称为必填项", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            // 渠道字段检查(pipeline: ZF,ZFES)
            if (empty($pipelineList)) {
                $errorMsg = sprintf("应用端口 %s 没有选择渠道", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            if (!empty($platformParams['sync_pipeline'])) {
                if (!isset($oldGroupSupportList[$platformCode][ $platformParams['sync_pipeline'] ])) {
                    $errorMsg = sprintf("应用端口 %s 同步渠道必须从原活动中选择", SitePlatform::getPlatformNameByCode($platformCode));
                    throw new JsonResponseException($this->codeFail, $errorMsg);
                }

                if (!empty($platformParams['sync_lang'])) {
                    $isSupport = in_array(
                        $platformParams['sync_lang'],
                        $oldGroupSupportList[$platformCode][ $platformParams['sync_pipeline']]
                    );
                    if (!$isSupport) {
                        $errorMsg = sprintf("应用端口 %s 同步渠道/语言必须从原活动中选择", SitePlatform::getPlatformNameByCode($platformCode));
                        throw new JsonResponseException($this->codeFail, $errorMsg);
                    }
                }
            }

            // 剔除不支持的渠道和语言
            $platformParams['pipeline'] = $this->getPageValidPipelineList($siteCode, $pipelineList);

            // 检查渠道下是否选择的语言
            if (!empty( $platformParams['pipeline'])) {
                foreach ($platformParams['pipeline'] as $_pipelineCode => $_langList) {
                    if (empty($_langList)) {
                        $errorMsg = sprintf(
                            "应用端口 %s 渠道 %s 没有选择语言",
                            SitePlatform::getPlatformNameByCode($platformCode),
                            $_pipelineCode
                        );
                        throw new JsonResponseException($this->codeFail, $errorMsg);
                    }
                }
            }

            // 对端口下的渠道按照配置顺序排序
            if (!empty($platformParams['pipeline'])) {
                $sortedPipelineList = [];
                $configPipelineList = $this->getConfigPipelineList($siteCode);
                foreach (array_keys($configPipelineList) as $_code) {
                    if (isset($platformParams['pipeline'][$_code])) {
                        $sortedPipelineList[$_code] = $platformParams['pipeline'][$_code];
                    }
                }
                $platformParams['pipeline'] = $sortedPipelineList;
            }

            // 获取端口下新增渠道
            $addPipelineList = $this->arrayDiffAssocRecursive($platformParams['pipeline'], $oldGroupSupportList[$platformCode]);
            if (!empty($addPipelineList) && empty($platformParams['sync_pipeline'])) {
                $errorMsg = sprintf("应用端口 %s 没有选择同步渠道", SitePlatform::getPlatformNameByCode($platformCode));
                throw new JsonResponseException($this->codeFail, $errorMsg);
            }

            // 保存有效数据
            if (!empty($addPipelineList) && !empty($platformParams['sync_pipeline'])
                && !empty($platformParams['sync_lang']) )
            {
                $srcPipelineCode = $platformParams['sync_pipeline'];
                $srcPlatformsSyncInfo[$platformCode] = [
                    'pipeline'  => $srcPipelineCode,
                    'lang'      => $platformParams['sync_lang']
                ];
                $targetPlatformsAddInfo[$platformCode] = $addPipelineList;
            }

            $validPlatformParams[$platformCode] = $platformParams;
        }


        $activityModelList = ActivityModel::find()->where(['group_id' => $activityGroupModel->id])->all();
        if (empty($activityModelList)) {
            throw new JsonResponseException(sprintf('活动组ID %s 下没有活动信息', $activityGroupModel->id));
        }

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            /** @var \app\modules\common\zf\models\ActivityModel $_activityModel */
            foreach ($activityModelList as $_activityModel) {
                if ($_activityModel->is_delete === ActivityModel::IS_DELETE)
                    continue;

                $platformCode = SitePlatform::getPlatformCodeBySiteCode($_activityModel->site_code);
                $platformParams = $validPlatformParams[$platformCode];

                $_activityModel->name = $platformParams['name'];
                $_activityModel->description = $platformParams['description'];
                if (false === $_activityModel->update(true)) {
                    throw new Exception($_activityModel->flattenErrors(', '));
                }
            }

            if (!empty($srcPlatformsSyncInfo) && !empty($targetPlatformsAddInfo)) {
                $this->ActivityGroupDataComponent->addActivityGroupPipeline(
                    $siteGroupCode,
                    $activityGroupModel,
                    $activityModelList,
                    $srcPlatformsSyncInfo,
                    $targetPlatformsAddInfo,
                    false
                );
            }

            $transaction->commit();

            // 同步活动信息需求执行时间比较多，线上比较容易出错！这里先关闭同步 - TianHaishen(2019-05-30)
//            foreach ($activityModelList as $_activityModel) {
//                if ($_activityModel->is_delete === ActivityModel::IS_DELETE)
//                    continue;
//
//                $platformCode = SitePlatform::getPlatformCodeBySiteCode($_activityModel->site_code);
//                $platformParams = $validPlatformParams[$platformCode];
//                $platformName = SitePlatform::getPlatformNameByCode($platformCode);
//
//                //修改活动信息需要同步到IPS
//                $sync_data['geshop_activity_id'] = $_activityModel->id;
//                $sync_data['geshop_activity_name'] = $platformParams['name']."_".$platformName;
//                $sync_data['activity_des'] = $platformParams['description'];
//                \app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);
//            }
           // (new PageCache())->refreshKey($activityModel->site_code, $activityModel->id); //页面缓存刷新，过期
			return app()->helper->arrayResult($this->codeSuccess, '修改成功');
		} catch (\Exception $e) {
			$transaction->rollBack();
			$message = sprintf(
				"%s in %s line %d trace:\n%s",
				$e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
			);
			\Yii::error($message, __METHOD__);
			return app()->helper->arrayResult($this->codeFail, sprintf('修改失败,错误：%s', $e->getMessage()));
		}

	}


	private function arrayDiffAssocRecursive ($array1, $array2)
	{
		$difference = [];
		foreach ($array1 as $key => $value) {
			if (is_array($value)) {
				if (!isset($array2[$key]) || !is_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
					if (!empty($new_diff))
						$difference[$key] = $new_diff;
				}
			} else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
				$difference[$key] = $value;
			}
		}

		return $difference;
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
	private function addActivity ($data, $runValidation = true)
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
	private function getTypeBySiteCode ($siteCode)
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
	public function delete ($id, $runValidation = true)
	{
		$model = ActivityModel::getById($id);

		//检查活动是否加锁，并判断权限
		if (false === ActivityModel::checkAuth($model)) {
			return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
		}

		if (!$model) {
			throw new JsonResponseException($this->codeFail, '自定义活动不存在');
		}


		//同步删除IPS活动
		$sync_data['geshop_activity_id'] = $id;
		$sync_data['del_info'] = [
			[
				'geshop_activity_id' => $id
			]
		];
		\app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);

		// 访问日志记录关联页面id
		$pages = PageModel::find()->select('id, site_code, pipeline, is_native')->where(['activity_id' => $id])->asArray()->all();
		$pageIds = array_column($pages, 'id');
		AccessLogComponent::addPageId($pageIds);

		$transaction = app()->db->beginTransaction();
		try {
			$nativePageIds = array_filter($pages, function ($item) {
				return !empty($item['is_native']);
			});
			if (!empty($nativePageIds) && is_array($nativePageIds)) {
				$nativePages = [];
				foreach ($nativePageIds as $nativePage) {
					$nativePages[] = PageModel::getNativeAppPageId(
						$nativePage['id'],
						$nativePage['site_code'],
						$nativePage['pipeline'],
						SitePlatform::isAppPlatform($nativePage['site_code']) ? false : true
					);
				}
				PageModel::updateAll(['is_delete' => PageModel::IS_DELETE], ['id' => $nativePages]);
			}
			//删除活动同时删除活动下的页面（删除只是修改是否删除状态值）
			PageModel::updateAll(['is_delete' => PageModel::IS_DELETE], ['activity_id' => $id]);

			//只修改状态
			$model->is_delete = ActivityModel::IS_DELETE;
			if (false === $model->update($runValidation)) {
				throw new JsonResponseException($this->codeFail, '删除失败');
			}

			$transaction->commit();
			return app()->helper->arrayResult($this->codeSuccess, '删除成功');
		} catch (\yii\db\Exception $exception) {
			$transaction->rollBack();
			return app()->helper->arrayResult($this->codeFail, '删除失败');
		}
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

        return app()->helper->arrayResult($this->codeSuccess, '设置成功');
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
	public function verify ($activityId, $status, $runValidation = true)
	{
		ignore_user_abort(true);

		//提交审核状态值检查
		$checkRes = $this->beforeVerifyActivity((int)$activityId, (int)$status);
		if ($checkRes['code']) {
			return $checkRes;
		}

		/** @var \app\modules\common\zf\models\ActivityModel $model */
		$model = $checkRes['data']['model'];
		$model->status = $status;
		$verifyTime = time();
		$model->verify_user = app()->user->username;
		$model->verify_time = $verifyTime;

		//若活动要下线，需要将已上线的页面也下线
		if (ActivityModel::STATUS_HAS_OFFLINE === $model->status) {
			$pageRows = PageModel::find()->select('id, pipeline, is_native')->where([
				'activity_id' => $activityId,
				'status'      => [
					PageModel::PAGE_STATUS_HAS_ONLINE
				],
				'is_delete'   => PageModel::NOT_DELETE
			])->asArray()->all();
			if ($pageRows) {
				$pageIds = array_column($pageRows, 'id');

				// 访问日志记录关联页面id
				AccessLogComponent::addPageId($pageIds);

				if (PageModel::updateAll([
					'status'        => PageModel::PAGE_STATUS_HAS_OFFLINE,
					'verify_status' => PageModel::VERIFY_STATUS_PASS_TO_OFFLINE,
					'verify_user'   => app()->user->username,
					'verify_time'   => $verifyTime
				], ['id' => $pageIds])
				) {
					if (empty($pageRows['is_native'])) {
						list($success, $data) = $this->batchCreateOfflineNativePageHtml($pageIds, $activityId);
					} else {
						list($success, $data) = $this->batchCreateOfflinePageHtml($pageIds, $activityId);
					}
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
	public function get ($id)
	{
		$data = ActivityModel::getActivityInfo((int)$id);
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
	public function doLock ($model, $runValidation = true)
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
