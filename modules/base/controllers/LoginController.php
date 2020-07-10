<?php
namespace app\modules\base\controllers;

use app\modules\base\components\MenuComponent;

/**
 * 登录控制器
 */
class LoginController extends Controller
{
    /**
     * 登录
     * @param string $returnUrl
     */
    public function actionLogin($returnUrl = '')
    {
        app()->response->redirect($this->LoginComponent->getSsoLoginUrl($returnUrl));
    }

    /**
     * 登录验证
     * @param string $sid
     * @param string $url
     * @return array|null
     * @throws \ego\base\JsonResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionDoLogin($sid, $url)
    {
        $result = $this->LoginComponent->doLogin($sid, $url);
        if (0 === $result['code']) {
            app()->user->loginByAdmin($result['data']['admin']);
            $this->LoginComponent->clearRedisCache();
            $siteCode =  MenuComponent::getDefaultSiteCode($result['data']['admin']['is_super']);
            list($websiteCode, ) = explode('-', $siteCode, 2);
            app()->response->redirect($result['data']['return'] . '/?site_group_code='.$websiteCode);
            return null;
        }

        return $result;
    }

    /**
     * 退出登录
     */
    public function actionLogout()
    {
        app()->user->logout();
        app()->response->redirect(app()->url->sso(
            'login/index/loginout',
            'returnurl=' . base64_encode(app()->url->admin())
        ));
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }
}
