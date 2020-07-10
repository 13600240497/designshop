<?php

namespace app\modules\home\dl\controllers;

use app\modules\component\dl\components\ExplainComponent;

class CommunityController extends Controller
{
    
    public function actionLists(string $siteCode)
    {
        $response = (new ExplainComponent())->getCommunityList($siteCode);
        
        return app()->helper->arrayResult(0, 'success', $response);
    }
}