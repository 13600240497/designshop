<?php

namespace app\modules\activity\zf\components;

use app\modules\base\models\AdminModel;
use app\modules\common\zf\components\CommonPageLayoutDesignComponent;
use app\modules\common\zf\components\CommonPageUiDesignComponent;
use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\PageUiModel;
use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\models\PageLayoutModel;
use app\modules\common\zf\models\PageLayoutDataModel;
use app\modules\common\zf\models\PageUiComponentDataModel;
use app\modules\common\zf\models\GoodsManagePageModel;
use app\modules\common\zf\models\GoodsManageDataBlockModel;
use app\modules\common\zf\components\CommonPageConfig;
use app\modules\common\zf\components\CommonCommonComponent;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\component\zf\components\ExplainComponent;
use app\modules\activity\zf\traits\PublishTrait;
use app\modules\activity\zf\components\PageComponent;
use app\modules\activity\zf\components\PageDesignComponent;
use app\modules\activity\zf\components\PageUiComponentDataComponent;
use app\modules\soa\components\IpsComponent;

use ego\base\JsonResponseException;
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\SiteConstants;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii;

/**
 * 页面组件商品管理组件
 */
class GoodsManageComponent extends Component
{
    use PublishTrait;

    /** @var string pc站点 */
    const DEVICE_CODE_PC = SitePlatform::PLATFORM_CODE_PC;
    /** @var string wap站点 */
    const DEVICE_CODE_MOBILE = SitePlatform::PLATFORM_CODE_WAP;
    /** @var string app站点 */
    const DEVICE_CODE_APP = SitePlatform::PLATFORM_CODE_APP;
    /** @var string web站点 */
    const DEVICE_CODE_WEB = SitePlatform::PLATFORM_CODE_WEB;

    /** @var int 最后一个节点的next_id值 */
    const COMPONENT_LAST_NODE_NEXT_ID = 0;

    /** @var int 商品管理UI组件默认在布局中的position */
    const GOODS_MANAGE_UI_DEFAULT_POSITION = 1;

    /** @var string 数据块more_url为空时设置的UI组件模板名称 */
    const COMPONENT_NOMORE_TPL_NAME = 'nomore';

    /** @var array 商品数据默认自动生成对应layout组件key */
    private $defaultLayoutComponentKey = [
        self::DEVICE_CODE_PC     => 'L000001',
        self::DEVICE_CODE_MOBILE => 'L000019',
        self::DEVICE_CODE_APP    => 'L000019'
    ];

    /** @var array 商品数据自动生成对应ui组件key */
    private $uiComponentKey = [
        self::DEVICE_CODE_PC     => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_KEY_PC,
        self::DEVICE_CODE_MOBILE => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_KEY_MOBILE,
        self::DEVICE_CODE_APP    => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_KEY_MOBILE,
        self::DEVICE_CODE_WEB => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_KEY_PC,
    ];

    /** @var array 数据块字段转组件字段 */
    private $dataBlockNameToComponentFieldMap = [
        'category_title' => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_TITLE_TEXT,
        'more_url'       => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_VIEW_HREF,
        'goods_sku'      => GoodsManageDataBlockModel::GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_GOODS_SKU
    ];


    /**
     * 丢弃掉商品标题组合组件单页面复制时
     *
     * @param array $layoutList 布局组件列表,结构参考 CommonPageTplComponent::getPageInfo
     * @param array $uiList     UI组件列表,结构参考 CommonPageTplComponent::getPageInfo
     *
     * @see \app\modules\common\components\CommonPageTplComponent::getPageInfo
     */
    public function discardGoodsTitleCompositeUiComponentWhenPageCopy(&$layoutList, &$uiList)
    {
        $allComponentKeys = array_values($this->uiComponentKey);
        foreach ($uiList as $layoutId => $positionList) {
            foreach ($positionList as $positionId => $uiInfoList) {
                foreach ($uiInfoList as $_uiInfo) {
                    if (in_array($_uiInfo['component_key'], $allComponentKeys, true)) {
                        unset($uiList[ $layoutId ][ $positionId ]);
                    }
                }
            }

            //如果布局下没有UI组件
            if (empty($uiList[ $layoutId ])) {
                unset($uiList[ $layoutId ]);

                foreach ($layoutList as $_key => $_layoutInfo) {
                    if ($layoutId == $_layoutInfo['id']) {
                        unset($layoutList[ $_key ]);
                    }
                }
            }
        }
    }

    /**
     * 装修页面添加UI组件时检查商品标题组合组件
     *
     * @param string $siteCode        站点简码
     * @param string $lang            语言简码
     * @param int    $pageId          活动子页面ID
     * @param int    $layoutId        页面布局ID
     * @param int    $position        页面布局分栏
     * @param string $addComponentKey 组件key
     *
     * @throws \ego\base\JsonResponseException
     */
    public function checkGoodsTitleCompositeUiComponentWhenUiAdd($siteCode, $pageId, $lang, $layoutId, $position, $addComponentKey)
    {
        $device = $this->getDeviceCodeBySiteCode($siteCode);
        if (empty($device) || !isset($this->uiComponentKey[ $device ])) {
            throw new JsonResponseException($this->codeFail, sprintf('站点%s简码无效', $siteCode));
        }

        //当前站点的商品标题组合组件key
        $uiComponentKey = $this->uiComponentKey[ $device ];

        $pageLayouts = PageLayoutModel::find()->where(['page_id' => $pageId, 'lang' => $lang])->asArray()->all();
        if (!empty($pageLayouts)) {
            if (0 === strcmp($uiComponentKey, $addComponentKey)) {
                $pageLayoutIds = array_column($pageLayouts, 'id');
                $count = PageUiModel::find()
                    ->where(['lang' => $lang, 'layout_id' => $pageLayoutIds, 'component_key' => $uiComponentKey])
                    ->count();
                if ($count > 0) {
                    throw new JsonResponseException($this->codeFail, '商品标题组合组件只能被创建一次');
                }
            } else {
                $allUiList = PageUiModel::find()
                    ->where(['lang' => $lang, 'layout_id' => $layoutId, 'position' => $position])
                    ->asArray()
                    ->all();

                if (!empty($allUiList)) {
                    foreach ($allUiList as $ui) {
                        if (0 === strcmp($uiComponentKey, $ui['component_key'])) {
                            throw new JsonResponseException($this->codeFail, '商品标题组合组件不能和其他组件同时放在同一个布局栏中');
                        }
                    }
                }
            }
        }
    }

    /**
     * 装修页面复制UI组件时检查商品标题组合组件
     *
     * @param string $siteCode 站点简码
     * @param int    $uiId     Ui组件ID
     *
     * @throws \ego\base\JsonResponseException
     */
    public function checkGoodsTitleCompositeUiComponentWhenUiCopy($siteCode, $uiId)
    {
        $device = $this->getDeviceCodeBySiteCode($siteCode);
        if (empty($device) || !isset($this->uiComponentKey[ $device ])) {
            throw new JsonResponseException($this->codeFail, sprintf('站点%s简码无效', $siteCode));
        }

        //当前站点的商品标题组合组件key
        $uiComponentKey = $this->uiComponentKey[ $device ];

        /** @var \app\modules\common\models\PageUiModel $uiModel */
        $uiModel = PageUiModel::findOne($uiId);
        if (!$uiModel) {
            throw new JsonResponseException($this->codeFail, 'copy_id未找到对应的UI组件');
        }

        if ($uiModel->component_key === $uiComponentKey) {
            throw new JsonResponseException($this->codeFail, '商品标题组合组件不能复制');
        }
    }

    /**
     * 装修页面复制布局组件时检查商品标题组合组件
     *
     * @param string $siteCode 站点简码
     * @param string $lang     语言简码
     * @param int    $layoutId 页面布局ID
     *
     * @throws \ego\base\JsonResponseException
     */
    public function checkGoodsTitleCompositeUiComponentWhenLayoutCopy($siteCode, $lang, $layoutId)
    {
        $device = $this->getDeviceCodeBySiteCode($siteCode);

        if (empty($device) || !isset($this->uiComponentKey[ $device ])) {
            throw new JsonResponseException($this->codeFail, sprintf('站点%s简码无效', $siteCode));
        }

        //当前站点的商品标题组合组件key
        $uiComponentKey = $this->uiComponentKey[ $device ];

        $count = PageUiModel::find()
            ->where(['lang' => $lang, 'layout_id' => $layoutId, 'component_key' => $uiComponentKey])
            ->count();
        if ($count > 0) {
            throw new JsonResponseException($this->codeFail, '布局内包含商品标题组合组件不能复制');
        }
    }

    /**
     * 商品管理活动页面列表
     *
     * @param array $params GET参数
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function lists($params)
    {
        if (empty($params['site_code'])
            || !SitePlatform::isCurrentSiteGroupPlatformSite($params['site_code'])
        ) {
            throw new JsonResponseException($this->codeFail, '无效的site_code');
        }
        $siteCode = $params['site_code'];

        $params['pageSize'] = $params['pageSize'] ?? 20;
        $conditions = [
            'p.site_code'   => $siteCode,
        ];

        $totalCount = GoodsManagePageModel::find()->alias('p')->where($conditions)->count();
        $pagination = Pagination::new($totalCount);

        //查询商品管理页面数据
        $activityPageList = GoodsManagePageModel::find()->alias('p')
            ->select('p.*, a.name as activity_name, l.title as page_title, a.update_time, u.realname as create_name, pm.status')
            ->leftJoin(PageModel::tableName() . ' as pm', 'pm.id=p.page_id')
            ->leftJoin(AdminModel::tableName() . ' as u', 'pm.create_user = u.username')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id=p.page_id')
            ->leftJoin(ActivityModel::tableName() . ' as a', 'a.id = p.activity_id')
            ->where($conditions)
            ->groupBy('p.id')
            ->orderBy('p.id desc')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()
            ->all();
        foreach ($activityPageList as &$activityPage) {
            $activityPage['status'] = (int)$activityPage['status'];
        }


        return app()->helper->arrayResult($this->codeSuccess, 'success', [
            'list'       => $activityPageList,
            'pagination' => [
                $pagination->pageParam     => (int) $pagination->page + 1,
                $pagination->pageSizeParam => (int) $pagination->pageSize,
                'totalCount'               => (int) $pagination->totalCount
            ]
        ]);
    }

    /**
     * 商品管理活动组页面预览
     *
     * @param string $groupId 组ID
     *
     * @return array
     */
    public function groupPreview($groupId)
    {
        if (empty($groupId)) {
            return app()->helper->arrayResult($this->codeFail, '参数groupId无效');
        }

        //组装数据
        $previewUrls = $this->getGroupPreviewUrl($groupId);

        return app()->helper->arrayResult($this->codeSuccess, 'success', $previewUrls);
    }

