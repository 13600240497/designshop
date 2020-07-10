<?php
namespace app\modules\base;

use app\modules\base\traits\MagicPropertyTrait;

/**
 * base 模块
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
                'class' => 'app\modules\base\zf\Module',
            ],
            'dl' => [
                'class' => 'app\modules\base\dl\Module',
            ]
        ];
    }
}
