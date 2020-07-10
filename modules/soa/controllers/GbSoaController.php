<?php

namespace app\modules\soa\controllers;

use app\base\SitePlatform;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\RequestOptions;
use app\modules\soa\components\GearbestGoodsInfoComponent;
use app\modules\soa\servers\GearbestSoaServer as GbSoaServer;
use app\modules\soa\models\TestReportModel;

/**
 * 选品系统对接API接口
 *
 * @since 1.5.0
 *
 * @property \app\modules\soa\components\AiComponent $AiComponent
 * @property \app\modules\soa\components\IpsComponent $IpsComponent
 *
 */
class GbSoaController extends Controller
{

    public function actionTest()
    {
        app()->response->isSent = true;
        //$this->testAi();
        //$this->testIps();
        //$data = [];
        //var_dump(isset($data['ips']['ipsGoodsSKU']));

        $this->loadData();
    }

    public function loadData()
    {

        $db = app()->get('online');
        $sql = "SELECT a.id, a.component_key, a.`name`,a.description,a.range, a.create_time, c.place,  group_concat(csr.site_code) as site_groups"
            ." FROM ui_component as a LEFT JOIN component_category as c ON a.category_id = c.id LEFT JOIN component_site_relation as csr ON a.id = csr.component_id AND csr.type = c.type"
            ." WHERE a.status=3 and a.is_delete=0 GROUP BY a.id";
        $rows = $db->createCommand($sql)->queryAll();
        foreach ($rows as &$row) {
            $row['range'] = ((int)$row['range'] === 1) ? '桌面端' : '移动端';
            $row['place'] = ((int)$row['place'] === 1) ? '活动页' : '首页';
            $row['create_time'] = date('Y-m-d', $row['create_time']);
            $row['site_groups'] = $this->getSiteGroupsBySiteCodes($row['site_groups']);

            $key =  $row['component_key'];
            $sql ="SELECT count(*) as num FROM page_ui_component as pu left JOIN page_layout_component as pl on pl.id=pu.layout_id left join page as p on p.id = pl.page_id WHERE pu.component_key='{$key}' and p.is_delete=0";
            $result = $db->createCommand($sql)->queryOne();
            $useCount = 0;
            if (!empty($result)) {
                $useCount = $result['num'];
            }
            $row['use_count'] = $useCount;

            unset($row['id'],$row['component_key']);print_r($row);
        }

        $columnNames = array_keys($rows[0]);
        TestReportModel::getDb()->createCommand()->batchInsert(
            TestReportModel::tableName(),
            $columnNames,
            $rows
        )->execute();
    }

    public function getSiteGroupsBySiteCodes(string $siteCodes)
    {
        $siteCodeArr = explode(',', $siteCodes);
        if (empty($siteCodes) || empty($siteCodeArr)) {
            return $siteCodes;
        }

        $list = [];
        foreach ($siteCodeArr as $siteCode) {
            $groupCode = explode(SitePlatform::SITE_CODE_SEPARATOR, $siteCode)[0];
            if ('test' == $groupCode) continue;
            $list[] = strtoupper($groupCode);
        }
        return implode(',', array_unique($list));
    }

