<?php
namespace app\modules\test\controllers;

use app\enums\Error;
use app\modules\api\components\ErrorComponent;

/**
 * 首页控制器
 */
class IndexController extends Controller
{
    public function actionValidateAddress()
    {
        if (app()->Api->AddressComponent->isValid($_GET)) {
            return app()->helper->arrayResult(0, 'success');
        }
        return app()->helper->arrayResult(
            Error::INVALID[0],
            Error::INVALID[1],
            [
                'errors' => app()->Api->AddressComponent->getErrors()
            ]
        );
    }

    public function actionPhpinfo()
    {
        phpinfo();
    }

    public function actionTest()
    {
        print_r( ['CacheControl' => 'max-age=' . app()->params['cdnCacheControl'],
            'Expires'=> gmdate('D, d M Y H:i:s T', strtotime(app()->params['cdnExpires']))
        ]);
        var_dump(app()->params['cdnExpires']);
        var_dump(app()->params['cdnCacheControl']);
        exit;
        //echo \app\base\Translate::gbUiComponentTrans('ru', 'limited_to_units', ['<span>1</span>']);
        //echo \app\base\Translate::getGbUiComponentJsTransMessage('ru');
		printFormat(\ego\base\Application::getDeveloper());
        $autoPageCrontab = new \app\components\auto\AutoPageCrontab('zf');
        $autoPageCrontab->refreshPageAsyncData();

        app()->response->isSent = true;
    }
}
