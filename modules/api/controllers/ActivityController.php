<?php

namespace app\modules\api\controllers;

use app\modules\common\models\PageLanguageModel;
use app\modules\common\zf\models\PageLanguageModel as ZFPageLanguageModel;

/**
 * 活动相关对外API接口
 *
 * Class ActivityController
 *
 * @package app\modules\api\controllers
 */
class ActivityController extends Controller
{

    /**
     * 取上线页面的多语言访问地址列表
     *
     * @return array
     */
    public function actionPageMultiLanguage()
    {
        $params = app()->request->post();
        $page = [];
        $rules = [['content', 'required'], ['content', 'string']];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(-1, implode('|', array_column($model->errors, 0)));
        }

        if (json_decode(app()->request->post('content'))) {
            $params = json_decode(app()->request->post('content'), true);

            if (isset($params['siteCode']) && ('zf' == strtolower($params['siteCode']))) {
                $rules = [
                    [['pageId', 'siteCode', 'pipelineCode'], 'required'],
                    [['siteCode'], 'string']
                ];
            } else {
                $rules = [
                    [['pageId', 'siteCode'], 'required'],
                    ['siteCode', 'string']
                ];
            }

            $model = app()->validatorModel->new($rules)->load($params);
            if (false === $model->validate()) {
                return app()->helper->arrayResult(-1, implode('|', array_column($model->errors, 0)));
            }

            ges_set_site_group_code($params['siteCode']);
            switch(strtolower($params['siteCode'])){
                case 'zf':
                    $page = ZFPageLanguageModel::getPageUrlListForId($params ?? []);
                    break;
                case 'rg':
                    $page = ZFPageLanguageModel::getPageUrlForId($params ?? []);
                    break;
                default :
                    $page = PageLanguageModel::getPageUrlListForId($params ?? []);
                    break;
            }
        }
        return app()->helper->arrayResult(0, 'success', $page);

    }
}
