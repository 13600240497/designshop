<?php

namespace app\modules\base\components;

use yii\helpers\ArrayHelper;
use app\base\Pagination;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use app\base\SiteConstants;
use ego\base\JsonResponseException;
use app\modules\common\models\PageModel;
use app\modules\common\models\PageLanguageModel;
use app\modules\common\models\PageUiModel;
use app\modules\common\zf\models\PageModel as ZfPageModel;
use app\modules\common\zf\models\PageLanguageModel as ZfPageLanguageModel;
use app\modules\common\zf\models\PageUiModel as ZfPageUiModel;
use app\modules\base\models\SubActivityDataModel;
use app\modules\base\models\IndexActivityDataModel;

/**
 * 统计数据管理组件
 * @package app\modules\activity\components
 */
class ActivityDataComponent extends Component
{
    //站点简称对应转化数组
    private $siteCode = ['zf' => 'zaful', 'rg' => 'rosegal', 'rw' => 'rosewholesale'];

    //默认所有终端
    private $platform = 'all';

    //默认为所有访客
    private $isNew = 2;

    /**
     * 获取首页活动子页面列表
     *
     * @param array $params http请求参数
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getHomePageList($params)
    {
        $websiteCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($websiteCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        //站点简码
        $platform = $params['platform'] ?? 'all';
        $siteCode = NULL;
        if ('all' == $platform) {
            $siteCode = [$this->getSiteCode('pc', $websiteCode), $this->getSiteCode('m', $websiteCode)];
        } else {
            $siteCode = $this->getSiteCode($platform, $websiteCode);
        }

        if (empty($siteCode)) {
            throw new JsonResponseException($this->codeFail, '无效的参数platform');
        }

        $condition = [
            'p.site_code' => $siteCode,
            'p.is_delete' => PageModel::NOT_DELETE,
            'p.activity_id' => SiteConstants::HOME_PAGE_ACTIVITY_ID,
        ];

        //查询子页面列表
        list($defaultPage, $pageList) = $this->getPageList($websiteCode, $condition);
        $pageInfoList = [];
        if (!empty($pageList)) {
            $pageInfoList = ArrayHelper::toArray($pageList);
            if (isZufulSite($websiteCode)) {
                $configPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($websiteCode);
                foreach ($pageInfoList as &$_pageInfo) {
                    $_pipelineName = $configPipelineList[ $_pageInfo['pipeline'] ] ?? $_pageInfo['pipeline'];
                    $_pageInfo['title'] = $_pipelineName .' - '. $_pageInfo['title'];
                    unset($_pageInfo['pipeline']);
                }
            }
        }

        $result = [
            'default' => empty($defaultPage) ? [] : ArrayHelper::toArray($defaultPage),
            'list' => $pageInfoList,
        ];
        return app()->helper->arrayResult($this->codeSuccess, 'success', $result);
    }

    /**
     * 根据首页活动子页面ID查询组件列表
     *
     * @param array $params
     * @return array
     * @throws JsonResponseException
     */
    public function getHomePageComponentList($params)
    {
        $dataModel = new IndexActivityDataModel();
        return $this->getPageComponentList($dataModel, $params);
    }

    /**
     * 获取首页活动子页面总体数据列表
     *
     * @param array $params http请求参数
     * @return array
     * @throws JsonResponseException
     */
    public function getHomePageTotalDataList($params)
    {
        $dataModel = new IndexActivityDataModel();
        return $this->getPageTotalDataList($dataModel, $params);
    }

    /**
     * 获取首页活动子页面组件或坑位数据列表
     *
     * @param array $params http请求参数
     * @return array
     * @throws JsonResponseException
     */
    public function getHomePageDetailDataList($params)
    {
        $dataModel = new IndexActivityDataModel();
        return $this->getPageDetailDataList($dataModel, $params);
    }

