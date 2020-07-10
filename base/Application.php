<?php
namespace app\base;

use app\common\util\CssJsVersionManage;

/**
 * 基础Application，继承`\ego\base\Application`
 *
 * @property \app\base\User $user
 * @property \app\base\RedisKey $redisKey
 * @property \app\components\Url $url
 * @property \app\components\Rms $rms
 * @property \app\base\Cache $rcache
 */
class Application extends \ego\base\Application
{
    public function init()
    {
        parent::init();
        app()->params['css_version'] = CssJsVersionManage::info();
        app()->params['js_version'] = CssJsVersionManage::info();
    }
}
