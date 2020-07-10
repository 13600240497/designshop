<?php

namespace app\modules\api\controllers;

use app\modules\common\models\PageLanguageModel;
use app\modules\soa\models\SoaObsGoodsModel;

/**
 * OBS相关对外API接口
 *
 * Class ActivityController
 *
 * @package app\modules\api\controllers
 */
class ObsController extends Controller
{
    
    const OBS_PAGE_URL_TOKEN = 'Ti371Gu0jMUwNvyQV9ztgo2OaDd5eCJF';
    /**
     * 获取板块下所有页面的访问地址
     *
     * @return array
     */
    public function actionThemePage()
    {
        
        $params = app()->request->post();
        $rules = [['content', 'required'], ['content', 'string']];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(-1, implode('|', array_column($model->errors, 0)));
        }
        if (json_decode(app()->request->post('content'))) {
            $params = json_decode(app()->request->post('content'), true);
            $rules = [
                [['theme_id'], 'required'],
                ['theme_id', 'number']
            ];
            $model = app()->validatorModel->new($rules)->load($params);
            if (false === $model->validate()) {
                return app()->helper->arrayResult(-1, implode('|', array_column($model->errors, 0)));
            }
        }
        if(empty($params['theme_id'])){
            return app()->helper->arrayResult(1, '主题ID未传');
        }
        $activitys = SoaObsGoodsModel::getActivityByThemeId($params['theme_id']);
        $page = PageLanguageModel::getThemePageUrlList(array_column($activitys, 'activity_id'));
        $page = SoaObsGoodsModel::formatUrlData($page);
        return app()->helper->arrayResult(0, 'success', $page);
    }
}