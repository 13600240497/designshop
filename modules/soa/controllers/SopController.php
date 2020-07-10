<?php
namespace app\modules\soa\controllers;

/**
 * 商品运营平台相关接口
 *
 * @property \app\modules\soa\components\SopComponent $SopComponent
 *
 * @author TianHaisen
 * @since 2.1.7
 */
class SopController extends Controller
{
    /**
     * 获取选品规律列表
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionRuleList()
    {
        return $this->SopComponent->getRuleList(app()->request->get());
    }
}