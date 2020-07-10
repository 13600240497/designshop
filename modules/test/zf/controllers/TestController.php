<?php

namespace app\modules\test\zf\controllers;

use app\base\SysConfigUtils;
use app\common\dal\model\zf\PageModel;
use yii\helpers\ArrayHelper;

class TestController extends Controller
{
    public function actionIndex()
    {
        set_time_limit(0);
        app()->response->isSent = true;
//        $pageModel2 = PageModel::getById(7084);
//        $pageIds = PageModel::find()->select('id')->where(['group_id' => $pageModel2->group_id])->column();
//        $pageModelList = PageModel::getPageByIds([7975, 7976], ['languages.layouts.data', 'languages.layouts.uiList']);
//        var_dump($pageModelList);

        /** @var PageModel $pageModel */
//        $pageModel = $pageModelList[0];
//        $pageModel2 = $pageModel->languages[0]->page;
//        echo $pageModel === $pageModel2 ? 'same' : 'not the same';
//        var_dump(ArrayHelper::toArray($pageModel));
//        var_dump(ArrayHelper::toArray($pageModel2));

//        print_r(ArrayHelper::toArray($pageModel->languages[0]->layouts[0]->uiList));
//        print_r(ArrayHelper::toArray($pageModelList[1]->languages[0]->layouts[0]->uiList));
        return '1212';
    }

    public function actionFallback()
    {
      app()->response->isSent = true;

//      $pageId = 11018;
//      $pageModel = \app\modules\common\zf\models\PageModel::findOne($pageId);
//      $pageAsyncApiFallback = new \app\components\fallback\api\PageAsyncApiFallback($pageModel, 'en');
//      var_dump($pageAsyncApiFallback->hasFallbackAsyncApi());

      $pageId = 3097;
      $pageModel = \app\modules\common\models\PageModel::findOne($pageId);

      //$pageAsyncApiManager = new \app\components\fallback\api\PageAsyncApiManager($pageModel, 'en');
      //$pageAsyncApiManager->checkFallbackAsyncApi();
      //print_r($pageAsyncApiManager::getTaskQueueInfo('rg'));

//      $pageAsyncApiFallback = new \app\components\fallback\api\PageAsyncApiFallback($pageModel, 'en');
//      $pageAsyncApiFallback->fallback();



//      \app\components\fallback\api\PageAsyncApiManager::getUiAsyncApiFallbackTask('zf');

      $monitor = new \app\components\monitor\site\SiteAsyncApiMonitor();
      $monitor->runTask();

//      $configs = SysConfigUtils::getAllOption();
//      print_r($configs);
//      $options = [
//        'sys.ui.direct_use_sync_api_fallback_data' => 1
//      ];
//      \app\base\SysConfigUtils::updateOptions($options);
//      $configs = \app\base\SysConfigUtils::getAllOption();
//      print_r($configs);



    }
}
