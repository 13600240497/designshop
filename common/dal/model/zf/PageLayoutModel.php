<?php
namespace app\common\dal\model\zf;

use app\common\helper\ModelHelper;

/**
 * PageLayoutComponent模型
 *
 * @property int $id
 * @property int $page_id
 * @property int $lang
 * @property string $component_key
 * @property int $next_id
 * @property int $activity_id
 * @property array $logConfig
 */
class PageLayoutModel extends AbstractZaFulModel
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'page_id';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zf_page_layout_component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id', 'next_id'], 'integer'],
            [['page_id', 'component_key', 'lang', 'next_id'], 'required'],
            ['next_id', 'validateKey'],
        ];
    }

    /**
     * 将data字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'data',
            'custom_css',
            'background_color',
            'background_img',
            'style_data',
            'site_code',
            'activity_id',
            'pipeline'
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
        if (($item = static::findOne([
                'page_id' => $this->page_id,
                'next_id' => $this->next_id,
                'lang'    => $this->lang
            ]))
            && (!$this->id || (int)$item->id !== (int)$this->id)
        ) {
            $this->addError('next_id', 'page_id&&next_id主键冲突');
            return false;
        }

        return true;
    }

    /**
     * 获取布局组件数据
     */
    public function getData()
    {
        return $this->hasOne(PageLayoutDataModel::class, ['component_id' => 'id', 'lang' => 'lang']);
    }

    /**
     * 获取UI组件列表
     */
    public function getUiList()
    {
        return $this->hasMany(PageUiModel::class, ['layout_id' => 'id', 'lang' => 'lang']);
    }
}
