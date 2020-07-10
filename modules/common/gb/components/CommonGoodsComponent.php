<?php

namespace app\modules\common\gb\components;

use app\modules\common\gb\models\PageModel;
use app\modules\common\gb\models\PageUiModel;
use app\modules\common\gb\models\ActivityModel;
use app\modules\component\gb\components\ExplainDataComponent;
use ego\base\JsonResponseException;
use yii\base\Exception;

class CommonGoodsComponent extends Component
{
    
    /**
     * 模板组件商品管理列表
     *
     * @param array $params GET参数
     *
     * @return array
     * @throws JsonResponseException
     */
    public function tplGoodsData($params)
    {
        // 参数校验
        $rules = [
            [['skus', 'lang', 'pipeline'], 'required'],
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }
        
        if (isset($params['pageId'])) {
            return $this->getTplGoodsDataByPageId($params);
        }
        
        return $this->getTplGoodsDataByUiId($params);
    }
    
    /**
     * 校验传入的sku是否存在
     *
     * @param array $params GET参数
     *
     * @return string
     * @throws JsonResponseException
     */
    public function checkGoodsExists($params)
    {
        // 参数校验
        $rules = [
            [['skus', 'lang', 'pipeline'], 'required'],
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }
        
        if (isset($params['pageId'])) {
            return $this->checkGoodsExistsByPageId($params);
        }
        
        return $this->checkGoodsExistsByUiId($params);
    }
    
    /**
     * 根据页面ID获取商品数据
     *
     * @param array $params
     *
     * @return array
     */
    private function getTplGoodsDataByPageId(array $params)
    {
        $params['siteCode'] = $this->getSiteCodeByPageId($params['pageId']);
        $pageUi = PageUiModel::getPageUi($params['pageId']);
        $params['componentKey'] = $pageUi['component_key'];
        $data = $this->getTplGoodsDataBySiteCode($params);
        
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
    }
    
    /**
     * 根据ui组件ID获取商品数据
     *
     * @param int    $uiId ui组件ID
     * @param string $skus 商品sku
     * @param string $lang 语言简码
     *
     * @return array
     */
    private function getTplGoodsDataByUiId(array $params)
    {
        //获取站点siteCode
        $params['siteCode'] = PageUiModel::getSiteCode($params['uiId']);
        $pageUi = PageUiModel::getById($params['uiId']);
        $params['componentKey'] = $pageUi->component_key;
        $data = $this->getTplGoodsDataBySiteCode($params);
        
        return app()->helper->arrayResult(0, 'success', $data);
    }
    
    /**
     * 根据站点简码获取商品数据
     *
     * @param array $params
     *
     * @return array|mixed
     */
    private function getTplGoodsDataBySiteCode(array $params)
    {
        $explainComponent = new ExplainDataComponent(
            //$params['componentKey']
            $params['siteCode'], $params['lang'], '', $params['pipeline']
        );
        
        try {
            return $explainComponent->getGoodsData($params['skus']);
        } catch (Exception $exception) {
            throw new JsonResponseException($exception->getCode(), $exception->getMessage());
        }
    }
    
    /**
     * 根据页面id获取站点简码
     *
     * @param int $pageId
     *
     * @return string
     */
    private function getSiteCodeByPageId($pageId)
    {
        $module = app()->controller->module->module->id;
        if (in_array(strtolower($module), ['activity', 'advertisement'])) {
            return $this->activitySiteCodeByPageId($pageId);
        }
        if ('home' === strtolower($module)) {
            return $this->homeSiteCodeByPageId($pageId);
        }
    }
    
    /**
     * 活动页面获取站点
     *
     * @param $pageId
     *
     * @return mixed|string
     */
    private function activitySiteCodeByPageId($pageId)
    {
        $page = PageModel::find()->alias('p')
            ->select('a.site_code')
            ->leftJoin(ActivityModel::tableName() . ' as a', 'a.id = p.activity_id')
            ->where(['p.id' => $pageId])
            ->asArray()
            ->one();
        
        return $page ? $page['site_code'] : '';
    }
    
    /**
     * 首页获取站点
     *
     * @param $pageId
     *
     * @return mixed|string
     */
    private function homeSiteCodeByPageId($pageId)
    {
        $page = PageModel::find()->select('site_code')->where(['id' => $pageId])->asArray()->one();
        
        return $page ? $page['site_code'] : '';
    }
    
    /**
     * 校验传入的sku是否存在
     *
     * @param int    $pageId 页面ID
     * @param string $skus
     * @param string $lang
     *
     * @return string
     */
    private function checkGoodsExistsByPageId(array $params)
    {
        //获取站点siteCode
        $params['siteCode'] = $this->getSiteCodeByPageId($params['pageId']);
        
        return $this->checkGoodsExistsBySiteCode($params);
    }
    
    /**
     * 校验传入的sku是否存在
     *
     * @param array $params
     *
     * @return string
     */
    private function checkGoodsExistsByUiId(array $params)
    {
        //获取站点siteCode
        $params['siteCode'] = PageUiModel::getSiteCode($params['uiId']);
        
        return $this->checkGoodsExistsBySiteCode($params);
    }
    
    private function checkGoodsExistsBySiteCode(array $params)
    {
        $explainComponent = new ExplainDataComponent($params['siteCode'], $params['lang'], '', $params['pipeline']);
        
        try {
            $explainComponent->getGoodsData($params['skus']);
            
            return true;
        } catch (Exception $exception) {
            throw new JsonResponseException($exception->getCode(), $exception->getMessage());
        }
    }
}
