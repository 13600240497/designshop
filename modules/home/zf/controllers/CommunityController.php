<?php

namespace app\modules\home\zf\controllers;

use app\modules\component\zf\components\ExplainComponent;

class CommunityController extends Controller
{
    /**
     * actionLists
     * @param string $siteCode
     * @param string $pipeline
     * @return array
     */
    public function actionLists(string $siteCode, string $pipeline)
    {
        $response = (new ExplainComponent())->getCommunityList($siteCode, $pipeline);

        return app()->helper->arrayResult(0, 'success', $response);
    }
}
