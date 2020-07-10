<?php
/**
 * GB大数据埋点
 */

namespace app\modules\component\gb\components;

use app\modules\common\gb\traits\CommonPageParseTrait;
use app\base\SitePlatform;

class BigDataComponent extends Component
{
    use CommonPageParseTrait;
    
    protected $siteCode      = 10002;
    protected $bigCateType   = 'b';
    protected $smallCateType = 'b03';
    protected $pageCode      = '';
    protected $platform      = '';
    protected $baseStruct    = []; //大数据基础结构体 前端交互用
    protected $goodsStruct   = [];
    protected $pipeMark      = ''; //国家站标识
    
    /**
     * 唯一入bulit 生成大数据数据并返回结果集
     *
     * @param int    $pageId
     * @param string $pipeline
     * @param string $siteCode
     *
     * @return string 解析之后的数据集合
     */
    public function bulit(int $pageId, string $lang, string $pipeline, string $siteCode)
    {
        $this->setPageCode($pageId);
        $this->setPlatform($siteCode);
        $this->setPipeMark($pipeline);
        $this->baseStruct();
        $layouts = $this->getPageLayoutByPageId($pageId, $lang);
        $uiList = $this->getPageUIByLayoutIds(array_column($layouts, 'id'), $lang);
        if (!empty($uiList) && is_array($uiList)) {
            $this->skuStruct($uiList);
        }
        $dataArr = array_merge($this->baseStruct, $this->goodsStruct);
        
        return sprintf('window.TrackData = %s', json_encode($dataArr));
    }
    
    /**
     * 设置专题页面ID
     *
     * @param $pageId
     */
    private function setPageCode($pageId)
    {
        $this->pageCode = $pageId;
    }
    
    /**
     * 设置平台字段
     *
     * @param $siteCode
     *
     * @return string
     */
    private function setPlatform($siteCode)
    {
        $this->platform = SitePlatform::getPlatformCodeBySiteCode($siteCode);
    }
    
    /**
     * 大数据基础结构体 前端交互用
     *
     * @return array
     */
    private function baseStruct()
    {
        $this->baseStruct = [
            'common' => [
                'oi'       => '',                   // session id 前端获取
                'u'        => '',                   // 用户id 前端获取
                'd'        => $this->siteCode,      // 站点编码|站点标识
                'b'        => $this->bigCateType,   // 页面大类
                's'        => $this->smallCateType, // 页面小类
                'p'        => 'p-G' . $this->pageCode,      // 专题页面编码
                'plf'      => str_replace('wap', 'm', $this->platform),      // 前端字段 平台pc
                'dc'       => $this->pipeMark,      // 国家站标识
                'pageType' => 'list'                // 页面类型 前端需要
            ]
        ];
    }
    
    /**
     * SKU 统一数据结构体
     *
     * @param array $skus
     *
     * @return bool
     */
    private function skuStruct(array $uiList)
    {
        foreach ($uiList as $ui) {
            $data = \GuzzleHttp\json_decode($ui['data'], true);
            if (!empty($data['goodsSKU'])) {
                if (is_array($data['goodsSKU'])) {
                    $lists = array_column($data['goodsSKU'], 'lists');
                    foreach ($lists as $sku) {
                        $skus = explode(',', $sku);
                    }
                } else {
                    $skus = explode(',', $data['goodsSKU']);
                }
                foreach ($skus as $sku) {
                    if (stripos($sku, '_')) {
                        $this->goodsStruct[ $sku ] = ['k' => mb_substr($sku, stripos($sku, '_') + 1, strlen($sku))];
                    } else {
                        $this->goodsStruct[ $sku ] = ['k' => ''];
                    }
                }
            }
        }
    }
    
    /**
     * 设置国家站标识
     *
     * @param string $pipeline
     */
    private function setPipeMark(string $pipeline)
    {
        $pipeMap = [
            'GB'    => 1301,
            'GBRU'  => 1302,
            'GBES'  => 1303,
            'GBUK'  => 1304,
            'GBUS'  => 1305,
            'GBIT'  => 1306,
            'GBDE'  => 1307,
            'GBPT'  => 1308,
            'GBBR'  => 1309,
            'GBFR'  => 1310,
            'GBTR'  => 1311,
            'GBAU'  => 1312,
            'GBPL'  => 1313,
            'GBIN'  => 1314,
            'GBGR'  => 1315,
            'GBMX'  => 1316,
            'GBHU'  => 1317,
            'GBSK'  => 1318,
            'GBCZ'  => 1319,
            'GBNL'  => 1320,
            'GBRO'  => 1321,
            'GBMA'  => 1322,
            'GBJP'  => 1323,
            'GBSTY' => 1340,
            'GBGAG' => 1346,
            'GBCOZ' => 1347
        ];
        
        $this->pipeMark = $pipeMap[ $pipeline ];
    }
}