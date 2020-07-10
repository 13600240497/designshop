<?php
/**
 * 组件异步接口兜底配置
 */
//return [
//  'U000001' => [
//    'rg_quick_buy' => [
//      'rg' => [
//        'api'     => 'goods_samelist',
//        'params'  => ['lang' => 'lang', 'client' => 'client'],
//        'pageSize' => 140
//      ]
//    ]
//  ],
//  'U000080' => [
//    'default_v2' => [
//      'rg' => [
//        'api' => '/m-interface-a-getGoodsList.html',
//        'params' => ['lang' => 'lang', 'goodsSn' => 'goodsSKU']
//      ],
//      'zf' => [
//        'api' => '/api/get_seckill_api.php?method=getGoods',
//        'params' => ['pipeline' => 'pipeline', 'lang' => 'lang', 'skuArr' => 'goodsSKU']
//      ]
//    ]
//  ]
//];

return [
  'U000090' => [
    'rg_template1_v1' => [
      'rg' => [
        'api' => 'getrankdetail',
        'params' => [
          'content' => [
            'type' => 'goodsDataSource',
            'pageno' => 1,
            'pagesize' => 12,
            'cateid' => 'cateId|0',
            'lang' => 'lang',
            'pipeline' => 'pipeline'
          ]
        ]
      ]
    ]
  ]
];
