<?php
namespace app\modules\common\gb\components;

use app\modules\common\gb\models\{
    PageLayoutModel,PageLayoutDataModel
};

use yii\db\Exception;

/**
 * 页面布局组件管理
 *
 */
class CommonLayout extends CommonCommonComponent
{
    //组件所在页面ID
    public $pageId;

    /**
     * 初始化组件类
     *
     */
    public function init()
    {
        parent::init();
        $this->componentType = static::LAYOUT_COMPONENT;
        $this->model = new PageLayoutModel();
    }

    /**
     * 复制布局组件
     * @param $data
     * @param bool $runValidation
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function copyLayout($data, $runValidation = true)
    {
        if (!$this->copyComponent($data, $runValidation)) {
            return false;
        }

        if (!$this->copyLayoutData($data['copy_id'], $this->instanceId)) {
            return false;
        }

        return true;
    }

    /**
     * 复制布局组件数据
     * @param int $fromId 源组件ID
     * @param int $toId 目标组件ID
     * @return bool
     * @throws Exception
     */
    private function copyLayoutData($fromId, $toId)
    {
        if ($info = PageLayoutDataModel::find()->where(['component_id' => $fromId])->asArray()->one()) {
            $pageLayoutData = new PageLayoutDataModel();
            $pageLayoutData->component_id = $toId;
            $pageLayoutData->lang = $info['lang'];
            $pageLayoutData->data = $info['data'];
            $pageLayoutData->custom_css = $info['custom_css'];
            $pageLayoutData->background_color = $info['background_color'];
            $pageLayoutData->background_img = $info['background_img'];
            if (!$pageLayoutData->save(false)) {
                throw new Exception($pageLayoutData->flattenErrors(', '));
            }
            $this->layoutData = json_decode($info['data'], true);
            $this->customData = ['custom_css' => $info['custom_css'],
                'background_color' => $info['background_color'],
                'background_img' => $info['background_img']
            ];
        }
        return true;
    }

}
