<?php
namespace app\modules\test\components;

use app\base\SiteConstants;
use app\modules\common\models\PageModel;
use yii\helpers\ArrayHelper;

/**
 * 工具组件组件
 */
class RepairToolsComponent extends Component
{

    public function pageSiteCode()
    {
        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::findOne(3320);
        if ($pageModel) {
            $pageModel->site_code = 'rg-wap';
            $pageModel->update(false);
        }

        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::findOne(3321);
        if ($pageModel) {
            $pageModel->site_code = 'rg-app';
            $pageModel->update(false);
        }

        return 0;
    }


    public function repairUiTplData($params = [])
    {
        $this->repairSiteUiTplData(SiteConstants::SITE_GROUP_CODE_ZF);
        $this->repairSiteUiTplData(SiteConstants::SITE_GROUP_CODE_DL);
        $this->repairSiteUiTplData(SiteConstants::SITE_GROUP_CODE_RG);
    }

    private function repairSiteUiTplData($websiteCode)
    {
        if (SiteConstants::SITE_GROUP_CODE_ZF === $websiteCode) {
            $modelList = \app\modules\common\zf\models\PageUiTemplateModel::find()->all();
        } elseif (SiteConstants::SITE_GROUP_CODE_DL === $websiteCode) {
            $modelList = \app\modules\common\dl\models\PageUiTemplateModel::find()->all();
        } else {
            $modelList = \app\modules\common\models\PageUiTemplateModel::find()->all();
        }

        if (empty($modelList)) {
            return;
        }

        foreach ($modelList as $model) {
            if (!empty($model->ui)) {
                continue;
            }

            if (SiteConstants::SITE_GROUP_CODE_ZF === $websiteCode) {
                $uiModel = new \app\modules\common\zf\models\PageUiModel();
            } elseif (SiteConstants::SITE_GROUP_CODE_DL === $websiteCode) {
                $uiModel = new \app\modules\common\dl\models\PageUiModel();
            } else {
                $uiModel = new \app\modules\common\models\PageUiModel();
            }

            $uiModel->id = 0;
            $uiModel->component_key = $model->ui_key;
            $uiModel->lang = $model->lang;
            $uiModel->layout_id = 0;
            $uiModel->next_id = 0;
            $uiModel->position = 1;
            $uiModel->tpl_id = $model->tpl_id;

            $model->ui = json_encode(ArrayHelper::toArray($uiModel), true);
            $model->update(false);
        }
    }

}