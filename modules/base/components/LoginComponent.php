<?php

namespace app\modules\base\components;

use app\modules\base\models\AdminModel;
use ego\base\JsonResponseException;
use ego\curl\BaseResponseCurl;

/**
 * 登录组件
 */
class LoginComponent extends Component
{
    /**
     * 获取sso登录地址
     *
     * @param string $returnUrl
     *
     * @return string
     */
    public function getSsoLoginUrl($returnUrl)
    {
        $returnUrl = $returnUrl ?: app()->url->admin();
        $returnUrl = app()->crypt->encode($returnUrl);

        return app()->url->sso(
            'login/index/sso',
            'struli=' . base64_encode(app()->url->admin('base/login/do-login') . '|' . $returnUrl)
        );
    }

    /**
     * 登录
     * @param string           $sid
     * @param string           $url
     * @param BaseResponseCurl $curl
     * @return array
     * @throws JsonResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function doLogin($sid, $url, $curl = null)
    {
        $returnUrl = base64_decode($url);
        if (!$returnUrl = app()->crypt->decode($returnUrl)) {
            throw new JsonResponseException(1, '回调URL错误');
        }
        $curl = $curl ?: new BaseResponseCurl();
        $response = $curl->slient()->request(
            'get',
            app()->url->sso('login/index/checksso/?sid=' . $sid)
        );

        if (!$response) {
            throw new JsonResponseException(1, '网络异常,请稍后重试');
        }

        if ('fbd' === ($body = $response->getBody() . '')) {
            throw new JsonResponseException(1, '登录失败,请稍后重试');
        }

        $result = json_decode(base64_decode($body));

        if (!isset($result->username)) {
            throw new JsonResponseException(1, '登录异常,请稍后重试');
        }

        $admin = AdminModel::getByUserName($result->username);
        \yii::info(var_export($admin, true), 'uc-data');
        if (null === $admin) {
            if (0 !== (int) $result->is_lock) {
                throw new JsonResponseException(1, '登录异常：账号已锁定');
            }
            $admin = $this->firstLogin($result);
        } else {
            $admin = $this->normalLogin($admin, $result);
        }

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'admin'  => $admin,
                'return' => false !== strpos($returnUrl, app()->request->hostInfo) ? $returnUrl : app()->homeUrl
            ]
        );
    }

    /**
     * 自动登录
     * @throws JsonResponseException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function autoLogin()
    {
        $admin = AdminModel::getByUserName('laihuan');
        $result = null;
        if ($admin) {
            $result = $this->normalLogin($admin, (object)array('is_lock' => '0'));
        };
        $result && app()->user->loginByAdmin($result);
        $this->LoginComponent->clearRedisCache();
    }

    /**
     * 清除redis缓存(需要在用户登录后清理)
     * @throws JsonResponseException
     * @throws \Throwable
     */
    public function clearRedisCache()
    {
        //每次登录成功后，删除用户缓存以便后面可以先从数据库获取最新数据，然后再次缓存
        if (app()->user->id) {
            app()->user->clearUserRedisCache(
                app()->user->id,
                [MenuComponent::getDefaultSiteCode(app()->user->get('is_super'))]
            );
        }
    }

    /**
     * 首次登录
     * @param $result
     * @return array
     * @throws \Exception
     * @throws JsonResponseException
     * @throws \Throwable
     */
    private function firstLogin($result)
    {
        $admin = new AdminModel();
        $admin->department_id = $result->department_id;
        $admin->user_no = $result->user_no;
        $admin->username = $result->username;
        $admin->realname = $result->name ?: $result->username;
        $admin->is_leader = $result->is_group_leader;   //数据库表设计存疑!
        $admin->last_login_ip = app()->ip->get(true);
        $admin->last_login_time = time();
        $admin->status = 1;
        if (\in_array($result->username, app()->params['superAdminAccounts'], true)) {
            $admin->is_super = 1;
        }
        if (!$admin->insert()) {
            \Yii::warning($admin->flattenErrors());
            throw new JsonResponseException(4, $admin->flattenErrors());
        }

        return $admin;
    }

    /**
     * 普通登录
     * @param \app\modules\base\models\AdminModel $admin
     * @param object $result
     * @return array
     * @throws \Exception
     * @throws JsonResponseException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function normalLogin($admin, $result)
    {
        if ($admin->status === 0) {
            throw new JsonResponseException(1, '登录失败：账号已关闭');
        }

        if (0 !== (int)$result->is_lock) {
            $admin->status = AdminModel::STATUS_UCLOCKED;
            $admin->update();
            throw new JsonResponseException(1, '登录异常：账号已锁定');
        }
        // 正常登录
        $admin->logins++;
        $admin->last_login_ip = app()->ip->get(true);
        $admin->last_login_time = time();
        //$admin->department_id = $result->department_id;
        $admin->status = 1;
        $admin->update();

        return $admin;
    }
}
