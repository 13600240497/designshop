<?php

namespace app\modules\common\gb\models;


/**
 * gb_page_service_tag 模型
 *
 * @property int $id
 * @property int $page_id
 * @property string $lang
 * @property string $pipeline
 * @property string $tag_config
 *
 */
class PageServiceTagModel extends BaseModel
{

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id', 'lang', 'pipeline', 'tag_config'], 'required'],
            ['page_id', 'integer'],
        ];
    }

    /**
     * 获取页面服务标配置
     *
     * @param int $pageId
     * @param string $lang
     *
     * @return  PageServiceTagModel
     */
    public static function getPageServiceTag($pageId, $lang)
    {
        return static::find()->where(['page_id' => $pageId, 'lang' => $lang])->one();
    }
}