    /**
     * 获取专题活动子页面列表
     *
     * @param array $params http请求参数
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getSpecialPageList($params)
    {
        $websiteCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($websiteCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        //站点简码
        $platform = $params['platform'] ?? 'all';
        $siteCode = NULL;
        if ('all' == $platform) {
            $siteCode = [$this->getSiteCode('pc', $websiteCode), $this->getSiteCode('m', $websiteCode)];
        } else {
            $siteCode = $this->getSiteCode($platform, $websiteCode);
        }

        if (empty($siteCode)) {
            throw new JsonResponseException($this->codeFail, '无效的参数platform');
        }

        $condition = [
            'p.is_delete' => PageModel::NOT_DELETE,
            'p.site_code' => $siteCode
        ];

        //查询子页面列表
        $keyword = $params['keyword'] ?? NULL;
        //查询子页面列表
        list($defaultPage, $pageList) = $this->getPageList($websiteCode, $condition, $keyword, 20);

        $pageInfoList = [];
        if (!empty($pageList)) {
            $pageInfoList = ArrayHelper::toArray($pageList);
            if (isZufulSite($websiteCode)) {
                $configPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($websiteCode);
                foreach ($pageInfoList as &$_pageInfo) {
                    $_pipelineName = $configPipelineList[ $_pageInfo['pipeline'] ] ?? $_pageInfo['pipeline'];
                    $_pageInfo['title'] = $_pipelineName .' - '. $_pageInfo['title'];
                    unset($_pageInfo['pipeline']);
                }
            }
        }

        $result = [
            'default' => empty($defaultPage) ? [] : ArrayHelper::toArray($defaultPage),
            'list' => $pageInfoList,
        ];
        return app()->helper->arrayResult($this->codeSuccess, 'success', $result);
    }

    /**
     * 根据专题活动子页面ID查询组件列表
     *
     * @param array $params
     * @return array
     * @throws JsonResponseException
     */
    public function getSpecialPageComponentList($params)
    {
        $dataModel = new SubActivityDataModel();
        return $this->getPageComponentList($dataModel, $params);
    }

    /**
     * 获取专题活动子页面总体数据列表
     *
     * @param array $params http请求参数
     * @return array
     * @throws JsonResponseException
     */
    public function getSpecialPageTotalDataList($params)
    {
        $dataModel = new SubActivityDataModel();
        return $this->getPageTotalDataList($dataModel, $params);
    }

    /**
     * 获取专题活动子页面组件或坑位数据列表
     *
     * @param array $params http请求参数
     * @return array
     * @throws JsonResponseException
     */
    public function getSpecialPageDetailDataList($params)
    {
        $dataModel = new SubActivityDataModel();
        return $this->getPageDetailDataList($dataModel, $params);
    }

    /**
     * 根据活动子页面ID查询组件列表
     *
     * @param \app\models\ActiveRecord $dataModel
     * @param array $params
     * @return array
     * @throws JsonResponseException
     */
    private function getPageComponentList($dataModel, $params)
    {
        $websiteCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($websiteCode)) {
            throw new JsonResponseException($this->codeFail, '无效的站点组编码');
        }

        if (empty($params['page_id']) || !is_numeric($params['page_id'])) {
            throw new JsonResponseException($this->codeFail, '请选择活动子页面');
        }

        if (!isset($this->siteCode[$websiteCode])) {
            return app()->helper->arrayResult($this->codeSuccess, 'success', []);
        }

        $site = $this->siteCode[$websiteCode];    //站点名称
        $startTime = date('Y-m-d', time()-3600*48); //开始时间
        $endTime = date('Y-m-d', time()-3600*24);   //结束时间

        //固定查询条件
        $condition = [
            'a.site' => $site,
            'a.sub_id' => $params['page_id'],
        ];

        $pageUiTableName = isZufulSite($websiteCode) ?  ZfPageUiModel::tableName() : PageUiModel::tableName();
        $componentList = $dataModel->find()->alias('a')
            ->select('a.location, a.module_id as id, d.name')
            ->leftJoin($pageUiTableName .' as c', 'a.module_id = c.id')
            ->leftJoin('ui_component as d', 'c.component_key = d.component_key')
            ->where($condition)
            ->andWhere(['>=', 'a.update_time', $startTime])
            ->andWhere(['<=', 'a.update_time', $endTime])
            ->groupBy('a.module_id')
            ->orderBy('a.location ASC')
            ->asArray()
            ->all();

