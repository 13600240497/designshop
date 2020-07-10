<?php
namespace app\modules\base\controllers;

use yii\web\Response;
use GuzzleHttp\Client;
use Yii;

/**
 * 系统工具
 *
 * @property \app\modules\base\components\ToolsComponent $ToolsComponent
 */
class ToolsController extends Controller
{
    /**
     * EDM链接生成器UI
     *
     * @return string
     */
    public function actionEdmDeepLink()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('deep-link');
    }

    /**
     * 清除CDN缓存页面
     *
     * @return string
     */
    public function actionLinkCacheClear()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('link-cache-clear');
    }
    /**
     * 清除CDN缓存接口
     *
     * @return array
     */
    public function actionLinkCacheClearApi($url)
    {
        $api = app()->params['clearCDNAPI'];
        if (empty($url)) {
            //未配置clearCDNAPI的则无需清理CDN缓存

            return app()->helper->arrayResult(1, '请输入正确的URL');
            
        }

        $api .= $url;
        $responseData = [];
        
        try {
            $response = (new Client())->get($api);
            $response && $responseData = json_decode($response->getBody() . '', true);
            
            Yii::info('CDN缓存清理返回原始值：' . $api . '----' . json_encode($responseData), __METHOD__);
            
            return app()->helper->arrayResult(0, '清理成功', $responseData);
        } catch (\Exception $e) {
            
            Yii::error('CDN缓存清理Exception：' . '---'.$e->getMessage(), __METHOD__);
        }

        if (!(isset($responseData['results']) && isset($responseData['results'][1])
            && $responseData['results'][1]['result'] && $responseData['results'][1]['result']['status']
            && $responseData['results'][1]['result']['status'] === 'success')
        ) {
            Yii::error('CDN缓存清理失败：' . $api . '---' . json_encode($responseData), __METHOD__);
        }
    }

    /**
     * EDM链接生成器UI数据
     *
     * @return array
     */
    public function actionEdmDeepLinkUiData()
    {
        return $this->ToolsComponent->getEdmDeepLinkUiData();
    }

    /**
     * 页面下线
     *
     * @return string
     * @throws
     */
    public function actionGenerateEdmDeepLinkUrl()
    {
        return $this->ToolsComponent->generateEdmUrl(app()->request->post());
    }


    /**
     * EDM链接生成器UI
     *
     * @return string
     */
    public function actionPageOffline()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('page-offline');
    }


    /**
     * 查询页面数量
     * @return array
     */
    public function actionGetPageNumber()
    {
        return $this->ToolsComponent->getOnlinePageNumber(app()->request->get());
    }
}
