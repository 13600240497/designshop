<?php

namespace app\modules\common\gb\models;


class PageSpecialModel extends BaseModel
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
            [['page_group_id'], 'required']
        ];
    }
}