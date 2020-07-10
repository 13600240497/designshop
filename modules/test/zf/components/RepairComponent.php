<?php

namespace app\modules\test\zf\components;


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
 * 版本升级，旧数据处理组件
 */
class RepairComponent extends Component
{

    /**
     * 修复活动组支持列表数据
    /** @var array 更新日志信息 */
    private $updateMessage = [];


    /**
     * 修复首页，将活动页面类型设置为B首页类型
     */
    public function resetHomeB()
    {
        $pageModelList = PageModel::find()->where([
            'site_code'   => 'zf-pc',
            'activity_id' => 0,
            'group_id'    => '451977761ac18d76f9e0d935c139161a'
        ])->all();

        $pipelineList = [];
        if ($pageModelList) {
            /** @var \app\modules\common\zf\models\PageModel[] $pageModel */
            foreach($pageModelList as $pageModel) {
                if ($pageModel->home_type == 0) {
                    $pageModel->home_type = 1;
                    $pageModel->save();
                    $pipelineList[] = $pageModel->pipeline;
                }
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, $pipelineList);
    }

    /**
     * 把ZF活动首页发布权限更新到专题页
     *
     * 注意： 执行一次就可以
     */
    public function updateSpecialPermissions()
    {
        $sql = 'UPDATE admin_site_privilege SET special_permissions=home_permissions';
        ActivityGroupModel::getDb()->createCommand($sql)->execute();
        return app()->helper->arrayResult($this->codeSuccess, 'ok');
    }

    /**
     * 修复活动组支持列表数据
     *
     * 注意： 执行一次就可以
     */
    public function repairActivityGroupSupportList()
    {
        $activityGroupModelList = ActivityGroupModel::find()->all();
        $configPipelineLangList = PipelineUtils::getSiteSpecialAllPlatformPipelineLangList('zf');
        $supportPipelineLangList = $configPipelineLangList['support_pipelines'];

        $batchRepairMessage = [];
        /** @var \app\modules\common\zf\models\ActivityGroupModel $activityGroupModel */
        foreach ($activityGroupModelList as $activityGroupModel) {
            $activityPlatformList = explode(SiteConstants::CHAR_COMMA, $activityGroupModel->platform_list);
            $activityPipelineLangList = json_decode($activityGroupModel->lang_list, true);

            $activitySupportList = [];
            foreach ($activityPlatformList as $platformCode) {
                foreach ($activityPipelineLangList as $pipelineCode => $langList) {
                    if (isset($supportPipelineLangList[$platformCode][$pipelineCode]))
                        $activitySupportList[$platformCode][$pipelineCode] = $langList;
                }
            }

            $activityGroupModel->support_list = json_encode($activitySupportList);
            if (!$activityGroupModel->save(true)) {
                $batchRepairMessage[] = sprintf('活动分组ID %s 修复失败', $activityGroupModel->id);
            } else {
                $batchRepairMessage[] = sprintf('活动分组ID %s 修复成功', $activityGroupModel->id);
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '修复信息', $batchRepairMessage);
    }

    /**
     * ZF国家站二期活动组件内容URL前缀替换
     * @param array $params
     * @return array
     */
    public function batchRepairPageUrlPrefix($params)
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
        $activityIds = explode(SiteConstants::CHAR_COMMA, $params['ids']);
        $targetPipelineList = explode(SiteConstants::CHAR_COMMA, $params['target_pipelines']);
        $srcPipelineCode = $params['src_pipeline'];

        foreach ($activityIds as $activityId) {
            try {
                $this->updateMessage = [];
                $this->repairPageUrlPrefix($activityId, $srcPipelineCode, $targetPipelineList);
                $batchRepairMessage[$activityId] = $this->updateMessage;
            } catch (\Exception $e) {
                $batchRepairMessage[$activityId] = $e->getMessage();
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '修复信息', $batchRepairMessage);
    }

    private function repairPageUrlPrefix($activityId, $srcPipelineCode, $targetPipelineCodeList)
    {
        /** @var \app\modules\common\zf\models\ActivityModel $activityModel */
        $activityModel = ActivityModel::getById($activityId);
        if ((!$activityModel) || ActivityModel::IS_DELETE === (int) $activityModel->is_delete) {
            throw new Exception(sprintf('活动ID %s 不存在', $activityId));
        }

        $platformActivityModelList = ActivityModel::find()->where(['group_id' => $activityModel->group_id])->all();

        /** @var \app\modules\common\zf\models\ActivityModel $platformActivityModel */
        foreach ($platformActivityModelList as $platformActivityModel) {
            $configPipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($platformActivityModel->site_code);
            $activitySupportPipelineList = json_decode($platformActivityModel->lang, true);
            if (!isset($activitySupportPipelineList[$srcPipelineCode])) {
                $this->updateMessage[] = sprintf('活动 %d 没有原渠道 %s', $platformActivityModel->id, $srcPipelineCode);
                continue;
            }

            $srcLangCode = $activitySupportPipelineList[$srcPipelineCode][0];
            $srcPageUrlPrefix = $configPipelineList[$srcPipelineCode][$srcLangCode];
            $srcPageUrlPrefix = str_replace('/promotion', '', $srcPageUrlPrefix);
            $srcPageUrlPrefix = str_replace('/app', '', $srcPageUrlPrefix);

            $pageModelList = PageModel::find()->where([
                    'activity_id'   => $platformActivityModel->id,
                    'pipeline'      => $targetPipelineCodeList
                ])->all();

            /** @var \app\modules\common\zf\models\PageModel $targetPageModel */
            foreach ($pageModelList as $targetPageModel) {
                foreach ($activitySupportPipelineList[$targetPageModel->pipeline] as $targetLangCode) {
                    $targetPageUrlPrefix = $configPipelineList[$targetPageModel->pipeline][$targetLangCode];
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
                        SitePlatform::getPlatformCodeByPlatformType($platformActivityModel->type),
                        $platformActivityModel->id,
                        $targetPageModel->pipeline,
                        $targetPageModel->id,
                        $targetLangCode,
                        $srcPageUrlPrefix,
                        $targetPageUrlPrefix,
                        $rowSize
                    );
                }
            }
        }
    }
}
