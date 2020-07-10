<?php
namespace app\modules\soa\models;

use app\models\ActiveRecord;
use app\base\SitePlatform;

/**
 * SoaIpsGoodsModel 模型
 *
 * @property int    $id
 * @property string $ips_activity_id
 * @property string $website_code
 * @property int    $rule_type
 * @property string $page_id
 * @property int    $lang
 * @property int    $component_id
 * @property int    $component_key
 * @property int    $goods_sku
 * @property int    $last_update_time
 */
class SoaIpsGoodsModel extends ActiveRecord
{

    /** @var 规则类型 - 自动规则 */
    const RULE_TYPE_AUTO = 1;
    /** @var 规则类型 - 手动规则 */
    const RULE_TYPE_MANUAL = 2;
    /** @var 规则类型 - 规则添加 */
    const RULE_TYPE_IPS_RULE =3;
    /** @var 规则类型 - 删选器添加 */
    const RULE_TYPE_IPS_FILTER =4;
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ips_activity_id', 'page_id', 'lang', 'component_id', 'component_key'], 'required'],
            ['goods_sku', 'default', 'value' => '']
        ];
    }

    /**
     * 获取站点UI组件所有关联IPS活动
     * @param string $webSiteCode
     * @param int $componentId
     * @return array
     */
    public static function getUiComponentAllActivitySku($webSiteCode, $componentId)
    {
        $rows = static::find()->where(['website_code' => $webSiteCode, 'component_id' => $componentId])->indexBy('ips_activity_id')->all();
        return empty($rows) ? [] : $rows;
    }

    /**
     * UI组件复制时，复制IPS关联信息
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     *
     * @param int $fromComponentId
     * @param int $toComponentId
     */
    public static function copyUiComponentWithIpsInfo($pageInfo, $fromComponentId, $toComponentId)
    {
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($pageInfo['siteCode']);

        //查询当前组件已经关联的IPS子活动
        $soaIpsGoodsModelList = static::find()
            ->where([
                'website_code'      => $websiteCode,
                'page_id'           => $pageInfo['pageId'],
                'lang'              => $pageInfo['lang'],
                'component_id'      => $fromComponentId
            ])->asArray()->all();

        //复制关联数据
        if (!empty($soaIpsGoodsModelList)) {
            $columns = [];
            $copyData = [];
            foreach ($soaIpsGoodsModelList as $soaIpsGoodsModel) {
                unset($soaIpsGoodsModel['id']);
                $soaIpsGoodsModel['component_id'] = $toComponentId;
                $soaIpsGoodsModel['page_id'] = $pageInfo['toPageId'] ?? $soaIpsGoodsModel['page_id'];
                $soaIpsGoodsModel['lang'] = $pageInfo['toLang'] ?? $soaIpsGoodsModel['lang'];
                if (empty($columns)) {
                    $columns = array_keys($soaIpsGoodsModel);
                }
                $copyData[] = array_values($soaIpsGoodsModel);
            }

            static::insertAll($columns, $copyData);
        }
    }

    /**
     * 删除页面组件IPS关联信息
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     */
    public static function delPageUiComponentWithIpsInfo($pageInfo)
    {
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($pageInfo['siteCode']);

        static::deleteAll([
            'website_code'      => $websiteCode,
            'page_id'           => $pageInfo['pageId'],
            'lang'              => $pageInfo['lang'],
        ]);
    }

    /**
     * UI组件删除时，删除IPS关联信息
     *
     * @param array $pageInfo geshop活动页面信息
     * - siteCode string 站点简码
     * - pageId int 活动页面ID
     * - lang string 语言简码
     * - pipeline string 渠道简码
     * @param int $componentId UI组件ID
     */
    public static function delUiComponentWithIpsInfo($pageInfo, $componentId)
    {
        $websiteCode = SitePlatform::getSiteGroupCodeBySiteCode($pageInfo['siteCode']);

        static::deleteAll([
            'website_code'      => $websiteCode,
            'page_id'           => $pageInfo['pageId'],
            'lang'              => $pageInfo['lang'],
            'component_id'      => $componentId
        ]);
    }
}