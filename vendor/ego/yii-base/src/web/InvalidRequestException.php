<?php
namespace ego\web;

use yii\web\ForbiddenHttpException;

/**
 * 非法访问异常，继承`yii\web\NotFoundHttpException`
 */
class InvalidRequestException extends ForbiddenHttpException
{
}
