<?php
namespace app\base;

use yii\web\HttpException;
use yii\filters\AccessControl;
use app\modules\base\components\MenuComponent;
use app\modules\base\components\AccessLogComponent;

/**
 * 基础控制器
 */
class Controller extends \ego\web\Controller
{
    /**
     * @var array get数据
     */
    public $getArr = [];
    /**
     * @var array post数据
     */
    public $postArr = [];

    //页面标题
    public $title = '';

    //页面标题后缀
    public $last = '-GESHOP';

    /**
     * @inheritdoc
     * @throws \ego\web\InvalidRequestException
     */
    public function init()
    {
        parent::init();
        $this->getArr = array_filter(
            app()->request->get(),
            function ($value) {
                return '0' === $value || !empty($value);
            }
        );

        $this->postArr = array_filter(
            app()->request->post(),
            function ($value) {
                return '0' === $value || !empty($value);
            }
        );
    }

    /**
     * 重写render，在render之前查询title，对于接口则无需查询，避免不必要的数据库操作
     * @param $view
     * @param array $params
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function render($view, $params = [])
    {
        $this->title = MenuComponent::getPageTitle();
        $this->title .= $this->last;

        return parent::render($view, $params);
    }

    /**
     * @inheritdoc
     * 添加日志记录
     *
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        // 初始化请求日志
        AccessLogComponent::initLog();
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        // 保存请求日志
        AccessLogComponent::requestLogging();
        return parent::afterAction($action, $result);
    }

    /**
     * @inheritdoc
     * @return array
     * @throws \yii\web\HttpException
     */
    public function behaviors()
    {
        if (app()->request->get('is_client')) {
            if (app()->user->getIsGuest()) {
                app()->Base->LoginComponent->autoLogin();
            }
            return [];
        }

        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    'actions' => [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return app()->user->can($this->id . '/' . $this->action->id);
                        }
                    ],
                ],
                'denyCallback' => function () {
                    if (app()->user->getIsGuest() && !app()->request->isAjax) {
                        return app()->getResponse()->redirect(app()->user->loginUrl);
                    }

                    if (!app()->request->isAjax) {
                        exit($this->renderFile(APP_PATH . '/views/error/purview.php', []));
                    } else {
                        $code = app()->user->getIsGuest() ? 403 : 402;
//                        throw new HttpException(200, '您没有执行此操作的权限。', $code);
                        $return = app()->helper->arrayResult(200, '您没有执行此操作的权限。', $code);
                        exit(json_encode($return));
                    }
                }
            ],
        ];

    }
}
