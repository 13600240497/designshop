<?php
namespace app\common\dal\model\zf;

use app\common\helper\ModelHelper;

/**
 * PageUiComponent模型
 *
 * @property int    $id
 * @property string $component_key
 * @property string $lang
 * @property int    $layout_id
 * @property int    $next_id
 * @property int    $position
 * @property int    $tpl_id
 * @property string $data
 * @property string $template
 * @property array  $logConfig
 */
class PageUiModel extends AbstractZaFulModel
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'layout_id';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zf_page_ui_component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['layout_id', 'component_key', 'lang',  'next_id', 'position'], 'required'],
            [['layout_id', 'next_id', 'position', 'tpl_id'], 'integer'],
            ['next_id', 'validateKey'],
        ];
    }

    /**
     * @inheritdoc
     * 将data、need_navigate字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'data',
            'share_data',
            'lang',
            'need_navigate',
            'tpl_id',
            'custom_css',
            'select_tpl_id'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        $condition = [
            'layout_id' => $this->layout_id,
            'next_id'   => $this->next_id,
            'position'  => $this->position,
            'lang'      => $this->lang
        ];
        if (($item = static::findOne($condition)) && (!$this->id || (int) $item->id !== (int) $this->id)) {
            $this->addError('next_id', 'layout_id&&next_id&&position主键冲突');
            return false;
        }
        return true;
    }
}
