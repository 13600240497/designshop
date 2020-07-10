<?php
namespace app\modules\advertisement;

use app\modules\advertisement\traits\MagicPropertyTrait;

/**
 * advertisement 模块
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
                'class' => 'app\modules\advertisement\zf\Module',
            ]
        ];
    }
}
