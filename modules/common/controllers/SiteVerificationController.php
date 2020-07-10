<?php
namespace app\modules\common\controllers;

use app\modules\common\components\CommonSiteVerifyComponent;

/**
 * 站点数据验证
 */
class SiteVerificationController extends Controller
{

    /**
     * 数据验证
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionVerify()
    {
        return CommonSiteVerifyComponent::verify(app()->request->post());
    }
}


