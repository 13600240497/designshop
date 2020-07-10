<?php
namespace app\modules\test\controllers;

use app\modules\base\models\SysRequestLogModel;
use yii\web\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;

/**
 * 首页控制器
 */
class InfoController extends NoAuthController
{
    public function init()
    {
        parent::init();
        app()->response->format = Response::FORMAT_HTML;
    }

    public function actionUsers()
    {
        var_dump(app()->user->showUser());
    }

    public function actionPageLog()
    {
        $id = app()->request->get('id', null);
        if (empty($id)) return '请在URL上添加 id';
        $logModel = new SysRequestLogModel();
        $logs = $logModel->getRequestLogByid($id);
        $result = '';
        foreach ($logs as $item) {
            $result.= $item->getDetailMessage() . "\n";
        }
        return $result;
    }

    public function  actionCheck()
    {
        $startTime = date('H:I:s', time());
        \Yii::error('开始时间:' . $startTime . "\r\n",'promise');
        $api = 'api.hqgeshop.com/app/activity/page/asyncInfo';
        $params = ['site_code' => 'zf-app','pipeline' => 'ZF', 'lang'=> 'en','user_group'=>1,'api' => $api];
        $pageIds = [112610];
        $params['page_id'] = $pageIds[array_rand($pageIds,1)];
        $client = new Client();
        $apiInfo = ['form_params' => $params,'headers' => ['Accept' => 'application/vnd.GESHOP.v1+json']];
        $pool = new Pool($client, $this->buildRequest($client, $apiInfo),[
			 'concurrency' => 5,
			 'fulfilled' => function ($response, $index) {
				 $data = $response->getBody()->getContents();
				 $mes = '结束时间' . date('H:I:s', time()) . "\r\n" ;
				 \Yii::error($mes,'promise');
			 },
			 'rejected' => function ($reason, $index) {
				 $data = $reason->getMessage();
				 $mes = '结束时间' . date('H:I:s', time()) . "\r\n" ;
				 \Yii::error($mes,'promise');
			 },
		 ]);
		 $pool->promise()->wait();
        /*$response = $client->post($api, ['form_params' => $params,'headers' => ['Accept' => 'application/vnd.GESHOP.v1+json']]);
        var_dump($response->getBody()->getContents());die;*/
    }

    protected function buildRequest($client,$params)
    {
        yield function () use ($client, $params) {
            return $client->postAsync($params['form_params']['api'], $params);
        };
    }
}
