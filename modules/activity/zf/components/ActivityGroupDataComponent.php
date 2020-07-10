<?php

namespace app\modules\activity\zf\components;

use app\modules\common\zf\components\CommonPageDesignComponent;
use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\ActivityGroupModel;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\PageGroupModel;
use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\models\PageLayoutModel;
use app\modules\common\zf\models\PageUiModel;
use app\modules\common\zf\models\PageUiComponentDataModel;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use app\base\SiteConstants;
use Yii;

/**
 * ZF 国家站活动数据组件
 *
 * @property \app\modules\activity\zf\components\PageDesignComponent $PageDesignComponent
 */
class ActivityGroupDataComponent extends Component
{
    /** @var string page_id字段 */
    const FIELD_PAGE_ID = 'page_id';

    /** @var array 更新日志信息 */
    private $updateMessage = [];

    public function batchActivityRepair($params)
    {
        if (!app()->user->admin->is_super) {
            return app()->helper->arrayResult($this->codeFail, '超级管理员才能访问');
        }

        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        if (empty($params['ids'])) {
            return app()->helper->arrayResult($this->codeFail, '无效参数ids');
        }

        if (empty($params['src_pipeline'])) {
            return app()->helper->arrayResult($this->codeFail, '无效参数src_pipeline');
        }

        if (empty($params['target_pipelines'])) {
            return app()->helper->arrayResult($this->codeFail, '无效参数target_pipelines');
        }

        $batchRepairMessage = [];
        $ids = explode(SiteConstants::CHAR_COMMA, $params['ids']);
        $targetPipelineList = explode(SiteConstants::CHAR_COMMA, $params['target_pipelines']);
        foreach ($ids as $activityId) {
            try {

                /** @var \app\modules\common\zf\models\ActivityModel $activityModel */
                $activityModel = ActivityModel::getById($activityId);
                if ((!$activityModel) || ActivityModel::IS_DELETE === (int) $activityModel->is_delete) {
                    throw new Exception(sprintf('活动ID %s 不存在', $activityId));
                }

                $this->activityRepair(
                    $siteGroupCode, $activityModel->group_id, $params['src_pipeline'], $targetPipelineList, true
                );
                $batchRepairMessage[$activityId] = $this->updateMessage;

            } catch (\Exception $e) {
                $batchRepairMessage[$activityId] = $e->getMessage();
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '修复信息', $batchRepairMessage);
    }

    /**
     * 添加活动分组渠道，不能新增端口
     *
     * @param string $siteGroupCode 站点分组简码,如：zf
     * @param ActivityGroupModel $activityGroupModel
     * @param array $activityModelList
     * @param array $srcPlatformsSyncInfo 原平台渠道信息，新增渠道数据同步来源
     * 格式:
     * - pc 平台
     * - pc.pipeline 渠道简码,如：ZF
     * - pc.lang 语言简码，如：en
     *
     * @param array $targetPlatformsAddInfo 要新增的渠道信息
     * 格式:
     * - pc 平台下渠道列表
     * - pc.ZF 渠道下语言列表
     * - pc.ZF.0 语言简码
     *
     * @param bool $isCopyComponent 是否需要复制组件数据(包含布局和UI组件)
     *
     * @throws Exception
     */
    public function addActivityGroupPipeline($siteGroupCode, $activityGroupModel, $activityModelList, $srcPlatformsSyncInfo,
                                             $targetPlatformsAddInfo, $isCopyComponent)
    {
        if (empty($srcPlatformsSyncInfo) || empty($targetPlatformsAddInfo)) {
            throw new Exception('参数错误');
        }

        $activityGroupSupportList = json_decode($activityGroupModel->support_list, true);
        $configPlatformPipelineLangList = PipelineUtils::getSiteSpecialAllPlatformPipelineLangList($siteGroupCode);
        $configSupportPipelineLangList = $configPlatformPipelineLangList['support_pipelines'];

        // 检查原同步渠道是否存在
        foreach ($srcPlatformsSyncInfo as $_platformCode => $_syncInfo) {
            if (!isset($activityGroupSupportList[$_platformCode][ $_syncInfo['pipeline'] ])) {
                $message = sprintf('端口 %s 原渠道 %s 不存在', SitePlatform::getPlatformNameByCode($_platformCode), $_syncInfo['pipeline']);
                throw new Exception($message);
            }

            if (!in_array($_syncInfo['lang'], $activityGroupSupportList[$_platformCode][ $_syncInfo['pipeline'] ])) {
                $message = sprintf(
                    '端口 %s 原渠道 %s 语言 %s 不存在',
                    SitePlatform::getPlatformNameByCode($_platformCode),
                    $_syncInfo['pipeline'],
                    $_syncInfo['lang']
                );
                throw new Exception($message);
            }
        }

        // 检查新增渠道是否支持
        foreach ($targetPlatformsAddInfo as $_platformCode => $_addInfo) {
            foreach ($_addInfo as $_pipelineCode => $_langList) {
                if (!isset($configSupportPipelineLangList[ $_platformCode ][ $_pipelineCode ])) {
                    $message = sprintf('端口 %s 不支持渠道 %s', SitePlatform::getPlatformNameByCode($_platformCode), $_pipelineCode);
                    throw new Exception($message);
                }

                foreach ($_langList as $_key => $_langCode) {
                    if (!isset($configSupportPipelineLangList[ $_platformCode ][ $_pipelineCode ]['lang_list'][$_langCode])) {
                        $message = sprintf(
                            '端口 %s 不支持渠道 %s 语言 %s',
                            SitePlatform::getPlatformNameByCode($_platformCode), $_pipelineCode, $_langCode
                        );
                        throw new Exception($message);
                    }

                    // 跳过已存在的渠道语言
                    if (isset( $activityGroupSupportList[ $_platformCode ][ $_pipelineCode ])) {
                        if (in_array($_langCode, $activityGroupSupportList[$_platformCode][$_pipelineCode])) {
                            unset($targetPlatformsAddInfo[$_platformCode][$_pipelineCode][$_key]);
                        }
                    }
                }

                // 跳过已存在的渠道
                if (empty($targetPlatformsAddInfo[$_platformCode][$_pipelineCode])) {
                    unset($targetPlatformsAddInfo[$_platformCode][$_pipelineCode]);
                }
            }

            if (empty($targetPlatformsAddInfo[$_platformCode])) {
                unset($targetPlatformsAddInfo[$_platformCode]);
            }
        }

        if (empty($targetPlatformsAddInfo)) {
            return;
        }

        $this->updateMessage = [];
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            // 更新活动分组信息
            $this->updateActivityGroupInfo($activityGroupModel, $targetPlatformsAddInfo);

            // 新增所有平台下渠道页面
            foreach ($activityModelList as $activityModel) {

                // 更新端口下活动信息
                $this->updateActivityInfo($targetPlatformsAddInfo, $activityModel);

                // 新增端口下渠道页面
                $this->addSinglePlatformPagePipeline(
                    $srcPlatformsSyncInfo, $targetPlatformsAddInfo, $activityModel, $isCopyComponent
                );
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            $format = '新增渠道出错，错误信息: %s';
            Yii::error(sprintf($format, $e->getMessage() . $e->getTraceAsString()), __METHOD__);
            throw new Exception(sprintf($format, $e->getMessage()));
        }
    }

    /**
     * 活动分组新增渠道
     *
     * @param string $siteGroupCode 站点分组简码,如：zf
     * @param int $groupId 活动分组ID
     * @param string $srcPipelineCode 原渠道编码，新增渠道信息从这个渠道同步
     * @param array $addPipelineList 要新增的渠道编码列表
     * @param bool $isCopyComponent 是否需要复制组件数据(包含布局和UI组件)
     *
     * @throws Exception
     * @return boolean
     */
    private function activityRepair($siteGroupCode, $groupId, $srcPipelineCode, $addPipelineList, $isCopyComponent)
    {
        /** @var \app\modules\common\zf\models\ActivityGroupModel $activityGroupModel */
        $activityGroupModel = ActivityGroupModel::find()->where(['id' => $groupId])->one();
        if (empty($activityGroupModel)) {
            throw new Exception(sprintf('活动分组ID %d 不存在', $groupId));
        }

        $activityPlatformList = explode(SiteConstants::CHAR_COMMA, $activityGroupModel->platform_list);
        $activityGroupSupportList = json_decode($activityGroupModel->support_list, true);
        $pipelineLangList = PipelineUtils::getSiteSpecialAllPlatformPipelineLangList($siteGroupCode);
        $configSupportPipelineLangList = $pipelineLangList['support_pipelines'];

        $srcPlatformsSyncInfo = []; // 源平台渠道信息
        $targetPlatformsInfo = []; // 需求新增的渠道
        foreach ($activityPlatformList as $platformCode) {

            // 检查原同步渠道是否存在
            if (!isset($activityGroupSupportList[$platformCode][$srcPipelineCode])) {
                $message = sprintf('端口 %s 原渠道 %s 不存在', SitePlatform::getPlatformNameByCode($platformCode), $srcPipelineCode);
                throw new Exception($message);
            }

            $srcPlatformsSyncInfo[$platformCode] = [
                'pipeline' => $srcPipelineCode,
                'lang' => $activityGroupSupportList[$platformCode][$srcPipelineCode][0]
            ];

            //计算各端口要新增的渠道，语言
            foreach ($addPipelineList as $_addPipelineCode) {
                // 跳过端口下不支持和已经存在的渠道
                if (!isset($configSupportPipelineLangList[$platformCode][$_addPipelineCode])
                        || isset($activityGroupSupportList[$platformCode][$_addPipelineCode])) {
                    continue;
                }

                $targetPlatformsInfo[$platformCode][$_addPipelineCode] = array_column(
                    $configSupportPipelineLangList[$platformCode][$_addPipelineCode]['lang_list'],
                    'code'
                );
            }
        }

        if (empty($targetPlatformsInfo)) {
            throw new Exception(sprintf('活动分组ID %d 没有要新增的渠道', $groupId));
        }

        $activityModelList = ActivityModel::find()->where(['group_id' => $activityGroupModel->id])->all();
        if (empty($activityModelList)) {
            throw new Exception(sprintf('活动组ID %s 下没有活动信息', $activityGroupModel->id));
        }

        $this->updateMessage = [];
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            // 更新活动分组信息
            $this->updateActivityGroupInfo($activityGroupModel, $targetPlatformsInfo);

            // 新增所有平台下渠道页面
            foreach ($activityModelList as $activityModel) {

                // 更新端口下活动信息
                $this->updateActivityInfo($targetPlatformsInfo, $activityModel);

                // 新增端口下渠道页面
                $this->addSinglePlatformPagePipeline(
                    $srcPlatformsSyncInfo, $targetPlatformsInfo, $activityModel, $isCopyComponent
                );
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $format = '活动组ID %s 修复出错，错误信息: %s';
            Yii::error(sprintf($format, $activityGroupModel->id, $e->getMessage() . $e->getTraceAsString()), __METHOD__);
            throw new Exception(sprintf($format, $activityGroupModel->id, $e->getMessage()));
        }
    }

    /**
     * @param array $srcPlatformsInfo 原平台渠道信息，新增渠道数据同步来源
     * 格式:
     * - pc 平台
     * - pc.pipeline 渠道简码,如：ZF
     * - pc.lang 语言简码，如：en
     *
     * @param array $targetPlatformsInfo 要新增的渠道信息
     * 格式:
     * - pc 平台下渠道列表
     * - pc.ZF 渠道下语言列表
     * - pc.ZF.0 语言简码
     *
     * @param ActivityModel $activityModel 活动Model对象
     * @param bool $isCopyComponent 是否需要复制组件数据(包含布局和UI组件)
     */
    private function addSinglePlatformPagePipeline($srcPlatformsInfo, $targetPlatformsInfo, $activityModel, $isCopyComponent) {
        $platformCode = SitePlatform::getPlatformCodeBySiteCode($activityModel->site_code);

        // 当前端口下没有要新增的渠道
        if (!isset($targetPlatformsInfo[$platformCode])) {
            $this->updateMessage[] = sprintf('活动分组 %d 下端口 %s 没有新增渠道', $activityModel->group_id, $platformCode);
            return;
        }

        if (!isset($srcPlatformsInfo[$platformCode])) {
            $this->updateMessage[] = sprintf('活动分组 %d 下端口 %s 没有原渠道', $activityModel->group_id);
            return;
        }

        $srcPipelineInfo = $srcPlatformsInfo[$platformCode];
        //查询单个活动下所有子页面指定被同步渠道的页面信息
        $pageModelList = PageModel::find()->where([
            'activity_id'   => $activityModel->id,
            'pipeline'      => $srcPipelineInfo['pipeline']
        ])->all();

        if (!empty($pageModelList)) {
            /** @var \app\modules\common\zf\models\PageModel $srcPageModel */
            foreach ($pageModelList as $srcPageModel) {

                // 添加活动子页面的渠道
                $this->addPlatformPipelinePages(
                    $targetPlatformsInfo, $platformCode, $srcPageModel, $srcPipelineInfo['lang'], $isCopyComponent
                );
            }
        }
    }

    /**
     * 新增单个平台下单个子页面的多个渠道
     *
     * @param array $targetPlatformsInfo 要新增的渠道信息
     * 格式:
     * - pc 平台下渠道列表
     * - pc.ZF 渠道下语言列表
     * - pc.ZF.0 语言简码
     *
     * @param string $platformCode 平台简码, 如：pc
     *
     * @param PageModel $srcPageModel 原 PageModel 对象,新增渠道页面信息同步这个对象信息
     * @param string $srcLangCode 原语言简码，新增渠道页面信息同步这个语言信息
     * @param bool $isCopyComponent 是否需要复制组件数据(包含布局和UI组件)
     * @throws Exception
     */
    private function addPlatformPipelinePages($targetPlatformsInfo, $platformCode, $srcPageModel, $srcLangCode, $isCopyComponent=false)
    {
        $pipelineLangList = $targetPlatformsInfo[$platformCode];
        $batchData = [];
        foreach ($pipelineLangList as $pipelineCode => $targetLangList) {
            $targetPageModel = PageModel::find()
                ->where(['group_id' =>  $srcPageModel->group_id, 'pipeline' => $pipelineCode])
                ->one();
            if (!$targetPageModel) {
                $defaultName = app()->params['site'][SITE_GROUP_CODE]['pipeline_default_lang'][$pipelineCode];
                //保存页面信息
                $targetPageModel = new PageModel();
                $targetPageModel->group_id = $srcPageModel->group_id;
                $targetPageModel->activity_id = $srcPageModel->activity_id;
                $targetPageModel->site_code = $srcPageModel->site_code;
                $targetPageModel->pipeline = $pipelineCode;
                $targetPageModel->auto_refresh = $srcPageModel->auto_refresh;
                $targetPageModel->refresh_time = $srcPageModel->refresh_time;
                $targetPageModel->end_time = $srcPageModel->end_time;
                $targetPageModel->default_lang = $defaultName;
                $targetPageModel->is_delete = $srcPageModel->is_delete;
                $targetPageModel->status = $srcPageModel->status;
                $targetPageModel->is_native = $srcPageModel->is_native;
                $targetPageModel->is_blog = $srcPageModel->is_blog;

                if (!$targetPageModel->insert(true)) {
                    throw new Exception('添加子页面分组失败');
                }
                $this->updateMessage[] = sprintf(
                    '新增子页面成功: %s',
                    json_encode([
                        'platform' => $platformCode,
                        'pipeline' => $targetPageModel->pipeline,
                        'id'       => $targetPageModel->id
                    ])
                );

                //保存页面分组
                /** @var \app\modules\common\zf\models\PageGroupModel $srcPageGroupModel */
                $srcPageGroupModel = PageGroupModel::find()
                    ->where(['page_id' => $srcPageModel->id, 'pipeline' => $srcPageModel->pipeline])
                    ->one();

                /** @var \app\modules\common\zf\models\PageGroupModel $targetPageGroupModel */
                $targetPageGroupModel = new PageGroupModel();
                $targetPageGroupModel->activity_group_id = $srcPageGroupModel->activity_group_id;
                $targetPageGroupModel->page_group_id = $srcPageGroupModel->page_group_id;
                $targetPageGroupModel->platform_type = SitePlatform::getPlatformTypeByPlatformCode($platformCode);
                $targetPageGroupModel->page_id = $targetPageModel->id;
                $targetPageGroupModel->pipeline = $pipelineCode;
                if (!$targetPageGroupModel->insert(true)) {
                    throw new Exception('添加子页面分组失败');
                }
                $this->updateMessage[] = sprintf(
                    '新增子页面分组成功,分组信息: %s',
                    json_encode(ArrayHelper::toArray($targetPageGroupModel))
                );
            }

            // 复制页面语言
            $this->copyPageAllLanguage($targetPlatformsInfo, $srcPageModel, $srcLangCode, $targetPageModel, $targetLangList);

            // 复制组件数据
            if (($targetPageModel->is_delete == PageModel::NOT_DELETE) && $isCopyComponent) {
                foreach ($targetLangList as $targetLangCode) {
                    $this->copyPageComponentData($srcPageModel, $srcLangCode, $targetPageModel, $targetLangCode, true);
                }
            }

            $batchData[] = ['pipeline' => $targetPageModel->pipeline, 'lang' => $targetPageModel->default_lang];
        }

        // 页面上线
//        if (!empty($batchData)) {
//            (new CommonPageDesignComponent())->activityBatchRelease(json_encode($batchData), $srcPageModel->group_id);
//        }
    }

    /**
     * 复制子页面及语言信息
     * @param array $targetPlatformsInfo 要新增的渠道信息
     * 格式:
     * - pc 平台下渠道列表
     * - pc.ZF 渠道下语言列表
     * - pc.ZF.0 语言简码
     *
     * @param PageModel $srcPageModel 要复制的子页面对象
     * @param string $srcLangCode 要复制的子页面下语言简码
     * @param PageModel $targetPageModel 目标子页面对象
     * @param array $targetLangList 目标子页面新增语言列表
     */
    private function copyPageAllLanguage($targetPlatformsInfo, $srcPageModel, $srcLangCode, $targetPageModel, $targetLangList)
    {
        //保存页面语言
        /** @var \app\modules\common\zf\models\PageLanguageModel $srcPageLanguageModel */
        $srcPageLanguageModel = PageLanguageModel::findOne([
            'page_id' => $srcPageModel->id,
            'lang'    => $srcLangCode
        ]);

        $targetPageLanguageData = ArrayHelper::toArray($srcPageLanguageModel);
        unset(
            $targetPageLanguageData['id'],
            $targetPageLanguageData['page_url'],
            $targetPageLanguageData['end_url'],
            $targetPageLanguageData['local_files'],
            $targetPageLanguageData['s3_files'],
            $targetPageLanguageData['status']
        );

        $columnNames = array_keys($targetPageLanguageData);
        foreach ($targetLangList as $targetLangCode) {
            $targetPageLanguageData['page_id'] = $targetPageModel->id;
            $targetPageLanguageData['lang'] = $targetLangCode;
            $targetPageLanguageData['tpl_id'] = 0;

            //关联跳转URL
            $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($targetPageModel->site_code);
            $wapSiteCode = SitePlatform::getWapPlatformSiteCode($siteGroupCode);
            $pipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($wapSiteCode);
            $targetPipeline = $targetPageModel->pipeline;
            if (SitePlatform::isPcPlatform($targetPageModel->site_code)
                    && isset($targetPlatformsInfo[SitePlatform::PLATFORM_CODE_WAP][$targetPipeline][$targetLangCode])
                    && isset($pipelineList[$targetPipeline][$targetLangCode])) {

                $_targetRedirectUrl = trim($pipelineList[$targetPipeline][$targetLangCode], '/') . '/';
                $_targetRedirectUrl .= $targetPageLanguageData['url_name'] . '.html';
                $targetPageLanguageData['redirect_url'] = $_targetRedirectUrl;
            } else {
                $targetPageLanguageData['redirect_url'] = '';
            }

            $command = $srcPageLanguageModel->getDb()->createCommand();
            $command->batchInsert($srcPageLanguageModel->tableName(), $columnNames, [array_values($targetPageLanguageData)])
                ->execute();
            $this->updateMessage[] = sprintf(
                '新增子页面语言成功：%s',
                json_encode(['page_id' => $targetPageLanguageData['page_id'], 'lang' => $targetPageLanguageData['lang']])
            );
        }
    }

    /**
     * 复制页面数据，包含页面样式，组件及组件数据
     * 注意：会先清除目标页面的装修数据组件及组件数据
     *
     * @param PageModel $srcPageModel 原PageModel对象
     * @param string $srcLangCode 原语言简码，如：en
     * @param PageModel $targetPageModel 目标PageModel对象
     * @param string $targetLangCode 目标语言简码，如：en
     * @param bool $isCopySku 是否复制商品SKU
     * @throws Exception
     */
    public function copyPageComponentData($srcPageModel, $srcLangCode, $targetPageModel, $targetLangCode, $isCopySku)
    {
        if (empty($srcPageModel) || empty($srcLangCode) || empty($targetPageModel) || empty($targetLangCode)) {
            throw new Exception('参数不正确');
        }

        // 复制Layout组件和UI组件数据
        $copyData = [
            'from_page_id'  => $srcPageModel->id,
            'from_lang'     => $srcLangCode,
            'to_page_id'    => $targetPageModel->id,
            'to_lang'       => $targetLangCode
        ];
        $result = $this->PageDesignComponent->copyPage($copyData, $isCopySku);
        if ($result['code'] == $this->codeFail) {
            throw new Exception('复制组件数据出错: '. $result['message']);
        }

        $this->updateMessage[] = sprintf('复制子页面组件数据成功：%s', json_encode($copyData));

        // 替换组件数据中的活动URL前缀
        $configPipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($srcPageModel->site_code);
        $srcPageUrlPrefix = $configPipelineList[$srcPageModel->pipeline][$srcLangCode];
        $targetPageUrlPrefix = $configPipelineList[$targetPageModel->pipeline][$targetLangCode];

        // 原链接前缀
        $srcPageUrlPrefix = str_replace('/promotion', '', $srcPageUrlPrefix);
        $srcPageUrlPrefix = str_replace('/app', '', $srcPageUrlPrefix);

        // 要替换的链接前缀
        $targetPageUrlPrefix = str_replace('/promotion', '', $targetPageUrlPrefix);
        $targetPageUrlPrefix = str_replace('/app', '', $targetPageUrlPrefix);

        $inSql = sprintf(
            'SELECT id FROM %s WHERE layout_id IN(SELECT id FROM %s WHERE page_id=%d AND lang=\'%s\')',
            PageUiModel::tableName(),
            PageLayoutModel::tableName(),
            $targetPageModel->id,
            $targetLangCode
        );

        $sql = sprintf(
            'UPDATE %s SET `value`=REPLACE (`value`, \'%s\', \'%s\') WHERE component_id IN(%s)',
            PageUiComponentDataModel::tableName(),
            str_replace('/', '\\\/', $srcPageUrlPrefix),
            str_replace('/', '\\\/', $targetPageUrlPrefix),
            $inSql
        );
        $rowSize = PageUiComponentDataModel::getDb()->createCommand($sql)->execute();

        $format = '%s活动 %d 渠道 %s 页面 %d 语言 %s 替换URL前缀 %s => %s 更新记录数: %d';
        $this->updateMessage[] = sprintf(
            $format,
            SitePlatform::getPlatformCodeBySiteCode($targetPageModel->site_code),
            $targetPageModel->activity_id,
            $targetPageModel->pipeline,
            $targetPageModel->id,
            $targetLangCode,
            $srcPageUrlPrefix,
            $targetPageUrlPrefix,
            $rowSize
        );
    }

    /**
     * 更新活动信息
     *
     * @param array $targetPlatformsInfo 要新增的渠道信息
     * 格式:
     * - pc 平台下渠道列表
     * - pc.ZF 渠道下语言列表
     * - pc.ZF.0 语言简码
     *
     * @param ActivityModel $activityModel 活动Model对象
     * @throws Exception
     */
    private function updateActivityInfo($targetPlatformsInfo, $activityModel)
    {
        $platformCode = SitePlatform::getPlatformCodeBySiteCode($activityModel->site_code);
        //当前端口下没有要新增的渠道
        if (!isset($targetPlatformsInfo[$platformCode])) {
            return;
        }

        $_srcLangList = json_decode($activityModel->lang, true);
        $_newLangList = ArrayHelper::merge($_srcLangList, $targetPlatformsInfo[$platformCode]);

        //排序
        $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($activityModel->site_code);
        $pipelineLangList = PipelineUtils::getSiteSpecialAllPlatformPipelineLangList($siteGroupCode);
        $_sortedLangList = [];
        foreach (array_column($pipelineLangList['all_pipelines'], 'code') as $_code) {
            if (isset($_newLangList[$_code])) {
                $_sortedLangList[$_code] = $_newLangList[$_code];
            }
        }

        $activityModel->pipeline = join(SiteConstants::CHAR_COMMA, array_keys($_sortedLangList));
        $activityModel->lang = json_encode($_sortedLangList);
        if (!$activityModel->save(true)) {
            throw new Exception('更新活动失败');
        }

        $this->updateMessage[] = sprintf(
            '更新端口[%s]活动[%s]成功,新渠道信息:%s',
            $platformCode,
            $activityModel->id,
            $activityModel->lang
        );
    }

    private function updateActivityGroupInfo($activityGroupModel, $targetPlatformsAddInfo)
    {
        $activityPipelineLangList = json_decode($activityGroupModel->lang_list, true);
        $activityGroupSupportList = json_decode($activityGroupModel->support_list, true);

        foreach ($targetPlatformsAddInfo as $_platformCode => $_pipelineLangList) {
            $activityPipelineLangList = ArrayHelper::merge($activityPipelineLangList, $_pipelineLangList);

            $activityGroupSupportList[$_platformCode] = ArrayHelper::merge(
                $activityGroupSupportList[$_platformCode], $_pipelineLangList
            );
        }

        // 语言去重复
        $activityPipelineLangList = array_map(function($langList) {
            return array_unique($langList);
        }, $activityPipelineLangList);

        foreach ($activityGroupSupportList as $_platformCode => $_pipelineLangList) {
            $_pipelineLangList = array_map(function($langList) {
                return array_unique($langList);
            }, $_pipelineLangList);
            $activityGroupSupportList[$_platformCode] = $_pipelineLangList;
        }

        //更新活动组支持渠道和语言
        $activityGroupModel->lang_list = json_encode($activityPipelineLangList);
        $activityGroupModel->support_list = json_encode($activityGroupSupportList);
        if (!$activityGroupModel->save(true)) {
            throw new Exception(sprintf('更新活动分组 %s 失败', $activityGroupModel->id));
        }

        $this->updateMessage[] = sprintf('更新活动分组[%s]成功,新渠道信息:%s', $activityGroupModel->id, $activityGroupModel->lang_list);
    }

}