    /**
     * 页面商品sku列表
     *
     * @param array $params GET参数
     *
     * @return array
     */
    public function skuList($params)
    {
        if (empty($params['gmpId']) || !is_numeric($params['gmpId'])) {
            return app()->helper->arrayResult($this->codeFail, '参数gmpId无效');
        }

        $gmpId = (int) $params['gmpId'];
        $viewType = empty($params['viewType']) ? 1 : (int) $params['viewType'];

        $goodsManagePageModel = GoodsManagePageModel::getById($gmpId);
        if (!$goodsManagePageModel) {
            return app()->helper->arrayResult($this->codeFail, '页面没有找到', []);
        }

        $dataBlockList = GoodsManageDataBlockModel::find()
            ->where(['gmp_id' => $gmpId])
            ->orderBy('sort_num asc')
            ->asArray()
            ->all();

        $ipsComponent = new IpsComponent();

        $jsonData = [];
        if (1 == $viewType) {
            $jsonData['sku_list'] = [];

            //按照语言分组
            foreach ($dataBlockList as $dataBlock) {
                $goodsSkuList = $dataBlock['goods_sku'];
                if (SiteConstants::GOODS_SKU_FROM_IPS === (int) $dataBlock['sku_from']) {
                    $ipsInfo = json_decode($dataBlock['goods_sku'], true);
                    list($goodsSkuList) = $ipsComponent->getGoodsSkuFromIps($ipsInfo['gsSelectLevel2']['value']);
                }

                $jsonData['sku_list'][ $dataBlock['lang'] ][] = [
                    'title' => $dataBlock['category_title'],
                    'sku'   => $goodsSkuList
                ];
            }

            //语言列表
            $jsonData['lang_list'] = [];
            $langConf = app()->params['lang'];
            foreach (array_keys($jsonData['sku_list']) as $_lang) {
                $jsonData['lang_list'][] = [
                    'key'  => $_lang,
                    'name' => $langConf[ $_lang ]['name']
                ];
            }

        } else {
            $skuList = [];

            //按照语言分组
            foreach ($dataBlockList as $dataBlock) {
                if (empty($dataBlock['goods_sku']))
                    continue;

                $lang = $dataBlock['lang'];
                if (!isset($skuList[ $lang ]))
                    $skuList[ $lang ] = [];

                $goodsSkuList = $dataBlock['goods_sku'];
                if (SiteConstants::GOODS_SKU_FROM_IPS === (int) $dataBlock['sku_from']) {
                    $ipsInfo = json_decode($dataBlock['goods_sku'], true);
                    list($goodsSkuList) = $ipsComponent->getGoodsSkuFromIps($ipsInfo['gsSelectLevel2']['value']);
                }

                $skuList[ $lang ] = array_merge($skuList[ $lang ], explode(',', $goodsSkuList));
            }

            foreach ($skuList as $key => $val) {
                $skuList[ $key ] = join(',', array_unique($val));
            }

            //语言列表
            $jsonData['lang_list'] = [];
            $langConf = app()->params['lang'];
            foreach (array_keys($skuList) as $_lang) {
                $jsonData['lang_list'][] = [
                    'key'  => $_lang,
                    'name' => $langConf[ $_lang ]['name']
                ];
            }

            $jsonData['all_sku'] = $skuList;
        }

        return app()->helper->arrayResult($this->codeSuccess, 'success', $jsonData);
    }

    /**
     * 编辑活动管理
     *
     * @param string $groupId
     *
     * @return array
     */
    public function editGroup($groupId)
    {
        if (empty($groupId)) {
            return app()->helper->arrayResult($this->codeFail, '参数groupId无效');
        }

        //根据组ID查询组下面的页面
        $groupPageList = GoodsManagePageModel::getGoodsManagePageByGroupId($groupId);
        if (!$groupPageList) {
            return app()->helper->arrayResult($this->codeFail, '无效的参数groupId', []);
        }
        $groupPageList = ArrayHelper::toArray($groupPageList);

        $siteName = stristr($groupPageList[0]['site_code'], '-', true);
        $dataJson = ['group_id' => $groupId];

        //获取group下已经选择的活动页面
        $selectedList = GoodsManagePageModel::find()->alias('p')
            ->select('p.*, u.realname as create_name')
            ->leftJoin(PageModel::tableName() . ' as pm', 'pm.id=p.page_id')
            ->leftJoin(AdminModel::tableName() . ' as u', 'pm.create_user = u.username')
            ->where(['group_id' => $groupId])
            ->asArray()
            ->all();
        if (!empty($selectedList)) {
            $selectedPages = [];
            array_map(function ($item) use (&$selectedPages) {
                $deviceCode = $this->getDeviceCodeBySiteCode($item['site_code']);
                $selectedPages[ $deviceCode ] = $item;
            }, $selectedList);

            $dataJson['selected'] = $selectedPages;
        }

        //查询站点下所有端的活动
        $activityList = ActivityModel::find()
            ->where(['is_delete' => ActivityModel::NOT_DELETE, 'mold' => SiteConstants::ACTIVITY_TYPE_SPECIAL])
            ->andWhere("site_code LIKE :siteName", [':siteName' => $siteName . '%'])
            ->orderBy('id desc')
            ->asArray()
            ->all();

        //活动页面按端分组
        $activities = [];
        if (!empty($activityList)) {
            array_map(function ($activity) use (&$activities) {
                $deviceCode = $this->getDeviceCodeBySiteCode($activity['site_code']);

                $activity['children'] = []; //方便前端使用
                $activities[ $deviceCode ][] = $activity;
                //过滤掉加锁非本人创建的活动
//                $canEdit = (($activity['is_lock'] == ActivityModel::IS_LOCK)
//                    && (app()->user->username == $activity['create_user'])) ? true : false;
//                if (($activity['is_lock'] == ActivityModel::UN_LOCK) || $canEdit) {
//                    $activities[$deviceCode][] = $activity;
//                }
            }, $activityList);

            $devices = [static::DEVICE_CODE_PC, static::DEVICE_CODE_APP, static::DEVICE_CODE_APP];
            foreach ($devices as $device) {
                if (!isset($activities[ $device ])) {
                    $activities[ $device ] = [];
                }
            }

        }
        $dataJson['activity_list'] = $activities;

        //查询页面下的数据块
        $maxDataBlockSize = 0;
        foreach ($groupPageList as &$groupPage) {
            unset($groupPage['group_id']);
            $dataBlockList = GoodsManageDataBlockModel::find()
                ->where(['gmp_id' => $groupPage['id']])
                ->orderBy('sort_num asc')
                ->asArray()
                ->all();
            $pageLangDataBlockList = [];
            if (!empty($dataBlockList)) {
                $componentIds = array_column($dataBlockList, 'component_id');
                $componentIds = array_filter($componentIds, function ($_id) {
                    return (int) $_id > 0;
                });

                $uiComponentsMap = PageUiModel::find()->where(['id' => $componentIds])->indexBy('id')->all();
                //按语言分组
                foreach ($dataBlockList as $dataBlock) {
                    $dataBlock['need_sync'] = 0;
                    if (!empty($dataBlock['component_id']) && ((int) $dataBlock['component_id'] > 0)) {
                        $dataBlock['need_sync'] = isset($uiComponentsMap[ $dataBlock['component_id'] ]) ? 0 : 1;
                    }

                    if (SiteConstants::GOODS_SKU_FROM_IPS === (int) $dataBlock['sku_from']) {
                        $dataBlock['ips'] = json_decode($dataBlock['goods_sku'], true);
                        $dataBlock['goods_sku'] = '';
                    } else {
                        //方便前端使用
                        $dataBlock['ips'] = [
                            'gsSelectLevel0' => ['value' => '', 'label' => ''],
                            'gsSelectLevel1' => ['value' => '', 'label' => ''],
                            'gsSelectLevel2' => ['value' => '', 'label' => '']
                        ];
                    }
                    $pageLangDataBlockList[ $dataBlock['lang'] ][] = $dataBlock;
                }

                //计算最大的数据块数量
                foreach ($pageLangDataBlockList as $_langBlockList) {
                    $blockSize = count($_langBlockList);
                    if ($blockSize > $maxDataBlockSize) {
                        $maxDataBlockSize = $blockSize;
                    }
                }
            }
            $groupPage['lang_list'] = $pageLangDataBlockList;
        }

        $dataJson['maxDataBlockSize'] = $maxDataBlockSize;
        $dataJson['page_list'] = $groupPageList;

        return app()->helper->arrayResult($this->codeSuccess, 'success', $dataJson);
    }

    /**
     * 当前站点下所有端活动列表
     *
     * @param string $groupId 组ID
     *
     * @return array
     */
    public function activityList($groupId)
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        $siteName = strtolower($siteGroupCode);
        $dataJson = ['selected' => [], 'activity_list' => []];

        if (!empty($groupId)) {
            //获取group下已经选择的活动页面
            $selectedList = GoodsManagePageModel::find()->where(['group_id' => $groupId])->asArray()->all();
            if (!empty($selectedList)) {
                $selectedPages = [];
                array_map(function ($item) use (&$selectedPages) {
                    $deviceCode = $this->getDeviceCodeBySiteCode($item['site_code']);
                    $selectedPages[ $deviceCode ] = $item;
                }, $selectedList);

                $dataJson['selected'] = $selectedPages;
            }
        }

