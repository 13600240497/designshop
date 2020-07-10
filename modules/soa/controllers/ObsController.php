<?php

namespace app\modules\soa\controllers;

/**
 * OBS系统对接API接口
 *
 * @property \app\modules\soa\components\ObsComponent $ObsComponent
 *
 * @author chenliangliang
 * @since 1.6.2
 */
class ObsController extends Controller
{

    /**
     * OBS - 获取主题
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionThemeList()
    {
        return $this->ObsComponent->getThemeList();
    }

    /**
     * OBS - 获取主题下页面
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionPageList()
    {
        return $this->ObsComponent->getPageList(app()->request->get());
    }

    /**
     * OBS - 获取页面下版块
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionSectionList()
    {
        return $this->ObsComponent->getSectionList(app()->request->get());
    }

    /**
     * OBS - 获取版块下的产品
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionProductList()
    {
        $result = $this->ObsComponent->getProductList(app()->request->get());
        $goods = [];
        if(!empty($result['data']['data'])){
            foreach ($result['data']['data'] as $value) {
                $goods[] = $value['good_sn'].'_'.$value['warehouse_code'];
            }  
        }
        $result['data'] = join(',',$goods);
        return $result;
    }
}