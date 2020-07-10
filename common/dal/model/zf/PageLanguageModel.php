<?php
namespace app\common\dal\model\zf;

use app\common\helper\ModelHelper;

/**
 * PageLanguage模型
 *
 * @property int    $id
 * @property string $group_id
 * @property int    $page_id
 * @property string $lang
 * @property string $title
 * @property string $seo_title
 * @property string $keywords
 * @property string $description
 * @property int    $tpl_id
 * @property string $background_color
 * @property string $background_image
 * @property string $background_position
 * @property string $background_repeat
 * @property int    $style_type
 * @property string $multi_time_style
 * @property string $url_name
 * @property string $page_url
 * @property string $end_url
 * @property string $redirect_url
 * @property string $statistics_code
 * @property string $custom_css
 * @property string $local_files
 * @property string $s3_files
 *
 * @property PageModel $page
 * @property PageLayoutModel[] $layouts
 */
class PageLanguageModel extends AbstractZaFulModel
{
    /** @var int 页面样式 - 系统设置 */
    const STYLE_TYPE_SYSTEM = 1;
    /** @var int 页面样式 - 自定义 */
    const STYLE_TYPE_CUSTOM = 2;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'title';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'page_id', 'lang', 'title'], 'required'],
            ['style_type', 'default', 'value' => static::STYLE_TYPE_SYSTEM],
            [
                [
                    'page_url',
                    'end_url',
                    'url_name',
                    'redirect_url',
                    'seo_title',
                    'keywords',
                    'description',
                    'tpl_id',
                    'background_color',
                    'background_image',
                    'background_position',
                    'background_repeat',
                    'multi_time_style',
                    'statistics_code',
                    'custom_css',
                    'local_files',
                    's3_files',
                    'goods_sn',
                    'share_title',
                    'share_image',
                    'share_desc',
                    'share_link',
                    'share_place'
                ], 'default', 'value' => ''
            ],
            ['seo_title', 'string', 'length' => [1, 200]],
            ['page_id', 'validateKey'],
            ['title', 'string', 'length' => [1, 100]],
            ['keywords', 'string', 'max' => 200],
        ];
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        if (empty($this->page_id) || empty($this->lang)) {
            $this->addError('page_id', 'page_id&&lang不能为空');

            return false;
        }

        if (($item = static::findOne([
                'page_id' => $this->page_id,
                'lang'    => $this->lang
            ]))
            && (!$this->id || (int) $item->id !== (int) $this->id)
        ) {
            $this->addError('page_id', 'page_id&&lang主键冲突');

            return false;
        }

        return true;
    }

    /**
     * 获取子活动页面
     */
    public function getPage()
    {
        return $this->hasOne(PageModel::class, ['id' => 'page_id']);
    }

    /**
     * 获取布局组件
     */
    public function getLayouts()
    {
        return $this->hasMany(PageLayoutModel::class, ['page_id' => 'page_id', 'lang' => 'lang']);
    }
}
