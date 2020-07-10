<?php
namespace app\modules\base\controllers;

use app\modules\base\traits\MagicPropertyTrait;
use yii\web\Response;

/**
 * base模块基础控制器
 * @property \app\modules\base\components\AdminComponent $AdminComponent
 * @property \app\modules\base\components\AdminLogComponent $AdminLogComponent
 * @property \app\modules\base\components\HomeLogComponent $HomeLogComponent
 * @property \app\modules\base\components\DepartmentComponent $DepartmentComponent
 * @property \app\modules\base\components\LoginComponent $LoginComponent
 * @property \app\modules\base\components\MenuComponent $MenuComponent
 * @property \app\modules\base\components\RoleComponent $RoleComponent
 */
class Controller extends \app\base\Controller
{
    use MagicPropertyTrait;

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        app()->response->format = Response::FORMAT_JSON;
    }

    /**
     * 重写render，在render之前输出按照html输出
     * @param $view
     * @param array $params
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function render($view, $params = [])
    {
        app()->response->format = Response::FORMAT_HTML;
        return parent::render($view, $params);
    }
}