        //查询站点下所有端的活动
        $activityList = ActivityModel::find()
            ->where(['is_delete' => ActivityModel::NOT_DELETE, 'mold' => SiteConstants::ACTIVITY_TYPE_SPECIAL])
            ->andWhere("site_code LIKE :siteName", [':siteName' => $siteName . '%'])
            ->orderBy('id desc')
            ->asArray()
            ->all();

        //活动页面按端分组
        $activities = [];
        if (!empty($activityList)) {
            array_map(function ($activity) use (&$activities) {
                //$activity['langList'] = ActivityModel::getLangListByLangString($activity['lang']);
                $deviceCode = $this->getDeviceCodeBySiteCode($activity['site_code']);

                $activity['children'] = []; //方便前端使用
                $activities[ $deviceCode ][] = $activity;
                //过滤掉加锁非本人创建的活动
//                $canEdit = (($activity['is_lock'] == ActivityModel::IS_LOCK)
//                    && (app()->user->username == $activity['create_user'])) ? true : false;
//                if (($activity['is_lock'] == ActivityModel::UN_LOCK) || $canEdit) {
//                    $activities[$deviceCode][] = $activity;
//                }
            }, $activityList);

            $devices = [static::DEVICE_CODE_PC, static::DEVICE_CODE_APP, static::DEVICE_CODE_APP];
            foreach ($devices as $device) {
                if (!isset($activities[ $device ])) {
                    $activities[ $device ] = [];
                }
            }
        }

        $dataJson['activity_list'] = $activities;

