<?php

namespace app\modules\activity\gb\components;

use app\modules\common\gb\components\CommonCrontabComponent;
use app\modules\common\gb\models\ActivityGroupModel;
use app\modules\common\gb\models\ActivityModel;
use app\modules\common\gb\models\PageModel;
use app\modules\common\gb\models\PageLanguageModel;
use app\modules\common\gb\models\PageLayoutModel;
use app\modules\common\gb\models\PageLayoutDataModel;
use app\modules\common\gb\models\PagePublishCacheModel;
use app\modules\common\gb\models\PagePublishLogModel;
use app\modules\common\gb\models\PageServiceTagModel;
use app\modules\common\gb\models\PageUiModel;
use app\modules\common\gb\models\PageUiDataModel;
use yii\db\Exception;

/**
 * 定时任务组件
 */
class CrontabComponent extends CommonCrontabComponent
{
    
    public function refreshErrorLang($activitys, $pipeline, $oldLang, $lang)
    {
        //开启事务
        $transaction = app()->db->beginTransaction();
        try {
            $activityArr = explode(',', $activitys);
            $activityResult = ActivityModel::find()
                ->select('id, group_id')
                ->where(['in', 'id', $activityArr])
                ->asArray()->all();
            if (!empty($activityResult) && is_array($activityResult)) {
                $activityGroupIds = array_unique(array_column($activityResult, 'group_id'));
                $groupResult = ActivityGroupModel::find()
                    ->select('id, lang_list')
                    ->where(['in', 'id', $activityGroupIds])
                    ->asArray()->all();
                if (!empty($groupResult) && is_array($groupResult)) {
                    foreach ($groupResult as $value) {
                        $langList = !empty($value['lang_list']) ? json_decode($value['lang_list'], true) : '';
                        if (!empty($langList) && is_array($langList)) {
                            foreach ($langList as $pipe => &$langValue) {
                                foreach ($langValue as &$pipeLang) {
                                    if ($pipe == $pipeline) {
                                        $pipeLang = $lang;
                                    }
                                }
                            }
                        }
                        ActivityGroupModel::updateAll(['lang_list' => json_encode($langList)], ['id' => $value['id']]);
                    }
                }
                $activityIds = array_unique(array_column($activityResult, 'id'));
                $pageResult = PageModel::find()
                    ->select('id')
                    ->where(['in', 'activity_id', $activityIds])
                    ->andWhere(['pipeline' => $pipeline])
                    ->asArray()->all();
                if (!empty($pageResult) && is_array($pageResult)) {
                    $pageIds = array_unique(array_column($pageResult, 'id'));
                    foreach ($pageIds as $pageId) {
                        PageLanguageModel::updateAll(['lang' => $lang], ['page_id' => $pageId, 'lang' => $oldLang, 'pipeline' => $pipeline]);
                        PageLayoutModel::updateAll(['lang' => $lang], ['page_id' => $pageId, 'lang' => $oldLang]);
                        PagePublishCacheModel::updateAll(['lang' => $lang], ['page_id' => $pageId, 'lang' => $oldLang]);
                        PagePublishLogModel::updateAll(['lang' => $lang], ['page_id' => $pageId, 'lang' => $oldLang]);
                        PageServiceTagModel::updateAll(['lang' => $lang], ['page_id' => $pageId, 'lang' => $oldLang, 'pipeline' => $pipeline]);
                    }
                    $layoutResult = PageLayoutModel::find()
                        ->select('id')
                        ->where(['in', 'page_id', $pageIds])
                        ->asArray()->all();
                    if (!empty($layoutResult) && is_array($layoutResult)) {
                        $layoutIds = array_unique(array_column($layoutResult, 'id'));
                        foreach ($layoutIds as $layoutId) {
                            PageLayoutDataModel::updateAll(['lang' => $lang], ['component_id' => $layoutId, 'lang' => $oldLang]);
                            PageUiModel::updateAll(['lang' => $lang], ['layout_id' => $layoutId, 'lang' => $oldLang]);
                        }
                        $uiResult = PageUiModel::find()
                            ->select('id')
                            ->where(['in', 'layout_id', $layoutIds])
                            ->asArray()->all();
                        if (!empty($uiResult) && is_array($uiResult)) {
                            $uiIds = array_unique(array_column($uiResult, 'id'));
                            foreach ($uiIds as $componentId) {
                                PageUiDataModel::updateAll(['lang' => $lang], ['component_id' => $componentId, 'lang' => $oldLang]);
                            }
                        }
                    }
                }
                $transaction->commit();
            }
            
            return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, '刷新成功');
        } catch (Exception $exception) {
            $transaction->rollBack();
            return app()->helper->arrayResult($this->codeFail, $this->msgFail, $exception->getMessage());
        }
    }
}
