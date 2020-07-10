<?php
namespace app\modules\component\gb\components;

use app\modules\component\traits\ErrorMessageTrait;
use app\modules\component\models\LayoutModel;
use app\modules\component\models\UiModel;

/**
 * component模块基础组件
 */
class Component extends \yii\base\Component
{
    use ErrorMessageTrait;

    /**
     * @var LayoutModel
     */
    public $layoutModel;

    /**
     * @var UiModel
     */
    public $uiModel;

    //组件类型与组件路径命名关联数组
    public $type = [1 => ['path' => 'layout', 'key' => 'L','num' => 1], 2 => ['path' => 'ui', 'key' => 'U','num' => 2]];

    //组件文件夹根目录
    public $basedir = '../files/parts/';

    //当前组件类型
    public $currType ;

    //错误信息
    public $errors;

     /**
     * 构造函数
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->layoutModel = new LayoutModel();
        $this->uiModel = new UiModel();
    }
}
