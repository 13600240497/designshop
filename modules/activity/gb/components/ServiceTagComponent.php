<?php

namespace app\modules\activity\gb\components;

use app\base\SiteConstants;
use app\modules\common\gb\models\PageModel;
use app\modules\common\gb\models\PageServiceTagModel;
use yii\helpers\ArrayHelper;
use ego\base\JsonResponseException;

/**
 * 服务标组件
 */
class ServiceTagComponent extends Component
{
    /**
     * 服务标配置数据保存
     *
     * @param array $params POST参数
     * @return array
     * @throws JsonResponseException
     */
    public function saveConfig($params)
    {
        //校验传参
        $rules = [
            [['page_id', 'lang', 'tag_config'], 'required']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult($this->codeFail, implode('|', array_column($model->errors, 0)));
        }

        //公共数据检查
        if (!is_numeric($params['page_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的参数page_id');
        }

        $pageId = (int)$params['page_id'];
        /** @var \app\modules\common\gb\models\PageModel $pageModel */
        $pageModel = PageModel::findOne([
            'id'        => $pageId,
            'is_delete' => PageModel::NOT_DELETE
        ]);
        if (!$pageModel) {
            throw new JsonResponseException($this->codeFail, '子页面不存在');
        }

        $tagConfig = json_decode($params['tag_config'], true);
        if (empty($tagConfig) || !is_array($tagConfig)) {
            throw new JsonResponseException($this->codeFail, '服务标配置数据无效');
        }

        $exitsSkuList = $allSkuList = [];
        $allowConfigKeys = ['text', 'bg_color', 'text_color', 'pic_url', 'link_url'];
        foreach ($tagConfig as $config) {
            if (!is_array($config) || !isset($config['sku_list'], $config['data_config'])) {
                throw new JsonResponseException($this->codeFail, '服务标配置项数据无效');
            }

            $skuList = $config['sku_list'];
            if (is_string($skuList)) {
                $skuList = explode(SiteConstants::CHAR_COMMA, $skuList);
            }

            //检查是否有重复SKU
            $intersectSkuList = array_intersect($allSkuList, $skuList);
            if (!empty($intersectSkuList)) {
                $exitsSkuList = array_merge($exitsSkuList, $intersectSkuList);
            }

            $allSkuList = array_merge($allSkuList, $skuList);
            foreach ($config['data_config'] as $tagData) {
                foreach ($allowConfigKeys as $key) {
                    if (!array_key_exists($key, $tagData)) {
                        throw new JsonResponseException($this->codeFail, '服务标配置项数据无效');
                    }
                }
            }

        }

        if (!empty($exitsSkuList)) {
            $message = sprintf('商品 %s 已经添加了服务标，请勿重复添加!', join(SiteConstants::CHAR_COMMA, $exitsSkuList));
            throw new JsonResponseException($this->codeFail, $message);
        }

        $pageServiceTagModel = PageServiceTagModel::getPageServiceTag($pageId, $params['lang']);
        if (empty($pageServiceTagModel)) {
            $pageServiceTagModel = new PageServiceTagModel();
            $pageServiceTagModel->page_id = $pageId;
            $pageServiceTagModel->lang = $params['lang'];
            $pageServiceTagModel->pipeline = $pageModel->pipeline;
        }

        $pageServiceTagModel->tag_config = $params['tag_config'];
        if (!$pageServiceTagModel->save(true)) {
            throw new JsonResponseException($this->codeFail, '添加/修改服务标失败');
        }

        return app()->helper->arrayResult($this->codeSuccess, '添加/修改成功');
    }

    /**
     * 服务标配置数据获取
     *
     * @param array $params GET参数
     * @return array
     * @throws JsonResponseException
     */
    public function getConfig($params)
    {
        //校验传参
        $rules = [
            [['page_id', 'lang'], 'required']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult($this->codeFail, implode('|', array_column($model->errors, 0)));
        }

        //公共数据检查
        if (!is_numeric($params['page_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的参数page_id');
        }

        $pageId = (int)$params['page_id'];
        $pageModel = PageModel::findOne([
            'id'        => $pageId,
            'is_delete' => PageModel::NOT_DELETE
        ]);
        if (!$pageModel) {
            throw new JsonResponseException($this->codeFail, '子页面不存在');
        }

        $pageServiceTagModel = PageServiceTagModel::getPageServiceTag($pageId, $params['lang']);
        $result = [];
        if ($pageServiceTagModel) {
            $result = ArrayHelper::toArray($pageServiceTagModel);
        }

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $result);
    }

    /**
     * @param array $goodsInfoList
     * @param int $pageId
     * @param string $lang
     *
     * @return boolean
     */
    public function processGoodsServiceTag(&$goodsInfoList, $pageId, $lang)
    {
        if (empty($goodsInfoList)) {
            return false;
        }

        /** @var \app\modules\common\gb\models\PageServiceTagModel $pageServiceTagModel */
        $pageServiceTagModel = PageServiceTagModel::getPageServiceTag($pageId, $lang);
        if (!$pageServiceTagModel) {
            return false;
        }

        $serviceTagConfig = json_decode($pageServiceTagModel->tag_config, true);
        if (empty($serviceTagConfig)) {
            return false;
        }

        foreach ($serviceTagConfig as &$tagConfig) {
            if (is_string($tagConfig['sku_list'])) {
                $tagConfig['sku_list'] = explode(SiteConstants::CHAR_COMMA, $tagConfig['sku_list']);
            }
        }

        foreach ($goodsInfoList as &$goodsInfo) {
            if (!isset($goodsInfo['goods_sn'])) {
                continue;
            }

            $key = $goodsInfo['goods_sn'];
            foreach ($serviceTagConfig as $_tagConfig) {
                if (in_array($key, $_tagConfig['sku_list'])) {
                    $goodsInfo['service_tag'] = $_tagConfig['data_config'];
                    break;
                }
            }
        }

        return true;
    }
}