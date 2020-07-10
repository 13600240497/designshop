<?php

namespace app\modules\activity\zf\components;

use app\base\PipelineUtils;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\modules\common\zf\components\CommonActivityComponent;
use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\ActivityGroupModel;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\PageGroupModel;
use app\modules\common\zf\models\PageLanguageModel;
use ego\base\JsonResponseException;
use yii\helpers\ArrayHelper;

/**
 * 自定义活动组件
 */
class ActivityComponent extends CommonActivityComponent
{

    /**
     * @inheritdoc
     */
    protected function getConfigPipelineList($siteCode) {
        return PipelineUtils::getConfigSpecialSupportPipelineList($siteCode);
    }

    /**
     * @inheritdoc
     */
    protected function getPageValidPipelineList($siteCode, $pipelineList) {
        return PipelineUtils::getSiteSpecialPageValidPipelineList($siteCode, $pipelineList);
    }

    /**
     * 活动权限加/解锁
     *
     * @param $id
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lock($id)
    {
        $model = ActivityModel::getById($id);
        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($model)) {
            throw new JsonResponseException($this->codeFail, '只有活动创建者才具有此权限');
        }
    
        if (!$model) {
            throw new JsonResponseException($this->codeFail, '自定义活动不存在');
        }
        
        return $this->doLock($model);
    }

    /**
     * 修复老活动数据
     *
     * @param array $params
     *
     * @return array
     * @throws JsonResponseException
     * @throws Exception
     */
    public function repair($params)
    {
        if (!app()->user->admin->is_super) {
            return app()->helper->arrayResult($this->codeFail, '超级管理员才能访问');
        }

        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        if (empty($params['id']) || !is_numeric($params['id'])) {
            return app()->helper->arrayResult($this->codeFail, '无效参数id');
        }

        if (empty($params['pipeline'])) {
            return app()->helper->arrayResult($this->codeFail, '无效参数pipeline');
        }

        $addPipelineList = explode(SiteConstants::CHAR_COMMA, $params['pipeline']);
        $activityId = (int) $params['id'];


        /** @var \app\modules\common\zf\models\ActivityModel $activityModel */
        $activityModel = ActivityModel::getById($activityId);
        if ((!$activityModel) || ActivityModel::IS_DELETE === (int) $activityModel->is_delete) {
            throw new JsonResponseException($this->codeFail, '活动不存在');
        }

        /** @var \app\modules\common\zf\models\ActivityGroupModel $activityGroupModel */
        $activityGroupModel = ActivityGroupModel::find()->where(['id' => $activityModel->group_id])->one();
        $activityPlatformList = explode(SiteConstants::CHAR_COMMA, $activityGroupModel->platform_list);
        $activityPipelineLangList = json_decode($activityGroupModel->lang_list, true);

        //新增渠道时默认数据来源
        $defaultCopyPipelineInfo = NULL;
        if (isset($activityPipelineLangList['ZF'])) {
            $defaultCopyPipelineInfo = [
                'pipeline' => 'ZF',
                'lang' => $activityPipelineLangList['ZF'][0]
            ];
        } else {
            $_pipelineCode = array_keys($activityPipelineLangList)[0];
            $defaultCopyPipelineInfo = [
                'pipeline' => $_pipelineCode,
                'lang' => $activityPipelineLangList[$_pipelineCode][0]
            ];
        }

        //计算各端口要新增的渠道，语言
        $pipelineLangList = PipelineUtils::getSiteSpecialAllPlatformPipelineLangList($siteGroupCode);
        $supportPipelineLangList = $pipelineLangList['support_pipelines'];
        $validPlatformParams = $toAddPipelineLangList = [];
        foreach ($activityPlatformList as $platformCode) {
            foreach ($addPipelineList as $_addPipelineCode) {
                if (!isset($supportPipelineLangList[$platformCode][$_addPipelineCode])
                        || isset($activityPipelineLangList[$_addPipelineCode]))
                    continue;

                $validPlatformParams[$platformCode][$_addPipelineCode] = array_column(
                    $supportPipelineLangList[$platformCode][$_addPipelineCode]['lang_list'],
                    'code'
                );

                if (!isset($toAddPipelineLangList[$_addPipelineCode])) {
                    $toAddPipelineLangList[$_addPipelineCode] = [];
                }

                $toAddPipelineLangList[$_addPipelineCode] = array_unique(ArrayHelper::merge(
                    $toAddPipelineLangList[$_addPipelineCode],
                    $validPlatformParams[$platformCode][$_addPipelineCode]
                ));
            }
        }


        if (empty($validPlatformParams)) {
            throw new JsonResponseException($this->codeFail, '没有要新增的渠道');
        }

        $activityModelList = ActivityModel::find()->where(['group_id' => $activityGroupModel->id])->all();
        if (empty($activityModelList)) {
            throw new JsonResponseException($this->codeFail, '活动不存在');
        }

        $updateMessage = [];
        //事物开始
        $transaction = app()->db->beginTransaction();
        try {

            //更新活动组支持渠道和语言
            $newActivityPipelineLangList = ArrayHelper::merge($activityPipelineLangList, $toAddPipelineLangList);
            $activityGroupModel->lang_list = json_encode($newActivityPipelineLangList);
            if (!$activityGroupModel->save(true)) {
                throw new Exception('更新活动分组失败');
            }
            $updateMessage[] = sprintf('更新活动分组[%s]成功,新渠道信息:%s', $activityGroupModel->id, $activityGroupModel->lang_list);

            //更新各个端口活动渠道和语言
            /** @var \app\modules\common\zf\models\ActivityModel $_activityModel */
            foreach ($activityModelList as $_activityModel) {
                $_platformCode = SitePlatform::getPlatformCodeBySiteCode($_activityModel->site_code);
                //当前端口下没有要新增的渠道
                if (!isset($validPlatformParams[$_platformCode]))
                    continue;

                $_srcLangList = json_decode($_activityModel->lang, true);
                $_newLangList = ArrayHelper::merge($_srcLangList, $validPlatformParams[$_platformCode]);

                //排序
                $_sortedLangList = [];
                foreach (array_column($pipelineLangList['all_pipelines'], 'code') as $_code) {
                    if (isset($_newLangList[$_code])) {
                        $_sortedLangList[$_code] = $_newLangList[$_code];
                    }
                }

                $_activityModel->pipeline = join(SiteConstants::CHAR_COMMA, array_keys($_sortedLangList));
                $_activityModel->lang = json_encode($_sortedLangList);
                if (!$_activityModel->save(true)) {
                    throw new Exception('更新活动失败');
                }
                $updateMessage[] = sprintf(
                    '更新端口[%s]活动[%s]成功,新渠道信息:%s',
                    $_platformCode,
                    $_activityModel->id,
                    $_activityModel->lang
                );
            }

            //更新活动子页面
            $activityIds = array_column(ArrayHelper::toArray($activityModelList), 'id');
            $pageModelList = PageModel::find()->where([
                'activity_id'   => $activityIds,
                'pipeline'      => $defaultCopyPipelineInfo['pipeline']
            ])->all();
            if (!empty($pageModelList)) {
                /** @var \app\modules\common\zf\models\PageModel $_srcPageModel */
                foreach ($pageModelList as $_srcPageModel) {
                    $_platformCode = SitePlatform::getPlatformCodeBySiteCode($_srcPageModel->site_code);
                    //当前端口下没有要新增的渠道
                    if (!isset($validPlatformParams[$_platformCode]))
                        continue;

                    foreach ($validPlatformParams[$_platformCode] as $_pipelineCode => $_targetLangList) {
                        //保存页面信息
                        $targetPageModel = new PageModel();
                        $targetPageModel->group_id = $_srcPageModel->group_id;
                        $targetPageModel->activity_id = $_srcPageModel->activity_id;
                        $targetPageModel->site_code = $_srcPageModel->site_code;
                        $targetPageModel->pipeline = $_pipelineCode;
                        $targetPageModel->auto_refresh = $_srcPageModel->auto_refresh;
                        $targetPageModel->refresh_time = $_srcPageModel->refresh_time;
                        $targetPageModel->end_time = $_srcPageModel->end_time;
                        $targetPageModel->default_lang = $_targetLangList[0];
                        if (!$targetPageModel->insert(true)) {
                            throw new Exception('添加子页面分组失败');
                        }
                        $updateMessage[] = sprintf(
                            '新增子页面成功: %s',
                            json_encode([
                                'platform' => $_platformCode,
                                'pipeline' => $targetPageModel->pipeline,
                                'id' => $targetPageModel->id
                            ])
                        );

                        //保存页面分组
                        /** @var \app\modules\common\zf\models\PageGroupModel $srcPageGroupModel */
                        $srcPageGroupModel = PageGroupModel::find()
                            ->where(['page_id' => $_srcPageModel->id, 'pipeline' => $_srcPageModel->pipeline])
                            ->one();

                        /** @var \app\modules\common\zf\models\PageGroupModel $targetPageGroupModel */
                        $targetPageGroupModel = new PageGroupModel();
                        $targetPageGroupModel->activity_group_id = $srcPageGroupModel->activity_group_id;
                        $targetPageGroupModel->page_group_id = $srcPageGroupModel->page_group_id;
                        $targetPageGroupModel->platform_type = SitePlatform::getPlatformTypeByPlatformCode($_platformCode);
                        $targetPageGroupModel->page_id = $targetPageModel->id;
                        $targetPageGroupModel->pipeline = $_pipelineCode;
                        if (!$targetPageGroupModel->insert(true)) {
                            throw new Exception('添加子页面分组失败');
                        }
                        $updateMessage[] = sprintf(
                            '新增子页面分组成功,分组信息: %s',
                            json_encode(ArrayHelper::toArray($targetPageGroupModel))
                        );

                        //保存页面语言
                        /** @var \app\modules\common\zf\models\PageLanguageModel $srcPageLanguageModel */
                        $srcPageLanguageModel = PageLanguageModel::findOne([
                            'page_id' => $_srcPageModel->id,
                            'lang'    => $defaultCopyPipelineInfo['lang']
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
                        foreach ($_targetLangList as $_targetLangCode) {
                            $targetPageLanguageData['page_id'] = $targetPageModel->id;
                            $targetPageLanguageData['lang'] = $_targetLangCode;
                            $targetPageLanguageData['tpl_id'] = 0;

                            if (!empty($targetPageLanguageData['redirect_url'])) {
                                $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($targetPageModel->site_code);
                                $wapSiteCode = SitePlatform::getWapPlatformSiteCode($siteGroupCode);
                                $pipelineList = PipelineUtils::getConfigSpecialSupportPipelineList($wapSiteCode);
                                if (isset($pipelineList[$_pipelineCode][$_targetLangCode])) {
                                    $_targetRedirectUrl = trim($pipelineList[$_pipelineCode][$_targetLangCode], '/') . '/';
                                    $_targetRedirectUrl .= $targetPageLanguageData['url_name'] . '.html';
                                    $targetPageLanguageData['redirect_url'] = $_targetRedirectUrl;
                                } else {
                                    $targetPageLanguageData['redirect_url'] = '';
                                }
                            }

                            $command = $srcPageLanguageModel->getDb()->createCommand();
                            $command->batchInsert($srcPageLanguageModel->tableName(), $columnNames, [array_values($targetPageLanguageData)])
                                    ->execute();
                            $updateMessage[] = sprintf(
                                '新增子页面语言成功：%s',
                                json_encode(['page_id' => $targetPageLanguageData['page_id'], 'lang' => $targetPageLanguageData['lang']])
                            );
                        }


                    }

                }
            }

            $transaction->commit();
            return app()->helper->arrayResult($this->codeSuccess, '修复成功', $updateMessage);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return app()->helper->arrayResult($this->codeFail, $e->getMessage());
        }
    }
}