        $componentList = empty($componentList) ? [] : $componentList;
        return app()->helper->arrayResult($this->codeSuccess, 'success', $componentList);
    }

    /**
     * 获取活动子页面总体数据列表
     *
     * @param \app\models\ActiveRecord $dataModel
     * @param array $params http请求参数
     * @return array
     * @throws JsonResponseException
     */
    private function getPageTotalDataList($dataModel, $params)
    {
        $websiteCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($websiteCode)) {
            throw new JsonResponseException($this->codeFail, '无效的站点组编码');
        }

        if (!isset($this->siteCode[$websiteCode])) {
            return $this->buildPageListResponse(Pagination::new(0));
        }

        $pageType = ($dataModel instanceof IndexActivityDataModel) ? 1 : 2; //1 首页 2 专题页
        $showType = $params['show_type'] ?? 1;      //显示类型 1 汇总 2 明细
        $site = $this->siteCode[$websiteCode];    //站点名称
        $excel = empty($params['excel']) ? false : true;  //是否为Excel导出操作
        $platform = $params['platform'] ?? 'all';           //终端维度：pc,m,ios,android,others,all
        $pipeline = $params['pipeline'] ?? '';              //ZF站点才有, 如：ZF
        $buyerIdentity = $params['buyer_identity'] ?? 2;    //新老客标识：2是整体的数据,1 代表新客,0 代表老客
        $pageId = isset($params['page_id']) ? (int)$params['page_id'] : 0;     //活动子页面ID
        $startTime = $params['start_time'] ?? date('Y-m-d', time()-3600*24);    //开始时间
        $endTime = $params['end_time'] ?? date('Y-m-d', time()-3600*24);        //结束时间

        //固定查询条件
        $condition = [
            'a.site' => $site,
            'a.platform' => $platform,
            'a.buyer_identity' => $buyerIdentity,
        ];
        if ($pageId > 0) $condition['a.sub_id'] = $pageId;

        if (isZufulSite($websiteCode) && (1 == $pageType) && !empty($pipeline)) {
            $rows = ZfPageModel::find()->select('id')
                ->where(['is_delete' => 0, 'activity_id' => 0, 'pipeline' => $pipeline])
                ->andWhere(['not in', 'status', [ZfPageModel::PAGE_STATUS_TO_BE_ONLINE]])
                ->asArray()
                ->all();
            if (!empty($rows)) {
                $pageIds = array_column($rows, 'id');
                $condition['a.sub_id'] = $pageIds;
            }
        }

        $query = $dataModel->find()->alias('a')
            ->where($condition)
            ->andWhere(['>=', 'a.update_time', $startTime])
            ->andWhere(['<=', 'a.update_time', $endTime]);

        //查询数据
        $dataList = NULL;
        $pagination = NULL;
        $pageIds = [];
        if (1 == $showType) {// 汇总显示
            $fields = 'a.id, a.buyer_identity, a.platform, a.sub_id, a.sub_uv, a.sub_ie_pv, a.sub_ic_pv, a.update_time';
            if (2 == $pageType) {
                $fields .= ', a.sub_pur_numb, a.sub_pay_amount';
            }
            $query = $query->select($fields)->groupBy('a.sub_id, a.update_time');

            $fields = 't.id, t.buyer_identity, t.platform, t.sub_id, sum(t.sub_uv) AS sub_uv, sum(t.sub_ie_pv) AS sub_ie_pv, sum(t.sub_ic_pv) AS sub_ic_pv, t.update_time';
            if (2 == $pageType) {
                $fields .= ', sum(t.sub_pur_numb) AS sub_pur_numb, sum(t.sub_pay_amount) AS sub_pay_amount';
            }

            $format = 'SELECT %s FROM (%s) AS t GROUP BY t.sub_id';
            $sql = sprintf($format, $fields, $query->createCommand()->getRawSql());
            $countSql = sprintf('SELECT count(*) as total FROM (%s) m', $sql);
            $result = $dataModel->getDb()->createCommand($countSql)->queryOne();
            $total = (int)$result['total'] ?? 0;
            $pagination = Pagination::new($total);

            $sql = sprintf('%s ORDER BY `t`.`sub_id` DESC LIMIT %d,%d', $sql, $pagination->offset, $pagination->limit);
            $dataList = $dataModel->getDb()->createCommand($sql)->queryAll();
            if (!empty($dataList)) {
                foreach ($dataList as &$row) {
                    if (!in_array($row['sub_id'], $pageIds)) {
                        $pageIds[] = $row['sub_id'];
                    }
                    $row['update_time'] = $startTime .' --- '. $endTime;
                    if (!empty($row['sub_ie_pv']) && is_numeric($row['sub_ie_pv'])) {
                        $row['sub_cl_rate'] = round(($row['sub_ic_pv'] / $row['sub_ie_pv'] * 100), 2) . '%';
                    } else {
                        $row['sub_cl_rate'] = '';
                    }
                }
            }
        } else {
            $query = $query->groupBy('a.sub_id, a.update_time');
            $subSql = str_replace('*', 'count(*)', $query->createCommand()->rawSql);
            $sql = sprintf('SELECT count(*) as total FROM (%s) t', $subSql);
            $result = $dataModel->getDb()->createCommand($sql)->queryOne();
            $total = (int)$result['total'] ?? 0;
            $pagination = Pagination::new($total);

            $fields = 'a.id, a.buyer_identity, a.platform, a.sub_id, a.sub_uv, a.sub_ie_pv, a.sub_ic_pv, a.sub_cl_rate, a.update_time';
            if (2 == $pageType) {
                $fields .= ', a.sub_pur_numb, a.sub_pay_amount';
            }

            $dataList = $query
                ->select($fields)
                ->limit($pagination->limit)
                ->offset($pagination->offset)
                ->orderBy('a.update_time DESC, a.sub_id DESC')
                ->asArray()
                ->all();
            if (!empty($dataList)) {
                foreach ($dataList as &$row) {
                    if (!in_array($row['sub_id'], $pageIds)) {
                        $pageIds[] = $row['sub_id'];
                    }
                    $row['sub_cl_rate'] .= '%';
                }
            }
        }

        //组装数据
        if (!empty($dataList)) {
            $isZFSite = isZufulSite($websiteCode);
            $pageLanguageQuery = $isZFSite ?  ZFPageLanguageModel::find() : PageLanguageModel::find();
            $pageLangList = $pageLanguageQuery->select('page_id, title')
                ->where(['page_id' => $pageIds])->groupBy('page_id')->indexBy('page_id')->asArray()->all();
            $pageLangList = empty($pageLangList) ? [] : $pageLangList;

            $ZFPageInfo = $configPipelineList = [];
            if ($isZFSite) {
                $ZFPageInfo = ZFPageModel::find()->select('id, pipeline')->where(['id' => $pageIds])->indexBy('id')->asArray()->all();
                $configPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($websiteCode);
            }

            foreach ($dataList as &$row) {
                $_pageId = $row['sub_id'];
                $row['title'] = $pageLangList[$_pageId]['title'] ?? '';
                if ($isZFSite) {
                    $row['pipeline'] = '';
                    if (isset($ZFPageInfo[$_pageId]['pipeline'])) {
                        $row['pipeline'] = $configPipelineList[ $ZFPageInfo[$_pageId]['pipeline'] ] ?? '';
                    }
                }
            }
        }

        if ($excel) {
            app()->response->isSent = true;

            $orderedCellNames = ['update_time', 'sub_id', 'platform', 'title', 'sub_ie_pv', 'sub_uv', 'sub_ic_pv', 'sub_cl_rate', 'sub_pur_numb', 'sub_pay_amount'];
            $this->exportExcel($pageType, '销售展现数据统计', $dataList, $orderedCellNames);
        } else {
            return $this->buildPageListResponse($pagination, $dataList);
        }
    }

    /**
     * 获取活动子页面组件或坑位数据列表
     *
     * @param \app\models\ActiveRecord $dataModel
     * @param array $params http请求参数
     * @return array
     * @throws JsonResponseException
     */
    private function getPageDetailDataList($dataModel, $params)
    {
        $websiteCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($websiteCode)) {
            throw new JsonResponseException($this->codeFail, '无效的站点组编码');
        }

        if (empty($params['page_id']) || !is_numeric($params['page_id'])) {
            throw new JsonResponseException($this->codeFail, '请选择专题页面');
        }

        if (!isset($this->siteCode[$websiteCode])) {
            return $this->buildPageListResponse(Pagination::new(0));
        }

        $viewType = $params['view_type'] ?? 1;      //查看方式 1 广告位查看 2 坑位查看
        $site = $this->siteCode[$websiteCode];    //站点名称
        $excel = empty($params['excel']) ? false : true;  //是否为Excel导出操作
        $platform = $params['platform'] ?? 'all';           //终端维度：pc,m,ios,android,others,all
        $buyerIdentity = $params['buyer_identity'] ?? 2;    //新老客标识：2是整体的数据,1 代表新客,0 代表老客
        $pageId = (int)$params['page_id'];                  //活动子页面ID
        $startTime = $params['start_time'] ?? date('Y-m-d', time()-3600*24);    //开始时间
        $endTime = $params['end_time'] ?? date('Y-m-d', time()-3600*24);        //结束时间

        $sqlParams = [
            'site'            => $site,
            'platform'        => $platform,
            'buyer_identity'  => $buyerIdentity,
            'sub_id'          => $pageId,
            'module_id'       => $params['module_id'] ?? 0,
            'start_time'      => $startTime,
            'end_time'        => $endTime
        ];

        if (1 == $viewType) {
            return $this->getPageComponentDataList($websiteCode, $dataModel, $sqlParams, $excel);
        }

        return $this->getPagePitDataList($dataModel, $sqlParams, $excel);
    }


    /**
     * 获取活动子页面组件数据列表
     *
     * @param string $websiteCode
     * @param \app\models\ActiveRecord $dataModel
     * @param array $sqlParams sql参数
     * @param boolean $excel 是否为Excel导出操作
     * @return array
     */
    private function getPageComponentDataList($websiteCode, $dataModel, $sqlParams, $excel)
    {
        $pageType = ($dataModel instanceof IndexActivityDataModel) ? 1 : 2; //1 首页 2 专题页

        //固定查询条件
        $condition = [
            'a.site'            => $sqlParams['site'],
            'a.platform'        => $sqlParams['platform'],
            'a.buyer_identity'  => $sqlParams['buyer_identity'],
            'a.sub_id'          => $sqlParams['sub_id']
        ];

        $query = $dataModel->find()->alias('a')
            ->where($condition)
            ->andWhere(['>=', 'a.update_time', $sqlParams['start_time']])
            ->andWhere(['<=', 'a.update_time', $sqlParams['end_time']])
            ->groupBy('a.module_id, a.update_time');

        $subSql = str_replace('*', 'count(*)', $query->createCommand()->rawSql);
        $sql = sprintf('SELECT count(*) as total FROM (%s) t', $subSql);
        $result = $dataModel->getDb()->createCommand($sql)->queryOne();
        $total = (int)$result['total'] ?? 0;
        $pagination = Pagination::new($total);

        $fields = 'a.id, a.buyer_identity, a.platform, a.location, a.module_id, a.module_ie_pv, a.module_ic_pv, a.update_time';
        if (2 == $pageType) {
            $fields .= ', a.module_pur_numb, a.module_pay_amount';
        }

        $dataList = $query
            ->select($fields)
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('a.update_time DESC')
            ->asArray()
            ->all();

        //组装数据
        if (!empty($dataList)) {

            $pageUiQuery = isZufulSite($websiteCode) ?  ZFPageUiModel::find() : PageUiModel::find();
            $ids = array_unique(array_column($dataList, 'module_id'));
            $componentList = $pageUiQuery->alias('c')
                ->select('c.id, d.name')
                ->leftJoin('ui_component as d', 'c.component_key = d.component_key')
                ->where(['c.id' => $ids])
                ->indexBy('id')
                ->asArray()
                ->all();

            $componentList = empty($componentList) ? [] : $componentList;
            foreach ($dataList as &$row) {
                $row['component_name'] = $componentList[$row['module_id']]['name'] ?? '';
            }
        }

        if ($excel) {
            app()->response->isSent = true;
            $pageType = ($dataModel instanceof IndexActivityDataModel) ? 1 : 2;
            $orderedCellNames = ['update_time', 'location', 'component_name', 'module_ie_pv', 'module_ic_pv', 'module_pur_numb', 'module_pay_amount'];
            $this->exportExcel($pageType, '广告数据统计', $dataList, $orderedCellNames);
        } else {
            return $this->buildPageListResponse($pagination, $dataList);
        }
    }

    /**
     * 获取活动子页面坑位数据列表
     *
     * @param \app\models\ActiveRecord $dataModel
     * @param array $sqlParams sql参数
     * * @param boolean $excel 是否为Excel导出操作
     * @return array
     * @throws JsonResponseException
     */
    private function getPagePitDataList($dataModel, $sqlParams, $excel)
    {
        if (empty($sqlParams['module_id'])) {
            throw new JsonResponseException($this->codeFail, '请选择组件');
        }

        $pageType = ($dataModel instanceof IndexActivityDataModel) ? 1 : 2; //1 首页 2 专题页

        //固定查询条件
        $condition = [
            'a.site'            => $sqlParams['site'],
            'a.platform'        => $sqlParams['platform'],
            'a.buyer_identity'  => $sqlParams['buyer_identity'],
            'a.sub_id'          => $sqlParams['sub_id'],
            'a.module_id'       => $sqlParams['module_id']
        ];

        $query = $dataModel->find()->alias('a')
            ->where($condition)
            ->andWhere(['>=', 'a.update_time', $sqlParams['start_time']])
            ->andWhere(['<=', 'a.update_time', $sqlParams['end_time']]);

        //查询数据
        $total = $query->count('a.pit_id');
        $pagination = Pagination::new($total);

        $fields = 'a.id, a.buyer_identity, a.platform, a.pit_id, a.pit_ie_pv, a.pit_ic_pv, a.pit_cl_rate, a.update_time';
        if (2 == $pageType) {
            $fields .= ', a.pit_pur_numb, a.pit_pay_amount';
        }

        $dataList = $query
            ->select($fields)
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('a.update_time DESC')
            ->asArray()
            ->all();

        //组装数据
        if (!empty($dataList)) {
            foreach ($dataList as &$row) {
                $row['pit_cl_rate'] .= '%';
            }
        }

        if ($excel) {
            app()->response->isSent = true;
            $pageType = ($dataModel instanceof IndexActivityDataModel) ? 1 : 2;
            $orderedCellNames = ['update_time', 'pit_cl_rate', 'pit_id', 'pit_ie_pv', 'pit_ic_pv', 'pit_pur_numb', 'pit_pay_amount'];
            $this->exportExcel($pageType, '坑位数据统计', $dataList, $orderedCellNames);
        } else {
            return $this->buildPageListResponse($pagination, $dataList);
        }
    }


    /**
     * 分页数据返回
     *
     * @param Pagination $pagination
     * @param array $list
     *
     * @return array
     */
    private function buildPageListResponse($pagination, $list=[])
    {
        return app()->helper->arrayResult($this->codeSuccess, 'success',
            [
                'list' => $list,
                'pagination' => [
                    'pageNo' => $pagination->page + 1,
                    'pageSize' => $pagination->pageSize,
                    'totalCount' => $pagination->totalCount
                ],
            ]
        );
    }

    /**
     * 根据传入参数平台简码获取站点简码
     * @param string $platform
     * @param string $websiteCode
     * @return string
     */
    private function getSiteCode($platform, $websiteCode)
    {
        if ('m' == $platform) {
            return SitePlatform::getSiteCodeByPlatformType(SitePlatform::PLATFORM_TYPE_WAP, $websiteCode);
        } elseif ('pc' == $platform) {
            return SitePlatform::getSiteCodeByPlatformType(SitePlatform::PLATFORM_TYPE_PC, $websiteCode);
        }

        return NULL;
    }

    /**
     * 获取活动子页面列表
     *
     * @param string $websiteCode
     * @param array $condition
     * @param string $keyword
     * @param int $limit
     *
     * @return array
     */
    private function getPageList($websiteCode, $condition, $keyword=NULL, $limit=0)
    {
        $pageLanguageTableName = isZufulSite($websiteCode) ?  ZfPageLanguageModel::tableName() : PageLanguageModel::tableName();
        $pageFiledNames = isZufulSite($websiteCode) ? 'p.id, p.pipeline, l.title' : 'p.id, l.title';
        //默认活动
        if (isset($condition['p.activity_id']) && (SiteConstants::HOME_PAGE_ACTIVITY_ID == $condition['p.activity_id'])) { //首页活动
            $siteCode = is_array($condition['p.site_code']) ? $condition['p.site_code'][0] : $condition['p.site_code'];
            $defaultCondition = $condition;
            $defaultCondition['p.site_code'] = $siteCode;
            $defaultCondition['p.status'] = [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_ONLINE_UPDATE];

            $pageQuery = isZufulSite($websiteCode) ? ZfPageModel::find() :  PageModel::find();
            $defaultPage = $pageQuery->alias('p')
                ->select($pageFiledNames)
                ->leftJoin($pageLanguageTableName . ' as l', 'p.id = l.page_id')
                ->where($defaultCondition)->groupBy('p.id')->one();
        } else {
            $pageQuery = isZufulSite($websiteCode) ? ZfPageModel::find() :  PageModel::find();
            $siteCode = is_array($condition['p.site_code']) ? $condition['p.site_code'][0] : $condition['p.site_code'];
            $defaultCondition = $condition;
            $defaultCondition['p.site_code'] = $siteCode;
            $defaultCondition['p.status'] = PageModel::PAGE_STATUS_HAS_ONLINE;
            $defaultPage = $pageQuery->alias('p')
                ->select($pageFiledNames)
                ->leftJoin($pageLanguageTableName . ' as l', 'p.id = l.page_id')
                ->where($defaultCondition)->groupBy('p.id')->orderBy('p.id DESC')->one();
        }

        $pageQuery = isZufulSite($websiteCode) ? ZfPageModel::find() :  PageModel::find();
        $query = $pageQuery->alias('p')
            ->select($pageFiledNames)
            ->leftJoin($pageLanguageTableName . ' as l', 'p.id = l.page_id')
            ->where($condition);

        if (!empty($keyword)) {
            if(preg_match("/^[1-9][0-9]*$/", $keyword)) {
                $query->andWhere(['p.id' => $keyword]);
            } else {
                $query->andWhere(['like', 'l.title', $keyword]);
            }
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $pageList = $query->groupBy('p.id')->orderBy('p.id DESC')->all();
        return [$defaultPage, $pageList];
    }

    private function exportExcel($pageType, $title, $dataList, $orderedCellNames)
    {
        $cellName = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X',
            'Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS',
            'AT','AU','AV','AW','AX','AY','AZ'
        ];
        $cellTitleMapping = [
            'id'                => '数据记录ID',
            'buyer_identity'    => '新老客标识',
            'page_name'         => '页面名称',
            'page_type'         => '页面类型',
            'sub_id'            => ($pageType == 1) ? '首页ID' : '专题页ID',
            'platform'          => '端口',
            'sub_ie_pv'         => 'PV',
            'sub_ic_pv'         => '点击数',
            'sub_uv'            => 'UV',
            'sub_cl_rate'       => '点击率',
            'sub_pur_numb'      => '购买客户数',
            'sub_pay_amount'    => '销售额',
            'location'          => '位置',
            'module_id'         => '组件ID',
            'module_ie_pv'      => 'PV',
            'module_ic_pv'      => '点击数',
            'module_pur_numb'   => '购买客户数',
            'module_pay_amount' => '销售额',
            'pit_id'            => '坑位',
            'pit_ie_pv'         => 'PV',
            'pit_ic_pv'         => '点击数',
            'pit_cl_rate'       => '点击率',
            'pit_pur_numb'      => '购买客户数',
            'pit_pay_amount'    => '销售额',
            'platform'          => '终端维度',
            'update_time'       => '日期',
            'site'              => '网站',
            'title'             => ($pageType == 1) ? '首页名称' : '专题页名称',
            'component_name'    => '组件名称'
        ];

        //表格对象
        $objExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objExcel->setActiveSheetIndex(0);
        $activeSheet = $objExcel->getActiveSheet();
        $activeSheet->setTitle($title);

        if (!empty($dataList)) {
            $lastCellName = $cellName[count($dataList[0]) - 1];
            $activeSheet->getStyle('A1:' . $lastCellName . '1')->getFont()->setBold(true);

            //设置表头
            foreach ($orderedCellNames as $key => $cellTitle) {
                $activeSheet->setCellValue($cellName[$key] . '1', ($cellTitleMapping[$cellTitle] ?? $cellTitle));
            }

            //写入数据
            $dataStartRow = 2;
            foreach ($dataList as $row => $dataInfo) {
                $curRow = $dataStartRow + $row;
                foreach ($orderedCellNames as $index => $_cellName) {
                    $value = $dataInfo[$_cellName] ?? '';
                    if ('buyer_identity' == $_cellName) {
                        if (2 == $value) {
                            $value = '整体';
                        } elseif (1 == $value) {
                            $value = '新客';
                        } elseif (0 == $value) {
                            $value = '老客';
                        }
                    }

                    $activeSheet->setCellValue($cellName[$index] . $curRow, $value);
                }
            }
        }

        //输出
        header("Pragma: public");
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        header('Content-Type:application/vnd.ms-execl');
        header("Content-Disposition: attachment;filename={$title}.xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter->save('php://output');
    }

    /**
     * 统计数据列表
     *
     * @param string platform  终端维度：pc,m,ios,android,others,all
     * @param int is_new 新老客标识:0 , 1 , 2 [说明:2是整体的数据,1 代表新客,0 代表老客]
     * @param string site_code   站点简称
     * @param string start_time 开始时间2018-10-05
     * @param string end_time 结束时间2018-10-05
     * @param int page_size 每页显示数 默认20
     * @param int page_no 页码
     *
     * @return array
     * @throws JsonResponseException
     */
    public function getDataList($params)
    {
        if (empty($params['site_code'])) {
            throw new JsonResponseException($this->codeFail, '无效的站点编码');
        }

        $list = [];
        $total = 0;
        if (isset($this->siteCode[$params['site_code']])) {
            $site = $this->siteCode[$params['site_code']];
            $platform = isset($params['platform']) ? $params['platform'] : $this->platform;
            $isNew = isset($params['is_new']) ? $params['is_new'] : $this->isNew;
            $startTime = isset($params['start_time']) ? $params['start_time'] : date('Y-m-d', time()-3600*24);
            $endTime = isset($params['end_time']) ? $params['end_time'] : date('Y-m-d', time()-3600*24);
            $where = [
                'a.site' => $site,
                'a.buyer_identity' => $isNew,
                'a.platform' => $platform
            ];

            $query = ActivityDataModel::find();
            $query->alias('a')
                ->leftJoin('page_language as b', 'a.sub_id = b.page_id')
                ->leftJoin('page_ui_component as c', 'a.module_id = c.id')
                ->leftJoin('ui_component as d', 'c.component_key = d.component_key')
                ->where($where)
                ->andWhere(['>=', 'a.update_time', $startTime])
                ->andWhere(['<=', 'a.update_time', $endTime]);
            $total = $query->count('DISTINCT(a.id)');
            $pagination = Pagination::new($total);
            $list = $query
                ->select('a.*,b.title,d.name')
                ->limit($pagination->limit)
                ->offset($pagination->offset)
                ->orderBy('a.update_time desc, a.sub_id desc, module_id desc, pit_id desc')
                ->groupBy('a.id')
                ->asArray()
                ->all();

            if (!empty($list)) {
                foreach ($list as &$row) {
                    $row['sub_cl_rate'] = $row['sub_cl_rate']  .'%';
                    $row['pit_cl_rate'] = $row['pit_cl_rate']  .'%';
                }
            }
        } else {
            $pagination = Pagination::new($total);
        }

        return app()->helper->arrayResult(
            0,
            'success',
            [
                'list' => $list,
                'pagination' => [
                    'pageNo' => $pagination->page+1,
                    'pageSize' => $pagination->pageSize,
                    'totalCount' => $total
                ],
            ]
        );

    }
}