    public function testIps()
    {

        try {
            //$result = $this->IpsComponent->getSingleActivityGoodsSkuFromIps('398');
            //print_r($result);

            $result2 = $this->IpsComponent->getMultiActivityGoodsSkuFromIps('zf-pc', [398]);
            print_r($result2);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function testAi()
    {
        $goodsCodeList1 = [
            ['spu'=> '11', 'sku' => '285731301'],
            ['spu'=> '22', 'sku' => '285535802'],
            ['spu'=> '11', 'sku' => '284764301'],
            ['spu'=> '33', 'sku' => '274478601'],
            ['spu'=> '22', 'sku' => '277887504'],
            ['spu'=> '44', 'sku' => '276088402'],
        ];

        $goodsCodeList2 = [
            ['spu'=> '33', 'sku' => '283412003'],
            ['spu'=> '55', 'sku' => '276088402'],
            ['spu'=> '55', 'sku' => '281757801'],
            ['spu'=> '55', 'sku' => '281757801'],
        ];

//        $goodsCodeList = array_merge($goodsCodeList1, $goodsCodeList2);
//        $result = $this->AiComponent->filterSameSpuBestSaleSku('zf-pc', $goodsCodeList);
//        print_r($result);

        $aiFilterGoodsList = [
            '11' => '285731301',
            '22' => '285535802',
            '33' => '274478601',
            '44' => '276088402',
            '55' => '281757801',
        ];
        print_r($aiFilterGoodsList);
        echo $this->getSameSpuBestSaleSku($goodsCodeList1, $aiFilterGoodsList);
        echo $this->getSameSpuBestSaleSku($goodsCodeList2, $aiFilterGoodsList);
    }

    private function getSameSpuBestSaleSku($ipsGoodsList, $aiFilterGoodsList)
    {
        //按SPU分组
        $goodsSpuMapping = [];
        foreach ($ipsGoodsList as $goodsCode) {
            $spu = $goodsCode['spu'];
            if (!isset($goodsSpuMapping[$spu])) {
                $goodsSpuMapping[$spu] = [];
            }

            if (!in_array($goodsCode['sku'], $goodsSpuMapping[$spu]))
                $goodsSpuMapping[$spu][] = $goodsCode['sku'];
        }

        //同款获取销量最好的SKU
        foreach ($goodsSpuMapping as $spu => $spuGoodsSkuList) {
            if (count($spuGoodsSkuList) == 1) {
                $filterSkuList[$spu] = $spuGoodsSkuList[0];
            } else {
                //人工智能有销售数据的用人工智能的结果，没有直接使用数组第一个
                $filterSkuList[$spu] = isset($aiFilterGoodsList[$spu]) ? $aiFilterGoodsList[$spu] : $spuGoodsSkuList[0];
            }
        }

        echo '---------------------';
        print_r($goodsSpuMapping);
        print_r($filterSkuList);

        return join(',', $filterSkuList);
    }

    public function testSingleRecommendGoodsList()
    {
        $skuString = '211751801_1433363';
        $goodsInfoList = $this->GearbestComponent->getGoodsInfo('gb-pc', 'en', $skuString, 'U0001011');
        print_r($goodsInfoList);
    }

    public function testDoubleRecommendGoodsList()
    {
        $skuString = '211751801_1433363,CA0001101_1363221';
        $goodsInfoList = $this->GearbestComponent->getGoodsInfo('gb-pc', 'en', $skuString, 'U000098');
        print_r($goodsInfoList);
    }

    public function testCouponGoodsList()
    {
        //$skuString = '209341401_1433363,171620101_1433363,228070702_1433363';
        $skuString = '228070702_1433363';
        $couponString = 'GBMPPC15';
        $goodsInfoList = $this->GearbestComponent->getGoodsInfo('gb-pc', 'en', $skuString, 'U000105');
        print_r($goodsInfoList);
    }


    public function actionTest2()
    {
        $soaGateway = 'http://10.60.34.84:2087/gateway';
        $soaTokenId = 'bc56a206a3770c926b3c8ea4513cde11';

        $requestHeader = [
            'service' => 'com.globalegrow.spi.goods.common.inter.IGoodsService',
            'method'  => 'querySimpleGoodsInfoForGEShop',
            'version' => '1.0.0',
            'tokenId' => $soaTokenId,
        ];

        $requestBody = [
            'siteCode'      => 'GB',
            'pipelineCode'  => 'GB',
            'platform'      => 1,
            'countryCode'   => 'US',
            'lang'          => 'en',
            'currencyCode'  => 'USD',
            'goodsList'   => [
                ['goodSn' => 'CA0001101', 'virWhCode' => '1433363'],
                ['goodSn' => 'CA0001801', 'virWhCode' => '1363221']
            ]
        ];

        $client = new Client(['timeout' => 10]);
        $promise = $client->postAsync($soaGateway, [
            RequestOptions::HEADERS => ['Content-type' => 'application/json'],
            RequestOptions::BODY => json_encode(['header' => $requestHeader, 'body' => $requestBody])
        ]);

        //等待所有请求完成
        $responses = Promise\unwrap(['price' => $promise]);
        $response = $responses['price'];
        $data = json_decode($response->getBody(), true);
        $data = json_decode($data['body'], true);

        $goodsInfoList = [];
        foreach ($data['data'] as $_goodsInfo) {
            $goodsInfoList[$_goodsInfo['goodSn']] = [
                'goodSn'        => $_goodsInfo['goodSn'],
                'goodTitle'      => $_goodsInfo['goodTitle'],
                'good_sn'        => $_goodsInfo['goodSn'],
                'detailUrl'     => 'https://www.gearbest.com/'. $_goodsInfo['urlTitle'],
                'imageUrl'      => 'https://gloimg.gbtcdn.com/soa/'. $_goodsInfo['mainImage']['imgUrl'],
                'shopCode'      => $_goodsInfo['shopCode'],
                'virWhCode'     => $_goodsInfo['virWhCode'],
                'goodsStatus'   => $_goodsInfo['goodsStatus'],
                'shopPrice'     => $_goodsInfo['shopPrice'],
                'isRecycle'     => $_goodsInfo['isRecycle'],
                'isVirtual'     => $_goodsInfo['isVirtual'],
            ];
        }

        print_r($goodsInfoList);
    }

    public function actionTest1()
    {
        //print_r(app());
        //app()->response->isSent = true;
        $soaGateway = 'http://10.60.34.84:2087/gateway';
        $soaTokenId = 'bc56a206a3770c926b3c8ea4513cde11';

        $requestHeader = [
            'service' => 'com.globalegrow.spi.promotion.common.inter.PriceApi',
            'method'  => 'getPriceList',
            'version' => '1.0.0',
            'tokenId' => $soaTokenId,
        ];

        $requestBody = [
            'siteCode'      => 'GB',
            'pipelineCode'  => 'GB',
            'platform'      => 1,
            'countryCode'   => 'US',
            'lang'          => 'en',
            'currencyCode'  => 'USD',
            'requestList'   => [
                ['warehouseCode' => '1433363', 'goodSn' => '217027701']
            ]
        ];\Yii::info('test', 'soa');

        $client = new Client(['timeout' => 20]);
        $promise = $client->postAsync($soaGateway, [
            RequestOptions::HEADERS => ['Content-type' => 'application/json'],
            RequestOptions::BODY => json_encode(['header' => $requestHeader, 'body' => $requestBody])
        ]);

        //等待所有请求完成
        $responses = Promise\unwrap(['price' => $promise]);
        $response = $responses['price'];
        $data = json_decode($response->getBody(), true);
        $data = json_decode($data['body'], true);
        print_r($data);


//        $response = $client->request('POST', $soaGateway, [
//            RequestOptions::HEADERS => ['Content-type' => 'application/json'],
//            RequestOptions::BODY => json_encode(['header' => $requestHeader, 'body' => $requestBody])
//        ]);
//        $data = json_decode($response->getBody(), true);
//        $data = json_decode($data['body'], true);
//        print_r($data);

        //\Yii::info('test', 'soa');
    }
}