<?php

namespace app\modules\common\dl\components;

use ego\base\JsonResponseException;
use ego\curl\StandardResponseCurl;
use app\base\RequestUtils;
use app\modules\common\dl\models\PageModel;
use app\modules\common\dl\models\PageUiModel;
use app\modules\common\dl\models\ActivityModel;
use app\modules\common\dl\traits\CommonPageParseTrait;
use app\modules\component\dl\components\ExplainComponent;

class CommonGoodsComponent extends Component
{
    use CommonPageParseTrait;

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
            [['skus', 'lang'], 'required'],
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }
        
        if (isset($params['pageId'])) {
            return $this->getTplGoodsDataByPageId($params['pageId'], $params['skus'], $params['lang'], $params['uiId']);
        }
        
        return $this->getTplGoodsDataByUiId($params['uiId'], $params['skus'], $params['lang']);
    }

    /**
     * 根据指定API接口验证数据
     * @param array $params
     * - site_code
     * - lang
     * - api
     * - content
     *
     * @return string
     * @throws JsonResponseException
     */
    public function checkGoodsExitsWithAPI($params)
    {
        if (!isset($params['site_code'], $params['api'], $params['content'])) {
            throw new JsonResponseException($this->codeFail, '参数错误！');
        }

        $apiKey = $params['api'];
        $apiList = $this->getInterfaceConfig($params['site_code']);
        if (!isset($apiList[$apiKey])) {
            throw new JsonResponseException($this->codeFail, 'API接口不存在！');
        }

        if (!isset($params['skus']) || empty($params['skus'])) {
            $contentInfo = json_decode($params['content'], true);
            if (isset($contentInfo['goodsSn'])) {
                $params['skus'] = $contentInfo['goodsSn'];
            }
        }

        if (empty($params['skus'])) {
            throw new JsonResponseException($this->codeFail, '参数[skus]错误！');
        }

        $apiInfo = $apiList[$apiKey];
        $curl = new StandardResponseCurl();
        $apiUrl = $apiInfo['url'];
        $apiUrl .= (false === strpos($apiUrl, '?')) ? '?' : '&';
        $apiUrl .= 'content='. $params['content'];
        $resultInfo = $curl->slient()->asArray()->request('GET', $apiUrl);
        $responseSku = [];
        if (isset($resultInfo['data']['goodsInfo'])) {
            $responseSku = array_column($resultInfo['data']['goodsInfo'], 'goods_sn');
        }

        $diffSku = array_diff(explode(',', $params['skus']), $responseSku);
        if (!empty($diffSku)) {
            return implode(',', $diffSku);
        }
        return NULL;
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
            [['skus', 'lang'], 'required'],
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
        }
        
        if (!empty($params['skus'])) {
            if (isset($params['pageId'])) {
                return $this->checkGoodsExistsByPageId($params['pageId'], $params['skus'], $params['lang']);
            }

            return $this->checkGoodsExistsByUiId($params['uiId'], $params['skus'], $params['lang']);
        }
        
        return '';
    }
    
    /**
     * 根据页面ID获取商品数据
     *
     * @param int    $pageId 页面ID
     * @param string $skus   商品sku
     * @param string $lang   语言简码
     *
     * @return array
     */
    private function getTplGoodsDataByPageId($pageId, $skus, $lang, $uiId)
    {
        $siteCode = $this->getSiteCodeByPageId($pageId);
        $data = $this->getTplGoodsDataBySiteCode($siteCode, $lang, $skus, $uiId);
        
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
    private function getTplGoodsDataByUiId(int $uiId, string $skus, string $lang)
    {
        //获取站点siteCode
        $siteCode = PageUiModel::getSiteCode($uiId);
        $data = $this->getTplGoodsDataBySiteCode($siteCode, $lang, $skus, $uiId);
        
        return app()->helper->arrayResult(0, 'success', $data);
    }
    
    /**
     * 根据站点简码获取商品数据
     *
     * @param string $siteCode 站点简码
     * @param string $lang     语言简码
     * @param string $skus     商品sku
     *
     * @return array
     */
    private function getTplGoodsDataBySiteCode($siteCode, $lang, $skus, $uiId)
    {
        $explainComponent = new ExplainComponent();
        $list = $explainComponent->getGoodsList($skus, $lang, $siteCode, $uiId);
        if (!empty($list) && is_array($list)) {
            return $this->buildTplGoodsData($list);
        }
        
        return [];
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
        $module = RequestUtils::getModuleName();
        if (in_array(strtolower($module),['activity','advertisement','gbad'])) {
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
     * 组装模板组件商品数据
     *
     * @param $list
     * @param $siteCode
     *
     * @return mixed
     */
    private function buildTplGoodsData($list)
    {
        $showColumn = [
            'goods_id', 'goods_sn', 'goods_title', 'goods_img', 'is_on_sale', 'goods_number', 'promte_percent',
            'promote_start_date', 'promote_end_date', 'activity_number', 'activity_volume_number', 'left_time',
            'market_price', 'shop_price', 'category', 'score_rank', 'review_total', 'catid', 'warecode', 'stockNum',
            'stock_num'
        ];

        foreach ($list as &$value) {
            $value['promote_start_date'] = !empty($value['promote_start_date']) ? $value['promote_start_date'] : '';
            $value['promote_end_date'] = !empty($value['promote_end_date']) ? $value['promote_end_date'] : '';
            $value = array_filter($value, function ($k) use ($showColumn) {
                return \in_array($k, $showColumn, true);
            }, ARRAY_FILTER_USE_KEY);
        }
        
        return array_values($list);
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
    private function checkGoodsExistsByPageId($pageId, $skus, $lang)
    {
        //获取站点siteCode
        $siteCode = $this->getSiteCodeByPageId($pageId);
        
        return $this->checkGoodsExistsBySiteCode($siteCode, $lang, $skus);
    }
    
    /**
     * 校验传入的sku是否存在
     *
     * @param int    $uiId ui组件ID
     * @param string $skus
     * @param string $lang
     *
     * @return string
     */
    private function checkGoodsExistsByUiId(int $uiId, string $skus, string $lang)
    {
        //获取站点siteCode
        $siteCode = PageUiModel::getSiteCode($uiId);
        return $this->checkGoodsExistsBySiteCode($siteCode, $lang, $skus, $uiId);
    }
    
    private function checkGoodsExistsBySiteCode($siteCode, $lang, $skus, $uiId)
    {
        $explainComponent = new ExplainComponent();
        $responseSku = $explainComponent->getGoodsList($skus, $lang, $siteCode, $uiId);
        $responseSku = array_column($responseSku, 'goods_sn');
        
        $diffSku = array_diff(explode(',', $skus), $responseSku);
        if (!empty($diffSku)) {
            return implode(',', $diffSku);
        }
        
        return '';
    }
    
    
}
