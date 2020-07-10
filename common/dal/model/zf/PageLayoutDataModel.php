<?php
namespace app\common\dal\model\zf;

/**
 * PageLayoutComponent模型
 *
 * @property int $id
 * @property int $component_id
 * @property string $lang
 * @property string $data
 * @property string $custom_css
 * @property string $background_color
 * @property string $background_img
 * @property string $style_data
 * @property array $logConfig
 */
class PageLayoutDataModel extends AbstractZaFulModel
{
    /**
     * 组件类型：1-Layout组件
     */
    const COMPONENT_TYPE = 1;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'component_id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['component_id', 'integer'],
            [['component_id', 'lang', 'data'], 'required'],
            [['custom_css', 'background_color', 'background_img', 'style_data'], 'default', 'value' => ''],
            ['component_id', 'validateKey'],
        ];
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        if (($item = static::findOne([
                'component_id' => $this->component_id
            ]))
            && (!$this->id || (int)$item->id !== (int)$this->id)
        ) {
            $this->addError('component_id', 'component_id主键冲突');
            return false;
        }

        return true;
    }


}