        return app()->helper->arrayResult($this->codeSuccess, 'success', $dataJson);
    }

    /**
     * 单个活动下所有页面列表
     *
     * @param int $activityId 活动ID
     *
     * @return array
     */
    public function activityPageList($activityId)
    {
        if (!$activityId) {
            return app()->helper->arrayResult($this->codeFail, 'activity_id不能为空');
        }

        $pageComponent = new PageComponent();
        $result = $pageComponent->lists($activityId);

        $existsPages = [];
        $pageIds = array_column($result['data']['list'], 'id');
        if (!empty($pageIds)) {
            $existsPages = GoodsManagePageModel::find()
                ->select('id,page_id')
                ->where(['page_id' => $pageIds])
                ->indexBy('page_id')
                ->asArray()
                ->all();
        }

        foreach ($result['data']['list'] as &$pageInfo) {
            //判断页面是否被管理过
            $isManaged = 0;
            if (isset($existsPages[ $pageInfo['id'] ])) {
                $isManaged = 1;
            }

            $pageInfo['is_managed'] = $isManaged;
            $pageInfo['preview_url'] = Url::to([
                '/activity/goods-manage/preview',
                'pid' => $pageInfo['pid']
            ], true);
        }

        return $result;
    }

    /**
     * 检查指定站点商品SKU是否存在
     *
     * @param array $params GET参数
     *
     * @return array
     */
    public function checkGoodsExists($params)
    {
        //校验传参
        $rules = [
            [['siteCode', 'lang', 'skuList'], 'required']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult($this->codeFail, implode('|', array_column($model->errors, 0)));
        }

        //添加空数据过滤
        $skuArr = explode(',', $params['skuList']);
        if (!empty($skuArr)) {
            $skuArr = array_filter($skuArr);
            $params['skuList'] = join(',', $skuArr);
        }
        // 获取站点下指定sku商品数据
        $explainComponent = new ExplainComponent();
        $responseSku = $explainComponent->getGoodsList(
            $params['skuList'], $params['lang'], $params['siteCode'], $params['pipeline']
        );
        $existsSkuArr = empty($responseSku) ? [] : array_column($responseSku, 'goods_sn');

        $diffSkuArr = array_diff($skuArr, $existsSkuArr);
        if (!empty($diffSkuArr)) {
            $diffSkuStr = implode(',', $diffSkuArr);

            return app()->helper->arrayResult($this->codeFail, "SKU {$diffSkuStr} 不存在", $diffSkuStr);
        }

        return app()->helper->arrayResult($this->codeSuccess, 'success', []);
    }

    /**
     * 页面配置动态预览，具体实现查看 getPageLayoutAndUiByPageId
     *
     * @param string $pid    活动页面pid
     * @param array  $params POST参数
     *
     * @return string
     * @see        getPageLayoutAndUiByPageId
     * @see        \app\modules\activity\traits\PublishTrait::preview
     * @deprecated 没有使用，但不删除，以便以后使用
     */
    public function preview($pid, $params)
    {
        if (empty($pid) || empty($params['lang']) || empty($params['content']) || empty($params['pipeline'])) {
            return '参数不全';
        }

        $lang = $params['lang'];
        if (!($pageModel = PageModel::getByPId($pid))) {
            return '页面不存在或已被删除';
        }

        $pageId = $pageModel->id;
        $componentStatic['css'] =
            '<link rel="stylesheet" href="'
            . (new CommonPageConfig())->staticDomain
            . '/resources/stylesheets/common/geshop-grid.css?2018070301">';
        //解决站点组件JS未加载的问题
        $componentStatic['js'] =
            '<script type="text/javascript">var GESHOP_STATIC = "' . (new CommonPageConfig())->staticDomain . '";</script>'
            . '<script src="' . (new CommonPageConfig())->staticDomain . '/resources/javascripts/library/LAB.js?version='
            . $_SERVER['REQUEST_TIME'] . '"></script>
            <script src="' . (new CommonPageConfig())->staticDomain . '/resources/sitesPublic/js/gs_common.min.js?version='
            . $_SERVER['REQUEST_TIME'] . '"></script>';

        //获取头尾
        $result = $this->getHeadAndFooterByPageId($pageId, $lang, $componentStatic, $params['pipeline']);

        //页面预览时，过滤掉可拖拽属性
        $html = str_replace(self::$dragClass, '', $this->parsePage($pageId, $lang, true));
        //页面的content用div包起来
        $html = $this->packageContent($html);

        if (!empty($result)) {
            $main = '/<!--\s*geshop\s*main\s*start\s*-->/';
            preg_match($main, $result, $matches);
            if (!empty($matches[0])) {
                $html = str_replace($matches[0], $matches[0] . $html, $result);
            }
        }

        return $html;
    }

    /**
     * 添加商品管理数据
     *
     * @param string $jsonContent
     *
     * @return array
     */
    public function add($jsonContent)
    {
        if (empty($jsonContent)) {
            return app()->helper->arrayResult($this->codeFail, '参数content不能为空', []);
        }

        $pageDataList = json_decode($jsonContent, true);
        if (is_array($pageDataList) && count($pageDataList) > 3) {
            return app()->helper->arrayResult($this->codeFail, '商品管理页面只有PC,M,APP三个端', []);
        }

        //生成分组ID
        $groupId = md5(microtime() . random_int(0, 100));

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            foreach ($pageDataList as $pageData) {
                // 保存页面数据
                $this->addGoodsMangePageDataBlocks($groupId, $pageData);
            }

            $previewUrls = $this->getGroupPreviewUrl($groupId);
            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '添加成功', [
                'groupId'      => $groupId,
                'preview_urls' => $previewUrls
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage(), []);
        }
    }

    /**
     * 添加商品管理数据
     *
     * @param string $groupId 商品管理组ID
     * @param string $jsonContent
     *
     * @return array
     */
    public function edit($groupId, $jsonContent)
    {
        if (empty($groupId)) {
            return app()->helper->arrayResult($this->codeFail, '参数groupId不能为空', []);
        }

        if (empty($jsonContent)) {
            return app()->helper->arrayResult($this->codeFail, '参数content不能为空', []);
        }

        $pageDataList = json_decode($jsonContent, true);
        if (is_array($pageDataList) && count($pageDataList) > 3) {
            return app()->helper->arrayResult($this->codeFail, '商品管理页面只有PC,M,APP三个端', []);
        }

        //根据组ID查询组下面的页面
        $goodsManagePageModelList = GoodsManagePageModel::find()
            ->where(['group_id' => $groupId])
            ->indexBy('page_id')
            ->all();
        if (empty($goodsManagePageModelList)) {
            return app()->helper->arrayResult($this->codeFail, '无效的参数groupId', []);
        }

        //查询所有端页面下的所有数据块
        $oldActivityPageIds = array_keys($goodsManagePageModelList);
        $oldGroupDataBlockList = GoodsManageDataBlockModel::find()
            ->where(['gmp_id' => array_column($goodsManagePageModelList, 'id')])
            ->indexBy('id')
            ->all();

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            $newActivityPageIds = $deletedActivityPageIds = [];
            foreach ($pageDataList as $pageData) {
                $this->checkPageDataIntegrity($pageData);

                $activityPageId = $pageData['page_id'];
                $activitySiteCode = $pageData['site_code'];
                $newActivityPageIds[] = $activityPageId;
                if (in_array($activityPageId, $oldActivityPageIds)) {
                    /** @var \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel */
                    $goodsManagePageModel = $goodsManagePageModelList[ $activityPageId ];
                    $oldPageDataBlockList = $this->getPageDataBlockList($oldGroupDataBlockList, $goodsManagePageModel->id);
                    //更新页面数据
                    $this->updateGoodsMangePageDataBlocks($goodsManagePageModel, $oldPageDataBlockList, $pageData);
                } else {
                    //删除被替换的页面数据
                    /** @var \app\modules\common\models\GoodsManagePageModel $_pageModel */
                    foreach ($goodsManagePageModelList as $_pageModel) {
                        if (0 === strcmp($activitySiteCode, $_pageModel->site_code)) {
                            $deletedActivityPageIds[] = $_pageModel->page_id;
                            //删除商品管理活动子页面，但不同步子页面组件
                            $this->delGoodsManagePageAndDataBlocks($_pageModel);
                            break;
                        }
                    }

                    // 添加页面数据
                    $this->addGoodsMangePageDataBlocks($groupId, $pageData);
                }
            }

            //删除没有选择的活动页面
            $diffActivityPageIds = array_diff($oldActivityPageIds, $newActivityPageIds);
            if (!empty($diffActivityPageIds)) {
                foreach ($diffActivityPageIds as $_activityPageId) {
                    //替换操作已经删除，这里就不重复删除
                    if (in_array($_activityPageId, $deletedActivityPageIds)) {
                        continue;
                    }

                    //删除商品管理活动子页面，但不同步子页面组件
                    $this->delGoodsManagePageAndDataBlocks($goodsManagePageModelList[ $_activityPageId ]);
                }
            }

            $previewUrls = $this->getGroupPreviewUrl($groupId);
            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, '修改成功', [
                'groupId'      => $groupId,
                'preview_urls' => $previewUrls
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage(), []);
        }
    }

    /**
     * 同步数据块到装修界面的UI组件
     *
     * @param string $groupId
     * @param int    $pageId
     * @param string $lang
     *
     * @return array
     */
    public function syncDataBlockUiComponent($groupId, $pageId, $lang)
    {
        if (empty($groupId)) {
            return app()->helper->arrayResult($this->codeFail, '参数groupId不能为空', []);
        }

        //根据组ID查询组下面的页面
        $goodsManagePageModelList = GoodsManagePageModel::find()
            ->where(['group_id' => $groupId])
            ->indexBy('page_id')
            ->all();
        if (empty($goodsManagePageModelList)) {
            return app()->helper->arrayResult($this->codeFail, '无效的参数groupId', []);
        }

        $conditions = [];
        if (empty($pageId)) {
            $conditions['gmp_id'] = array_column($goodsManagePageModelList, 'id');
        } else {
            if (!isset($goodsManagePageModelList[ $pageId ])) {
                return app()->helper->arrayResult($this->codeFail, '无效的参数pageId', []);
            }

            /** @var \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel */
            $goodsManagePageModel = $goodsManagePageModelList[ $pageId ];
            $conditions['gmp_id'] = $goodsManagePageModel->id;
        }

        if (!empty($lang)) {
            $conditions['lang'] = $lang;
        }

        //查询满足条件所有数据块
        $allGroupDataBlockModelList = GoodsManageDataBlockModel::find()
            ->where($conditions)
            ->indexBy('id')
            ->orderBy('sort_num asc')
            ->all();
        if (empty($allGroupDataBlockModelList)) {
            return app()->helper->arrayResult($this->codeFail, '没有需求同步的数据块', []);
        }

        $allPageLangDataBlockList = [];
        /** @var \app\modules\common\models\GoodsManageDataBlockModel $dataBlockModel */
        foreach ($allGroupDataBlockModelList as $dataBlockModel) {
            $key = $dataBlockModel->gmp_id . '-' . $dataBlockModel->lang;
            $allPageLangDataBlockList[ $key ][] = $dataBlockModel;
        }

        //事物开始
        $transaction = app()->db->beginTransaction();
        try {
            $goodsManagePageModelMap = array_column($goodsManagePageModelList, null, 'id');
            foreach ($allPageLangDataBlockList as $key => $orderedDataBlockList) {
                list ($gmpId, $lang) = explode('-', $key);

                /** @var \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel */
                $goodsManagePageModel = $goodsManagePageModelMap[ $gmpId ];

                //页面支持的语言
                $supportLanguages = $this->getPageLanguageList($goodsManagePageModel->page_id);

                $pageLangDataInfo = [
                    'site_code'         => $goodsManagePageModel->site_code,
                    'page_id'           => $goodsManagePageModel->page_id,
                    'support_languages' => $supportLanguages,
                    'lang'              => $lang
                ];

                $supportLanguageCodes = array_column(ArrayHelper::toArray($pageLangDataInfo['support_languages']), 'lang');
                //判断当前语言页面是否支持
                if (in_array($lang, $supportLanguageCodes)) {
                    $this->syncGoodsMangePageLangDataBlockComponents($pageLangDataInfo, $orderedDataBlockList);
                }
            }

            $transaction->commit();

            return app()->helper->arrayResult($this->codeSuccess, 'success', []);
        } catch (\Exception $e) {
            $transaction->rollBack();

            return app()->helper->arrayResult($this->codeFail, $e->getMessage(), []);
        }

    }

    /**
     * 保存商品数据，并返回预览地址
     *
     * @param string $groupId 商品管理组ID
     * @param string $jsonContent
     *
     * @return array
     */
    public function saveAndPreview($groupId, $jsonContent)
    {
        if (empty($groupId)) {
            return $this->add($jsonContent);
        }

        return $this->edit($groupId, $jsonContent);
    }

    /**
     * 验证页面数据的完整性
     *
     * @param array $pageData 页面数据
     *
     * @throws \yii\base\Exception
     */
    private function checkPageDataIntegrity($pageData)
    {
        if (!isset($pageData['site_code'], $pageData['page_id'], $pageData['lang_list'])) {
            throw new Exception(sprintf('页面%d数据不完整', $pageData['page_id']));
        }

        if (!is_array($pageData['lang_list']) || empty($pageData['lang_list'])) {
            throw new Exception('商品列表不能为空');
        }
    }

    /**
     * 验证页面数据块的完整性
     *
     * @param array $dataBlock 数据块
     *
     * @throws \yii\base\Exception
     */
    private function checkPageDataBlockIntegrity($dataBlock)
    {
        if (empty($dataBlock) || !is_array($dataBlock)) {
            throw new Exception('页面中包含无效的数据块');
        }

        if (empty($dataBlock['category_title']) || empty($dataBlock['sku_from'])) {
            throw new Exception('商品列表里分类标题和商品数据为必填项');
        }

        $skuFrom = (int) $dataBlock['sku_from'];
        if (!in_array($skuFrom, [SiteConstants::GOODS_SKU_FROM_INPUT, SiteConstants::GOODS_SKU_FROM_IPS], true)) {
            throw new Exception('请选择商品数据来源');
        }

        if (SiteConstants::GOODS_SKU_FROM_IPS === $skuFrom) {
            if (empty($dataBlock['ips']) || !is_array($dataBlock['ips']) || !isset($dataBlock['ips']['gsSelectLevel2'])) {
                throw new Exception('商品列表里选品系统分类没有选择');
            }
        } else {
            if (empty($dataBlock['goods_sku'])) {
                throw new Exception('商品列表里SKU为必填项');
            }

            $skuList = explode(',', $dataBlock['goods_sku']);
            foreach ($skuList as $sku) {
                if (empty($sku))
                    continue;

                if (!preg_match("/^[A-Za-z0-9]+$/s", $sku)) {
                    throw new Exception('商品列表里SKU不合法，SKU包含数字和英文字符');
                }
            }
        }
    }

    /**
     * 过滤指定页面的数据块
     *
     * @param array $groupDataBlockList 所有端的数据块
     * @param int   $gmpId              商品管理页面的ID
     *
     * @return array
     */
    private function getPageDataBlockList($groupDataBlockList, $gmpId)
    {
        return array_filter($groupDataBlockList, function ($item) use ($gmpId) {
            return (int) $item->gmp_id === (int) $gmpId;
        });
    }

    /**
     * 过滤页面指定语言的数据块
     *
     * @param array  $pageDataBlockList 所有端的数据块
     * @param string $lang              语言简码
     *
     * @return array
     */
    private function getPageLangDataBlockList($pageDataBlockList, $lang)
    {
        return array_filter($pageDataBlockList, function ($item) use ($lang) {
            return $item->lang === $lang;
        });
    }

    /**
     * 更新单个页面的数据块
     *
     * @param \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel
     * @param array                                           $oldPageDataBlockList 源页面数据块列表
     * @param array                                           $pageData             页面数据,格式：
     *
     * - site_code                      站点代码
     * - page_id                        活动页面ID
     * - lang_list                      多语言列表
     * - lang_list.en                   当前语言数据块列表
     * - lang_list.en.category_title    商品分类的标题名称
     * - lang_list.en.more_url          view more 链接
     * - lang_list.en.goods_sku         商品sku列表，多个用英文逗号分隔
     *
     * @throws \yii\base\Exception
     */
    private function updateGoodsMangePageDataBlocks($goodsManagePageModel, $oldPageDataBlockList, $pageData)
    {
        $this->checkPageDataIntegrity($pageData);

        /** @var \app\modules\common\models\PageModel $pageModel */
        //$pageModel = PageModel::findOne((int)$pageData['page_id']);
        $pageModel = PageModel::find()->alias('p')->select('p.*, l.title')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id=p.id')
            ->where(['p.id' => $pageData['page_id']])
            ->one();

        if (!$pageModel) {
            throw new Exception('活动子页面不存在');
        }

        $activityPageId = $pageModel->id;
        $activityPageTitle = $pageModel->title;

        /** @var \app\modules\common\models\ActivityModel $activityModel */
//        //判断子页面对应的活动是本人加锁
//        $activityModel = ActivityModel::find()->where(['id' => $pageModel->activity_id])->one();
//        if (($activityModel->is_lock == ActivityModel::IS_LOCK)  && (app()->user->username != $pageModel->create_user)) {
//            throw new Exception(sprintf('活动子页面[%s]已锁定，不能被管理', $activityPageTitle));
//        }

        //页面支持的语言
        $supportLanguages = $this->getPageLanguageList($activityPageId);

        //保存配置数据
        foreach ($pageData['lang_list'] as $lang => $pageLangDataBlockList) {
            $pageLangDataInfo = [
                'site_code'         => $pageData['site_code'],
                'page_id'           => $pageData['page_id'],
                'support_languages' => $supportLanguages,
                'lang'              => $lang
            ];
            $oldPageLangDataBlockList = $this->getPageLangDataBlockList($oldPageDataBlockList, $lang);
            $this->updateGoodsMangePageLangDataBlocks($goodsManagePageModel, $oldPageLangDataBlockList, $pageLangDataInfo, $pageLangDataBlockList);
        }
    }

    /**
     * 更新单个页面里单个语言的数据块
     *
     * @param \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel
     * @param array                                           $oldPageLangDataBlockList 源页面单个语言数据块列表
     * @param array                                           $pageLangDataInfo         页面数据,格式参考 updateGoodsMangePageDataBlocks 方法
     * @param array                                           $pageLangDataBlockList    新页面单个语言数据块列表
     *
     * @throws \yii\base\Exception
     */
    private function updateGoodsMangePageLangDataBlocks($goodsManagePageModel, $oldPageLangDataBlockList, $pageLangDataInfo, $pageLangDataBlockList)
    {
        $lang = $pageLangDataInfo['lang'];
        $activityPageId = $pageLangDataInfo['page_id'];
        if (empty($pageLangDataBlockList)) {
            $langName = app()->params['lang'][ $lang ]['name'];
            throw new Exception(sprintf('语言[%s]下商品列表不能为空', $langName));
        }

        // 按sort_num
        $orderedDataBlockList = [];
        if (!empty($oldPageLangDataBlockList)) {
            $oldPageLangDataBlockList = array_column($oldPageLangDataBlockList, null, 'sort_num');
            ksort($oldPageLangDataBlockList);
            $orderedDataBlockList = array_values($oldPageLangDataBlockList);
        }

        $index = 0;
        $newOrderedDataBlockList = [];
        foreach ($pageLangDataBlockList as $langDataBlock) {
            $this->checkPageDataBlockIntegrity($langDataBlock);

            $newDataBlockModel = $this->getPageDataBlockModel($goodsManagePageModel->id, $lang, ($index + 1), $langDataBlock);
            /** @var \app\modules\common\models\GoodsManageDataBlockModel $oldDataBlockModel */
            $oldDataBlockModel = $orderedDataBlockList[ $index++ ] ?? null;
            if (!empty($oldDataBlockModel)) {
                if (0 === strcmp($newDataBlockModel->data_md5, $oldDataBlockModel->data_md5)) {
                    $newOrderedDataBlockList[] = $oldDataBlockModel;
                    continue;
                } else {
                    //更新数据块
                    $newDataBlockModel->setOldAttributes($oldDataBlockModel->getOldAttributes());
                    $newDataBlockModel->id = $oldDataBlockModel->id;
                    if (!$newDataBlockModel->update(false)) {
                        throw new Exception(sprintf('页面[%d]下语言[%s]商品列表更新失败', $activityPageId, $lang));
                    }
                    $newOrderedDataBlockList[] = $newDataBlockModel;
                }
            } else {
                if (!$newDataBlockModel->insert(false)) {
                    throw new Exception(sprintf('页面[%d]下语言[%s]商品列表插入失败', $activityPageId, $lang));
                }
                $newOrderedDataBlockList[] = $newDataBlockModel;
            }
        }

        //删除多余老数据
        if (!empty($orderedDataBlockList)) {
            $redundantDataBlockList = array_slice($orderedDataBlockList, $index);
            if (!empty($redundantDataBlockList)) {
                foreach ($redundantDataBlockList as $_dataBlockModel) {
                    if (!$_dataBlockModel->delete()) {
                        throw new Exception(sprintf('页面[%d]下语言[%s]商品列表删除失败', $activityPageId, $lang));
                    }
                }
            }
        }
        unset($orderedDataBlockList);

        $supportLanguageCodes = array_column(ArrayHelper::toArray($pageLangDataInfo['support_languages']), 'lang');
        //判断当前语言页面是否支持
        if (in_array($lang, $supportLanguageCodes)) {
            $this->syncGoodsMangePageLangDataBlockComponents($pageLangDataInfo, $newOrderedDataBlockList);
        }
    }

    /**
     * 同步单个页面里单个语言的数据块组件
     *
     * @param array $pageLangDataInfo     页面数据,格式参考 updateGoodsMangePageDataBlocks 方法
     * @param array $orderedDataBlockList 数据块Model列表
     */
    private function syncGoodsMangePageLangDataBlockComponents($pageLangDataInfo, $orderedDataBlockList)
    {
        $siteCode = $pageLangDataInfo['site_code'];
        $activityPageId = $pageLangDataInfo['page_id'];
        $lang = $pageLangDataInfo['lang'];

        list($goodsManageLayout, $layoutPosition) = $this->syncGoodsManagePageLangLayoutComponent($siteCode, $activityPageId, $lang);
        $this->syncGoodsManagePageLangUiComponents(
            $siteCode, $activityPageId, $goodsManageLayout, $layoutPosition, $orderedDataBlockList
        );
    }

    /**
     * 根据当前页面数据块的数据同步商品管理UI组件，少于数据块就创建组件，大于数据块就删除多余组件
     * 保存组件的数量和数据块的数量一致
     *
     * @param string                                     $siteCode             站点简码
     * @param int                                        $activityPageId       活动页面ID
     * @param \app\modules\common\models\PageLayoutModel $goodsManageLayout    布局组件Model
     * @param int                                        $layoutPosition       布局位置
     * @param array                                      $orderedDataBlockList 数据块Model列表
     *
     * @throws \yii\base\Exception
     */
    private function syncGoodsManagePageLangUiComponents($siteCode, $activityPageId, $goodsManageLayout, $layoutPosition, $orderedDataBlockList)
    {
        $device = $this->getDeviceCodeBySiteCode($siteCode);
        if (empty($device) || !isset($this->uiComponentKey[ $device ])) {
            throw new Exception(sprintf('站点%s简码无效', $siteCode));
        }

        //当前站点的UI组件key
        $componentKey = $this->uiComponentKey[ $device ];
        /** @var \app\modules\component\models\UiModel $uiModel */
        $uiModel = UiModel::find()->where(['component_key' => $componentKey])->one();
        if (!$uiModel) {
            throw new Exception('组件无效');
        }
        $defaultTplId = $uiModel->tpl_id;

        //获取nomore模板数据
//        $noMoreUiTpl = UiTplModel::find()
//            ->where(['component_key' => $componentKey, 'name_en' => static::COMPONENT_NOMORE_TPL_NAME])
//            ->asArray()
//            ->one();
//        if (empty($noMoreUiTpl)) {
//            throw new Exception(sprintf('无法获取商品标题组合组件模板[%s]数据', static::COMPONENT_NOMORE_TPL_NAME));
//        }
//        $noMoreTplId = $noMoreUiTpl['id'];

        //获取组件模板字段配置
        $componentFields = PageUiComponentDataComponent::getConfig($defaultTplId, 'fields');
        if (empty($componentFields)) {
            throw new Exception('无法获取组件配置');
        }

        //获取当前已有的组件
        $existsUiComponents = $this->getOrderedGoodsManagePageLangUiList($goodsManageLayout->id, $layoutPosition, $componentKey);
        $dataBlockNo = count($orderedDataBlockList);
        $existsComponentNo = count($existsUiComponents);

        //同步UI组件
        if ($existsComponentNo !== $dataBlockNo) {
            if ($existsComponentNo < $dataBlockNo) {

                //创建组件
                $nextId = CommonCommonComponent::NEXT_ID_TEMP;
                for ($i = 0; $i < ($dataBlockNo - $existsComponentNo); $i++) {
                    $componentData = [
                        'component_key' => $componentKey,
                        'lang'          => $goodsManageLayout->lang,
                        'layout_id'     => $goodsManageLayout->id,
                        'next_id'       => $nextId--,
                        'position'      => $layoutPosition,
                        'tpl_id'        => $defaultTplId
                    ];
                    $existsUiComponents[] = $this->appendGoodsManageUiToLayout($componentData);
                }

                // 修改组件顺序
                for ($i = 0; $i < count($existsUiComponents); $i++) {
                    $nextIndexId = $i + 1;
                    $oldNextId = (int) $existsUiComponents[ $i ]['next_id'];
                    $existsUiComponents[ $i ]['next_id'] = isset($existsUiComponents[ $nextIndexId ])
                        ? $existsUiComponents[ $nextIndexId ]['id'] : self::COMPONENT_LAST_NODE_NEXT_ID;

                    if ($oldNextId != (int) ($existsUiComponents[ $i ]['next_id'])) {
                        $updateData = ['next_id' => $existsUiComponents[ $i ]['next_id']];
                        $conditions = [
                            'id'        => $existsUiComponents[ $i ]['id'],
                            'layout_id' => $existsUiComponents[ $i ]['layout_id']
                        ];
                        if (!PageUiModel::updateAll($updateData, $conditions)) {
                            throw new Exception('商品管理UI组件更新失败');
                        }
                    }
                }
            } else {
                //删除多余组件
                for ($i = 0; $i < ($existsComponentNo - $dataBlockNo); $i++) {
                    $componentData = array_pop($existsUiComponents);
                    $this->deleteGoodsManageUi($componentData);
                }

                //修改最后一个组件next_id
                $lastIndexId = $dataBlockNo - 1;
                $existsUiComponents[ $lastIndexId ]['next_id'] = self::COMPONENT_LAST_NODE_NEXT_ID;

                $updateData = ['next_id' => $existsUiComponents[ $lastIndexId ]['next_id']];
                $conditions = [
                    'id'        => $existsUiComponents[ $lastIndexId ]['id'],
                    'layout_id' => $existsUiComponents[ $lastIndexId ]['layout_id']
                ];
                if (!PageUiModel::updateAll($updateData, $conditions)) {
                    throw new Exception('商品管理UI组件顺序更新失败');
                }
            }
        }

        //同步组件数据
        $index = 0;
        /** @var \app\modules\common\models\GoodsManageDataBlockModel $dataBlockList */
        foreach ($orderedDataBlockList as $dataBlockList) {
            $curUiComponent = $existsUiComponents[ $index ];
            $componentId = $curUiComponent['id'];
            $toSetTplId = (int) ($curUiComponent['tpl_id']);

            //根据数据块more_url选择模板
//            $toSetTplId = empty($dataBlockList['more_url']) ? (int)$noMoreTplId : (int)$defaultTplId;
//            if ($toSetTplId != (int)($curUiComponent['tpl_id'])) {
//                $updateData = ['tpl_id'  => $toSetTplId];
//                $conditions = [
//                    'id'        => $curUiComponent['id'],
//                    'layout_id' => $curUiComponent['layout_id']
//                ];
//                if (!PageUiModel::updateAll($updateData, $conditions)) {
//                    throw new Exception('商品管理UI组件模板更新失败');
//                }
//            }

            // 如果商品SKU来源选品系统
            $skuList = $dataBlockList->goods_sku;
            if (SiteConstants::GOODS_SKU_FROM_IPS === (int) $dataBlockList->sku_from) {
                $ipsInfo = json_decode($dataBlockList->goods_sku, true);
                $ipsComponent = new IpsComponent();
                $skuList = $ipsComponent->getSkuListAndRelatedGoodsListUiComponent(
                    $ipsInfo['gsSelectLevel2']['value'], $activityPageId, $curUiComponent['lang'],
                    $componentId, $curUiComponent['component_key']
                );
            }

            //保存配置数据
            $uiComponentDataList = [
                ['key' => $this->dataBlockNameToComponentFieldMap['category_title'], 'value' => $dataBlockList->category_title],
                ['key' => $this->dataBlockNameToComponentFieldMap['more_url'], 'value' => $dataBlockList->more_url],
                ['key' => $this->dataBlockNameToComponentFieldMap['goods_sku'], 'value' => $skuList]
            ];

            foreach ($uiComponentDataList as $key => $uiComponentData) {
                if (!isset($componentFields[ $uiComponentData['key'] ])) {
                    throw new Exception(sprintf('商品管理UI组件字段[%s]不存在', $uiComponentData['key']));
                }

                $uiComponentDataList[ $key ] = array_merge($uiComponentData, $componentFields[ $uiComponentData['key'] ]);
            }

            //保存数据块数据到组件
            $this->savePageUiComponentData($componentId, $dataBlockList->lang, $toSetTplId, $uiComponentDataList);

            //关联数据块和组件
            $dataBlockList->component_id = $componentId;
            if (!$dataBlockList->update(false)) {
                throw new Exception('关联数据块和组件失败');
            }

            $index++;
        }

    }

    /**
     * 保存页面商品管理UI组件数据
     *
     * @param int    $componentId UI组件ID
     * @param string $lang        语言简码
     * @param int    $tplId       模板ID
     * @param array  $dataList    Ui组件数据列表
     *
     * @throws \yii\base\Exception
     */
    private function savePageUiComponentData($componentId, $lang, $tplId, $dataList)
    {
        $dataKeys = array_column($dataList, 'key');
        $conditions = [
            'component_id' => $componentId,
            'lang'         => $lang,
            'is_public'    => PageUiComponentDataModel::IS_PUBLIC,
            'key'          => $dataKeys
        ];

        //查询已存在的数据is_public的数据
        $dataModelMap = PageUiComponentDataModel::find()->where($conditions)->indexBy('key')->all();
        foreach ($dataList as $data) {

            //value值需要json编码
            $data['value'] = json_encode($data['value']);
            if (isset($dataModelMap[ $data['key'] ])) {
                /** @var \app\modules\common\models\PageUiComponentDataModel $dataModel */
                $dataModel = $dataModelMap[ $data['key'] ];
                if (0 !== strcmp($dataModel->value, $data['value'])) {
                    $dataModel->value = $data['value'];
                    if (!$dataModel->update(false)) {
                        throw new Exception('UI组件数据更新失败');
                    }
                }
            } else {
                $data['component_id'] = $componentId;
                $data['lang'] = $lang;
                $data['tpl_id'] = $tplId;

                $dataModel = new PageUiComponentDataModel();
                $dataModel->setAttributes($data, false);
                if (!$dataModel->insert(false)) {
                    throw new Exception('UI组件数据插入失败');
                }
            }
        }

    }

    /**
     * 获取商品管理布局下的所有商品UI组件
     *
     * @param int    $layoutId       布局组件ID
     * @param int    $position       布局位置
     * @param string $uiComponentKey UI组件key
     *
     * @return array 排序后的商品管理UI组件列表
     */
    private function getOrderedGoodsManagePageLangUiList($layoutId, $position, $uiComponentKey)
    {
        $uiList = PageUiModel::find()
            ->where([
                'layout_id' => $layoutId,
                'position'  => $position
            ])->asArray()->all();
        if (!empty($uiList)) {
            $orderUiList = $this->getOrderedComponents($uiList);
            $orderUiList = array_filter($orderUiList, function ($item) use ($uiComponentKey) {
                return isset($item['component_key'])
                    && ($item['component_key'] === $uiComponentKey);
            });

            return array_values($orderUiList);
        }

        return [];
    }

    /**
     * 同步商品管理页单语言面布局组件
     *
     * @param string $siteCode       站点简码
     * @param int    $activityPageId 活动页面ID
     * @param string $lang           语言简码
     *
     * @return array 0:\app\modules\common\models\PageLayoutModel; 1:layoutPosition
     * @throws \yii\base\Exception
     */
    private function syncGoodsManagePageLangLayoutComponent($siteCode, $activityPageId, $lang)
    {
        $device = $this->getDeviceCodeBySiteCode($siteCode);
        if (empty($device) || !isset($this->defaultLayoutComponentKey[ $device ])) {
            throw new Exception(sprintf('站点%s简码无效', $siteCode));
        }

        //当前站点的布局组件key
        $defaultComponentKey = $this->defaultLayoutComponentKey[ $device ];
        $uiComponentKey = $this->uiComponentKey[ $device ];

        //查询页面所有布局
        $pageLayoutMap = PageLayoutModel::find()
            ->where(['page_id' => $activityPageId, 'lang' => $lang])
            ->indexBy('id')
            ->all();
        if (!empty($pageLayoutMap)) {
            $layoutIds = array_keys($pageLayoutMap);
            /** @var \app\modules\common\models\PageUiModel $pageUiModel */
            $pageUiModel = PageUiModel::find()
                ->where(['layout_id' => $layoutIds, 'lang' => $lang, 'component_key' => $uiComponentKey])
                ->one();
            if ($pageUiModel) {
                return [$pageLayoutMap[ $pageUiModel->layout_id ], $pageUiModel->position];
            }
        }

        //没有使用商品管理Layout，这页面最后自动生成
        /** @var \app\modules\common\models\PageLayoutModel $goodsManageLayout */
        $goodsManageLayout = $this->appendGoodsManagePageLangLayout($activityPageId, $lang, $defaultComponentKey);

        return [$goodsManageLayout, self::GOODS_MANAGE_UI_DEFAULT_POSITION];
    }

    /**
     * 在页面布局最后生成商品管理布局组件
     *
     * @param int    $activityPageId 活动页面ID
     * @param string $lang           语言简码
     * @param string $componentKey   组件key
     *
     * @return \app\modules\common\models\PageLayoutModel
     * @throws \yii\base\Exception
     */
    private function appendGoodsManagePageLangLayout($activityPageId, $lang, $componentKey)
    {
        //生成布局组件
        $layoutModel = new PageLayoutModel();
        $layoutModel->page_id = $activityPageId;
        $layoutModel->lang = $lang;
        $layoutModel->component_key = $componentKey;
        $layoutModel->next_id = CommonCommonComponent::NEXT_ID_TEMP; //将布局组件添加到最后
        if (!$layoutModel->insert(true)) {
            yii::error(sprintf('布局添加失败:%s', $layoutModel->flattenErrors(',')), __METHOD__);
            throw new Exception('布局添加失败');
        }

        //更新布局父子关系
        /** @var \app\modules\common\models\PageLayoutModel $lastLayoutModel */
        $lastLayoutModel = PageLayoutModel::find()
            ->where([
                'page_id' => $activityPageId,
                'lang'    => $lang,
                'next_id' => self::COMPONENT_LAST_NODE_NEXT_ID
            ])->one();
        if ($lastLayoutModel) {
            $lastLayoutModel->next_id = $layoutModel->id;
            if (!$lastLayoutModel->update(false)) {
                yii::error(sprintf('更新布局父ID失败:%s', $lastLayoutModel->flattenErrors(',')), __METHOD__);
                throw new Exception('更新布局父ID失败');
            }
        }

        $layoutModel->next_id = self::COMPONENT_LAST_NODE_NEXT_ID;
        if (!$layoutModel->update(false)) {
            yii::error(sprintf('更新布局子ID失败:%s', $layoutModel->flattenErrors(',')), __METHOD__);
            throw new Exception('更新布局子ID失败');
        }

        //添加布局属性
        $pageLayoutDataModel = new PageLayoutDataModel();
        $pageLayoutDataModel->component_id = $layoutModel->id;
        $pageLayoutDataModel->lang = $lang;
        $pageLayoutDataModel->data = json_encode([]);
        if (!$pageLayoutDataModel->insert(true)) {
            yii::error(sprintf('布局属性添加失败:%s', $pageLayoutDataModel->flattenErrors(',')), __METHOD__);
            throw new Exception('布局属性添加失败');
        }

        return $layoutModel;
    }

    /**
     * 根据数据块生成Model对象
     *
     * @param int    $gmpId
     * @param string $lang
     * @param int    $sortNum
     * @param array  $dataBlock
     *
     * @return \app\modules\common\models\GoodsManageDataBlockModel
     */
    private function getPageDataBlockModel($gmpId, $lang, $sortNum, $dataBlock)
    {
        $skuValue = trim($dataBlock['goods_sku']);
        $skuFrom = (int) $dataBlock['sku_from'];
        if (SiteConstants::GOODS_SKU_FROM_IPS === $skuFrom) {
            $skuValue = json_encode($dataBlock['ips']);
        }

        $dataBlockModel = new GoodsManageDataBlockModel();
        $dataBlockModel->gmp_id = $gmpId;
        $dataBlockModel->lang = $lang;
        $dataBlockModel->category_title = trim($dataBlock['category_title']);
        $dataBlockModel->more_url = trim($dataBlock['more_url']);
        $dataBlockModel->sku_from = $skuFrom;
        $dataBlockModel->goods_sku = $skuValue;
        $dataBlockModel->sort_num = $sortNum;
        $dataBlockModel->is_same = $dataBlock['is_same'] ?? 0;
        $dataBlockModel->data_md5 = $this->getDataSign($dataBlockModel);
        $dataBlockModel->component_id = 0; //默认

        return $dataBlockModel;
    }

    /**
     * 添加单个页面里的数据块
     *
     * @param string $groupId  分组ID
     * @param array  $pageData 页面数据,格式：
     *
     * - site_code                      站点代码
     * - page_id                        活动页面ID
     * - lang_list                      多语言列表
     * - lang_list.en                   当前语言数据块列表
     * - lang_list.en.category_title    商品分类的标题名称
     * - lang_list.en.more_url          view more 链接
     * - lang_list.en.goods_sku         商品sku列表，多个用英文逗号分隔
     *
     * @throws \yii\base\Exception
     */
    private function addGoodsMangePageDataBlocks($groupId, $pageData)
    {
        $this->checkPageDataIntegrity($pageData);

        if (empty($pageData['page_id'])) {
            throw new Exception('活动子页面没有选择');
        }

        /** @var \app\modules\common\models\PageModel $pageModel */
        $pageModel = PageModel::find()->alias('p')->select('p.*, l.title')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id=p.id')
            ->where(['p.id' => $pageData['page_id']])
            ->one();

        if (!$pageModel) {
            throw new Exception('活动子页面不存在');
        }

        $activityPageId = $pageModel->id;
        $activityPageTitle = $pageModel->title;

        /** @var \app\modules\common\models\ActivityModel $activityModel */
        //判断子页面对应的活动是本人加锁
//        $activityModel = ActivityModel::find()->where(['id' => $pageModel->activity_id])->one();
//        if (($activityModel->is_lock == ActivityModel::IS_LOCK)  && (app()->user->username != $pageModel->create_user)) {
//            throw new Exception(sprintf('活动子页面[%s]已锁定，不能被管理', $activityPageTitle));
//        }

        //判断页面是否已被管理
        $managePageInfo = GoodsManagePageModel::find()->alias('p')
            ->select('p.*, l.title')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id=p.page_id')
            ->where(['p.site_code' => $pageData['site_code'], 'p.page_id' => $activityPageId])
            ->asArray()
            ->one();
        if (!empty($managePageInfo)) {
            throw new Exception(sprintf('活动子页面[%s]已经被管理', $activityPageTitle));
        }

        //保存页面数据
        $goodsManagePageModel = new GoodsManagePageModel();
        $goodsManagePageModel->group_id = $groupId;
        $goodsManagePageModel->site_code = $pageData['site_code'];
        $goodsManagePageModel->activity_id = $pageModel->activity_id;
        $goodsManagePageModel->page_id = $activityPageId;
        if (!$goodsManagePageModel->save(true)) {
            yii::error(sprintf('页面%d数据保存失败:%s', $activityPageId, $goodsManagePageModel->flattenErrors(',')), __METHOD__);
            throw new Exception(sprintf('页面%d数据保存失败', $activityPageId));
        }

        //页面支持的语言
        $supportLanguages = $this->getPageLanguageList($activityPageId);

        //保存配置数据
        foreach ($pageData['lang_list'] as $lang => $pageLangDataBlockList) {
            $pageLangDataInfo = [
                'site_code'         => $pageData['site_code'],
                'page_id'           => $pageData['page_id'],
                'support_languages' => $supportLanguages,
                'lang'              => $lang
            ];

            $this->addGoodsMangePageLangDataBlocks($goodsManagePageModel, $pageLangDataInfo, $pageLangDataBlockList);
        }
    }

    /**
     * 添加单个页面里单个语言的数据块
     *
     * @param \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel
     * @param array                                           $pageLangDataInfo      页面数据,格式参考 updateGoodsMangePageDataBlocks 方法
     * @param array                                           $pageLangDataBlockList 新页面单个语言数据块列表
     *
     * @throws \yii\base\Exception
     */
    private function addGoodsMangePageLangDataBlocks($goodsManagePageModel, $pageLangDataInfo, $pageLangDataBlockList)
    {
        $lang = $pageLangDataInfo['lang'];
        $activityPageId = $pageLangDataInfo['page_id'];
        if (empty($pageLangDataBlockList)) {
            $langName = app()->params['lang'][ $lang ]['name'];
            throw new Exception(sprintf('语言[%s]下商品列表不能为空', $langName));
        }

        //保存商品管理页面所有数据块
        $index = 0;
        $pageDataBlockModelList = [];
        foreach ($pageLangDataBlockList as $langDataBlock) {
            $this->checkPageDataBlockIntegrity($langDataBlock);

            $pageDataBlockModel = $this->getPageDataBlockModel($goodsManagePageModel->id, $lang, ($index + 1), $langDataBlock);
            if (!$pageDataBlockModel->insert(false)) {
                throw new Exception(sprintf('页面[%d]下语言[%s]商品列表插入失败', $activityPageId, $lang));
            }
            $pageDataBlockModelList[] = $pageDataBlockModel;
            $index++;
        }

        $supportLanguageCodes = array_column(ArrayHelper::toArray($pageLangDataInfo['support_languages']), 'lang');
        //判断当前语言页面是否支持
        if (in_array($lang, $supportLanguageCodes)) {
            $this->syncGoodsMangePageLangDataBlockComponents($pageLangDataInfo, $pageDataBlockModelList);
        }
    }

    /**
     * 获得活动页面支持语言列表
     *
     * @param int $activityPageId 活动页面ID
     *
     * @return array
     */
    private function getPageLanguageList($activityPageId)
    {
        return PageLanguageModel::find()->where(['page_id' => $activityPageId])->all();
    }

    /**
     * 获取活动管理分组下所有端预览URL
     *
     * @param string $groupId
     *
     * @return array
     */
    private function getGroupPreviewUrl($groupId)
    {
        //获取group下已经选择的活动页面
        $groupPageList = GoodsManagePageModel::find()->alias('m')
            ->select(['m.*', 'p.pid', 'group_concat(l.lang) as langString'])
            ->leftJoin(PageModel::tableName() . ' as p', 'p.id=m.page_id')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id = p.id')
            ->where(['group_id' => $groupId])
            ->groupBy('m.id')
            ->asArray()
            ->all();

        //组装数据
        $result = [];
        if (!empty($groupPageList)) {
            foreach ($groupPageList as $groupPageInfo) {
                $langArr = explode(',', $groupPageInfo['langString']);
                $device = $this->getDeviceCodeBySiteCode($groupPageInfo['site_code']);
                $previewUrl = Url::to([
                    '/activity/design/preview',
                    'pid'  => $groupPageInfo['pid'],
                    'lang' => !empty($langArr[0]) ? $langArr[0] : ''
                ], true);

                $result[ $device ] = $previewUrl;
            }
        }

        return $result;
    }

    /**
     * 删除商品管理UI组件及模板数据
     *
     * @param array $componentData
     *
     * @throws \yii\base\Exception
     */
    private function deleteGoodsManageUi($componentData)
    {
        //删除组件数据
        $conditions = [
            'component_id' => $componentData['id'],
            'lang'         => $componentData['lang']
        ];
        PageUiComponentDataModel::deleteAll($conditions);

        //删除组件
        $conditions = ['id' => $componentData['id'], 'component_key' => $componentData['component_key']];
        if (!PageUiModel::deleteAll($conditions)) {
            throw new Exception('删除商品管理UI组件失败');
        }
    }

    /**
     * 追加商品管理UI组件到商品管理布局
     *
     * @param array $data 组件数据
     *
     * @return array
     * @throws \yii\base\Exception
     */
    private function appendGoodsManageUiToLayout($data)
    {
        $uiModel = new PageUiModel();
        $uiModel->load($data, '');
        if (!$uiModel->insert(false)) {
            yii::error(sprintf('商品管理UI组件添加失败:%s', $uiModel->flattenErrors(',')), __METHOD__);
            throw new Exception('商品管理UI组件添加失败');
        }

        return ArrayHelper::toArray($uiModel);
    }

    /**
     * 获取数据MD5签名
     *
     * @param \app\modules\common\models\GoodsManageDataBlockModel $model
     *
     * @return string md5
     */
    private function getDataSign($model)
    {
        return md5(
            $model->category_title . $model->more_url . $model->goods_sku
            . $model->sort_num . $model->is_same . $model->sku_from
        );
    }

    /**
     * 删除商品管理页面
     *
     * @param \app\modules\common\models\GoodsManagePageModel $goodsManagePageModel
     *
     * @throws \yii\base\Exception
     */
    private function delGoodsManagePageAndDataBlocks($goodsManagePageModel)
    {
        $conditions = ['gmp_id' => $goodsManagePageModel->id];
        if (!GoodsManageDataBlockModel::deleteAll($conditions)) {
            throw new Exception('删除商品管理数据块失败');
        }

        if (!$goodsManagePageModel->delete()) {
            throw new Exception('删除商品管理组页面失败');
        }
    }

    /**
     * 判断字符串是否已指定字符串结束
     *
     * @param string $text   要检查的字符串
     * @param string $needle 要搜索的字符串
     *
     * @return bool 匹配true；否则false
     */
    private function endsWith($text, $needle)
    {
        return 0 === substr_compare($text, $needle, -strlen($needle));
    }

    /**
     * 根据站点简码获得设备简码
     *
     * @param string $siteCode 站点简码
     *
     * @return string 设备简码
     */
    private function getDeviceCodeBySiteCode($siteCode)
    {
        return SitePlatform::getPlatformCodeBySiteCode($siteCode);
    }

    /**
     * 覆盖 CommonPublishTrait 的 getPageLayoutAndUiByPageId 方法，实现没有保存的组件的情况下
     * 能预览页面配置的商品
     *
     * @See \app\modules\common\zf\traits\CommonPublishTrait::getPageLayoutAndUiByPageId
     * @deprecated 没有使用，但不删除，以便以后使用
     */
    private function getPageLayoutAndUiByPageId($pageId, $lang)
    {
        $uiListByLayoutPosition = [];
        $uiKeyList = [];

        $layoutId = 0;
        $layoutPosition = 0;

        $jsonContent = app()->request->post('content');
        $pageInfo = json_decode($jsonContent, true);
        $device = $this->getDeviceCodeBySiteCode($pageInfo['site_code']);
        $uiComponentKey = $this->uiComponentKey[ $device ];

        //获取链接好的layout
        $orderedLayouts = $this->getOrderedComponents($this->getPageLayoutByPageId($pageId, $lang));
        if ($orderedLayouts) {
            //一次获取所有相关的UI组件，后面再进行拼接
            $uiList = $this->getPageUIByLayoutIds(array_column($orderedLayouts, 'id'), $lang);
            if ($uiList) {
                //查找是否有商品管理UI组件
                foreach ($uiList as $_ui) {
                    if (0 === strcmp($_ui['component_key'], $uiComponentKey)) {
                        $layoutId = (int) $_ui['layout_id'];
                        $layoutPosition = (int) $_ui['position'];
                        break;
                    }
                }

                //按layout_id分好组的ui列表，并获取去重后的UI组件的key
                array_map(function ($value) use (&$uiListByLayoutPosition, &$uiKeyList) {
                    $uiListByLayoutPosition[ $value['layout_id'] ][ $value['position'] ][] = $value;
                    $keyAndTplId = $value['component_key'] . $this->delimiterInKeyAndTplId . ($value['tpl_id'] ?? 0);
                    if (!\in_array($keyAndTplId, $uiKeyList, true)) {
                        $uiKeyList[] = $keyAndTplId;
                    }
                }, $uiList);
            }
        }

        //构建虚拟布局和组件数据
        if ($layoutId > 0) {
            $uiList = $uiListByLayoutPosition[ $layoutId ][ $layoutPosition ];
            $newUiList = $this->pagePreviewUpdateDataBlockUiComponents($pageInfo, $layoutId, $layoutPosition, $uiList);
            $uiListByLayoutPosition[ $layoutId ][ $layoutPosition ] = $newUiList;
        } else {
            list($layout, $layoutPositionUiList) = $this->pagePreviewAddLayoutAndUiComponents($pageInfo);
            $orderedLayouts[] = $layout;
            $uiListByLayoutPosition[ $layout['id'] ] = $layoutPositionUiList;
        }

        return [$orderedLayouts, $uiListByLayoutPosition, $uiKeyList];
    }

    /**
     * 同过商品管理页面配置数据，更新方式构建虚拟布局和组件数据
     *
     * @param array $pageInfo       页面配置数据，格式如下：
     *                              - site_code                      站点代码
     *                              - page_id                        活动页面ID
     *                              - lang                           语言简码
     *                              - block_list                     当前语言数据块列表
     *                              - block_list.category_title      商品分类的标题名称
     *                              - block_list.more_url            view more 链接
     *                              - block_list.goods_sku           商品sku列表，多个用英文逗号分隔
     *
     * @param int   $layoutId       布局ID
     * @param int   $layoutPosition 布局栏位置
     * @param array $uiList         已有UI组件列表
     *
     * @return array 新的UI组件列表
     * @deprecated 没有使用，但不删除，以便以后使用
     */
    private function pagePreviewUpdateDataBlockUiComponents($pageInfo, $layoutId, $layoutPosition, $uiList)
    {
        $device = $this->getDeviceCodeBySiteCode($pageInfo['site_code']);
        $uiComponentKey = $this->uiComponentKey[ $device ];
        $orderedUiList = $this->getOrderedComponents($uiList);
        $maxIndex = empty($orderedUiList) ? 0 : count($orderedUiList);
        $dataBlockNames = ['category_title', 'more_url', 'goods_sku'];
        $index = 0;
        $uiId = 1;

        foreach ($pageInfo['block_list'] as $dataBlock) {
            $uiData = [];
            foreach ($dataBlockNames as $name) {
                !empty($dataBlock[ $name ])
                && $uiData[ $this->dataBlockNameToComponentFieldMap[ $name ] ] = $dataBlock[ $name ];
            }

            if (isset($orderedUiList[ $index ])) {
                if ((0 === strcmp($orderedUiList[ $index ]['component_key'], $uiComponentKey))) {
                    $_data = json_decode($orderedUiList[ $index ]['data'], true);
                    $_data = array_merge($_data, $uiData);
                    $orderedUiList[ $index ]['data'] = empty($_data) ? '{}' : json_encode($_data);
                }
            } else {
                $orderedUiList[] = [
                    'id'            => $uiId,
                    'component_key' => $uiComponentKey,
                    'layout_id'     => $layoutId,
                    'next_id'       => ($uiId + 1),
                    'position'      => $layoutPosition,
                    'tpl_id'        => 0,
                    'need_navigate' => 0,
                    'lang'          => $pageInfo['lang'],
                    'data'          => empty($uiData) ? '{}' : json_encode($uiData)
                ];
            }

            $index++;
        }

        if ($maxIndex > 0) {
            $lastSrcIndex = $maxIndex - 1;
            if ((int) $orderedUiList[ $lastSrcIndex ]['next_id'] === self::COMPONENT_LAST_NODE_NEXT_ID) {
                $orderedUiList[ $lastSrcIndex ]['next_id'] = $uiId;
            }
        }

        //删除多余组件
        if ($index < $maxIndex) {
            $orderedUiList = array_slice($orderedUiList, 0, ($index - 1));
        }

        if (!empty($orderedUiList)) {
            $lastIndex = count($orderedUiList) - 1;
            if ((int) $orderedUiList[ $lastIndex ]['next_id'] !== self::COMPONENT_LAST_NODE_NEXT_ID) {
                $orderedUiList[ $lastIndex ]['next_id'] = self::COMPONENT_LAST_NODE_NEXT_ID;
            }
        }

        return $orderedUiList;
    }

    /**
     * 同过商品管理页面配置数据，追加方式构建虚拟布局和组件数据
     *
     * @param array $pageInfo 页面配置数据，格式如下：
     *                        - site_code                      站点代码
     *                        - page_id                        活动页面ID
     *                        - lang                           语言简码
     *                        - block_list                     当前语言数据块列表
     *                        - block_list.category_title      商品分类的标题名称
     *                        - block_list.more_url            view more 链接
     *                        - block_list.goods_sku           商品sku列表，多个用英文逗号分隔
     *
     * @return array
     * @deprecated 没有使用，但不删除，以便以后使用
     */
    private function pagePreviewAddLayoutAndUiComponents($pageInfo)
    {
        $layoutId = 1;
        $layoutPosition = 1;
        $device = $this->getDeviceCodeBySiteCode($pageInfo['site_code']);
        $layoutComponentKey = $this->defaultLayoutComponentKey[ $device ];
        $uiComponentKey = $this->uiComponentKey[ $device ];

        $layout = [
            'id'               => $layoutId,
            'lang'             => $pageInfo['lang'],
            'page_id'          => $pageInfo['page_id'],
            'component_key'    => $layoutComponentKey,
            'next_id'          => self::COMPONENT_LAST_NODE_NEXT_ID,
            'data'             => '[]',
            'custom_css'       => '',
            'background_color' => '',
            'background_img'   => '',
            'site_code'        => $pageInfo['site_code']
        ];

        $uiList = [];
        $uiId = 1;
        $dataBlockNames = ['category_title', 'more_url', 'goods_sku'];
        foreach ($pageInfo['block_list'] as $dataBlock) {
            $uiData = [];
            foreach ($dataBlockNames as $name) {
                !empty($dataBlock[ $name ])
                && $uiData[ $this->dataBlockNameToComponentFieldMap[ $name ] ] = $dataBlock[ $name ];
            }

            $uiList[] = [
                'id'            => $uiId,
                'component_key' => $uiComponentKey,
                'layout_id'     => $layoutId,
                'next_id'       => ($uiId + 1),
                'position'      => $layoutPosition,
                'tpl_id'        => 0,
                'need_navigate' => 0,
                'lang'          => $pageInfo['lang'],
                'data'          => empty($uiData) ? '{}' : json_encode($uiData)
            ];

            $uiId++;
        }

        $uiList[ ($uiId - 2) ]['next_id'] = self::COMPONENT_LAST_NODE_NEXT_ID;
        $layoutPositionUiList = [$layoutPosition => $uiList];

        return [$layout, $layoutPositionUiList];
    }

    /**
     * 删除商品管理数据
     *
     * @param integer $id
     *
     * @return array
     * @throws Exception
     */
    public function deleteGoodsManage(int $id)
    {
        $pageInfo = GoodsManagePageModel::getById($id);
        if (!$pageInfo) {
            return app()->helper->arrayResult($this->codeFail, '商品数据不存在', []);
        }

        try {
            $component = GoodsManageDataBlockModel::find()->select('component_id, lang')
                ->where(['gmp_id' => $pageInfo->id])->asArray()->all();
            if (!empty($component) && is_array($component)) {
                $this->doDeleteGoodsManage($component, $pageInfo->page_id);
            }
            GoodsManageDataBlockModel::deleteAll("gmp_id = {$pageInfo->id}");
            $pageInfo->delete();

            return app()->helper->arrayResult(0, 'success', '删除成功');
        } catch (Exception $exception) {
            return app()->helper->arrayResult($this->codeFail, '删除失败', $exception->getMessage());
        }
    }

    /**
     * 执行删除商品管理数据
     *
     * @param array $component
     * @param int   $pageId
     */
    private function doDeleteGoodsManage(array $component, int $pageId)
    {
        $componentId = array_column($component, 'component_id');
        $layout = PageUiModel::find()->select('layout_id, lang')->where(['in', 'id', $componentId])
            ->asArray()->all();

        foreach ($component as $item) {
            $uiModel = PageUiModel::findOne($item['component_id']);
            if ($uiModel && $uiModel['lang'] === $item['lang']) {
                (new CommonPageUiDesignComponent())->deleteUIComponent(
                    ['page_id' => $pageId, 'id' => $item['component_id'], 'lang' => $item['lang']]
                );
            }
        }
        if (!empty($layout) && is_array($layout)) {
            foreach ($layout as $value) {
                $layoutModel = PageLayoutModel::findOne($value['layout_id']);
                if ($layoutModel && $layoutModel->lang === $value['lang']) {
                    (new CommonPageLayoutDesignComponent())->deleteLayoutComponent(
                        ['page_id' => $pageId, 'id' => $value['layout_id'], 'lang' => $value['lang']]
                    );
                }
            }
        }
    }
}
