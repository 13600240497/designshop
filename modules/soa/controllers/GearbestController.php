<?php

namespace app\modules\activity\controllers;

use yii\base\Exception;

/**
 * Class GearbestController
 *
 * @package app\modules\soa\controllers
 * @property \app\modules\soa\components\GearbestComponent $GearbestComponent
 */
class GearbestController extends Controller
{
    
    /**
     * 普通商品组件
     *
     * @return array
     */
    public function actionGeneralGoods()
    {
        try {
            $skus = ['sku' => 'CA0001101_1433363,CA0001801_1363221', ''];
            $data = $this->GearbestComponent->getGeneralGoodsList($skus);
            
            return app()->helper->arrayResult(0, 'success', $data);
        } catch (Exception $exception) {
            return app()->helper->arrayResult(1, $exception->getMessage());
        }
    }
    
    /**
     * 多商品组件
     *
     * @return array
     */
    public function actionManyGoods()
    {
        try {
            $skus = ['126329701_1433363,259790401_1433363', 'CA0001101_1433363,CA0001801_1363221'];
            $data = $this->GearbestComponent->getManyGoodsList($skus);
            
            return app()->helper->arrayResult(0, 'success', $data);
        } catch (Exception $exception) {
            return app()->helper->arrayResult(1, $exception->getMessage());
        }
    }
    
    /**
     * 加价购商品组件
     *
     * @return array
     */
    public function actionMarkUpGoods()
    {
        try {
            $data = $this->GearbestComponent->getMarkUpGoodsList(['platform' => 3, 'lang' => 'en', 'activityId' => 6445220402737201152]);
            return app()->helper->arrayResult(0, 'success', $data);
        } catch (Exception $exception) {
            return app()->helper->arrayResult(1, $exception->getMessage());
        }
    }
    
    /**
     * 抢购商品组件
     *
     * @return array
     */
    public function actionRushBuyGoods()
    {
        try {
            $skus = ['platform' => 3, 'lang' => 'en', 'sku' => '260660209_1433363,260485901_1433363'];
            $data = $this->GearbestComponent->getRushBuyGoodsList($skus);
            
            return app()->helper->arrayResult(0, 'success', $data);
        } catch (Exception $exception) {
            return app()->helper->arrayResult(1, $exception->getMessage());
        }
        
    }
    
    public function actionCouponList()
    {
        //EHDJHTPD58408  G4VUIBEI85088
        return $this->GearbestComponent->getSystemCouponList([]);
    }
    
    public function actionPartsGoods()
    {
        $skus = [
            'platform' => 3,
            'lang'     => 'en',
            'sku'      => [
                ['main' => '258235302_1433363', 'parts' => '126579801_1433363,104236103_1433363,106827101_1433363'],
                //['main' => '261189102_1433363', 'parts' => '260585004_1433363,260585002_1433363']
            ]
        ];
        
        return $this->GearbestComponent->getPartsGoodsList($skus);
    }
}