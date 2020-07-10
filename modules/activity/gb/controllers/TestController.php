<?php

namespace app\modules\activity\gb\controllers;

/**
 * @property \app\modules\activity\gb\components\GbActivityComponent $GbActivityComponent
 * @property \app\modules\activity\gb\components\PageComponent       $PageComponent
 */
class TestController extends Controller
{
    public function actionIndex()
    {
        return $this->GbActivityComponent->test();
    }
    
    public function actionSwoole()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        $fp = $client->connect("127.0.0.1", 9501, 1);
        if( !$fp ) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
        $client->send(\GuzzleHttp\json_encode(['test' => 'Hello']));
    }
}