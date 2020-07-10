<?php
namespace app\modules\home;

use app\modules\home\traits\MagicPropertyTrait;

/**
 * index 模块
 */
class Module extends \app\base\Module
{
    use MagicPropertyTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->modules = [
            'zf' => [
                'class' => 'app\modules\home\zf\Module',
            ],
            'dl' => [
                'class' => 'app\modules\home\dl\Module',
            ]
        ];
    }
}
