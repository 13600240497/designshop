<?php
namespace app\modules\base\controllers;

use ego\enums\CommonError;
use yii\base\UserException;

/**
 * ErrorController
 */
class ErrorController extends Controller
{
    /**
     * index
     *
     * @return array
     */
    public function actionIndex()
    {
        return app()->helper->arrayResult(
            CommonError::ERR_SYSTEM_BUSY,
            $this->getMessage(app()->errorHandler->exception)
        );
    }

    /**
     * 获取错误信息
     *
     * @param \Exception $exception
     * @return string
     */
    private function getMessage($exception)
    {
        if (app()->env->isLocal() || $exception instanceof UserException) {
            return $exception->getMessage();
        }
        return 'An internal server error occurred';
    }
}
