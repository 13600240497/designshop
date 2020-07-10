<?php
namespace app\modules\common\components;

use app\modules\common\models\{
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
            /** @var \app\modules\common\models\PageLayoutDataModel $pageLayoutData */
            $pageLayoutData = new PageLayoutDataModel();
            $pageLayoutData->component_id = $toId;
            $pageLayoutData->lang = $info['lang'];
            $pageLayoutData->data = $info['data'];
            $pageLayoutData->custom_css = $info['custom_css'];
            $pageLayoutData->background_color = $info['background_color'];
            $pageLayoutData->background_img = $info['background_img'];
            $pageLayoutData->style_data = $info['style_data'];
            if (!$pageLayoutData->save(false)) {
                throw new Exception($pageLayoutData->flattenErrors(', '));
            }
            $this->layoutData = json_decode($info['data'], true);

            $customData = [
                'custom_css'        => $info['custom_css'],
                'background_color'  => $info['background_color'],
                'background_img'    => $info['background_img'],
            ];
            $this->customData = static::getCustomData($customData, $info['style_data']);
        }
        return true;
    }

    /**
     * 获取新的自定义数据
     * @param array $customData
     * @param string $styleData
     * @return array
     */
    public static function getCustomData($customData, $styleData)
    {
        if (!is_array($customData)) {
            $customData = [];
        }

        if (!empty($styleData)) {
            $_data = json_decode($styleData, true);
            if (!empty($_data)) {
                $customData = array_merge($customData, $_data);
            }
        }

        return $customData;
    }
}
