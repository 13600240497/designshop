<?php

namespace app\modules\base\zf\components;

use app\base\Pagination;
use app\modules\base\models\AdminModel;
use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\PagePublishLogModel;
use yii\helpers\ArrayHelper;

/**
 * 首页发布日志组件
 */
class HomeLogComponent extends Component
{
    /**
     * @var PagePublishLogModel 页面发布日志模型
     */
    protected $model;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->model = new PagePublishLogModel();
    }
    
    /**
     * 列表
     *
     * @param string $siteCode
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function lists(string $siteCode)
    {
        $query = PagePublishLogModel::find()->alias('ppl')
            ->select(
                'ppl.*,
                pl.title,
                pl.page_url,
                group_concat(ppl.lang) as langList,
                p.pipeline'
            )
            ->innerJoin(PageModel::tableName() . ' as p', 'ppl.page_id = p.id')
            ->innerJoin(PageLanguageModel::tableName() . ' as pl', 'pl.page_id = ppl.page_id')
            ->where([
                'p.activity_id'   => 0,
                'ppl.log_type'    => PagePublishLogModel::LOG_TYPE_PUBLISH,
                'ppl.action_type' => PagePublishLogModel::ACTION_TYPE_ONLINE,
                'ppl.site_code'   => $siteCode,
                'ppl.file_type'   => 'html'
            ])
            ->andWhere("ppl.online_user != ''")
            ->groupBy('ppl.version');
        
        $count = $query->count();
        $pagination = Pagination::new($count);
        
        $list = $query->orderBy('ppl.id desc')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all();
        
        if ($list) {
            $list = ArrayHelper::toArray($list);
            $siteGroup = explode('-', $siteCode)[0];
            $pipelineConf = app()->params['site'][ $siteGroup ]['pipeline'];
            $domain = app()->params['sites'][ $siteCode ]['home_secondary_domain'][ $list[0]['pipeline'] ][ $list[0]['lang'] ];
            foreach ($list as $key => $item) {
                $list[ $key ]['create_user'] = AdminModel::getRealNameByUserName($item['create_user']);
                $list[ $key ]['update_user'] = AdminModel::getRealNameByUserName($item['update_user']);
                $list[ $key ]['rollback_user'] = AdminModel::getRealNameByUserName($item['rollback_user']);
                $list[ $key ]['online_user'] = AdminModel::getRealNameByUserName($item['online_user']);
                $langList = array_unique(explode(',', $item['langList']));
                $list[ $key ]['detail'] = '首页发布上线';
                $list[ $key ]['pipeline_name'] = $pipelineConf[ $list[ $key ]['pipeline'] ];
                $list[ $key ]['ip'] = long2ip($list[ $key ]['ip']);
                $list[ $key ]['page_url'] = $domain . '/'
                    . substr($item['file_name'], 0, strpos($item['file_name'], '_preview'))
                    . '.' . pathinfo($item['file_name'])['extension'];
                $list[ $key ]['langList'] = ActivityModel::getLangListByLangStringNotPipeline(implode(',', $langList));
                /** @var array[][] $list */
                foreach ($list[ $key ]['langList'] as $langKey => $langItem) {
                    $list[ $key ]['langList'][ $langKey ]['viewUrl'] = '';
                }
            }
        }
        
        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            [
                'list'       => $list ?? [],
                'pagination' => [
                    $pagination->pageParam     => (int) $pagination->page + 1,
                    $pagination->pageSizeParam => (int) $pagination->pageSize,
                    'totalCount'               => (int) $pagination->totalCount
                ]
            ]
        );
    }
    
    /**
     * 查询首页历史版本记录
     *
     * @param string $siteCode
     * @param int    $pageId
     * @param string $pipeline
     * @param string $lang
     *
     * @return array
     */
    public function getHistoryVersion(string $siteCode, int $pageId, string $pipeline, string $lang)
    {
        $data = [];
        $nowResult = PagePublishLogModel::getPageCurrentDayPushVersions($pageId);
        if (!empty($nowResult) && is_array($nowResult)) {
            $siteConf = app()->params['sites'][ $siteCode ];
            $domain = $siteConf['home_secondary_domain'][ $pipeline ][ $lang ];
            $nowResult = array_filter($nowResult, function ($item) {
                return false !== strstr($item['file_name'], 'preview');
            });
            $data['now_versions'] = array_map(function ($item) use ($domain) {
                $itemStr = substr($item['file_name'], 0, strpos($item['file_name'], '.'));
                $itemArr = explode('_', $itemStr);
                
                return [
                    'log_id'   => $item['id'],
                    'version'  => date('H:i:s', array_pop($itemArr)),
                    'page_url' => $domain . '/' . $item['file_name']
                ];
            }, $nowResult);
        }
        
        $beforeResult = PagePublishLogModel::getPageBeforeDayPushVersions($pageId);
        if (!empty($beforeResult) && is_array($beforeResult)) {
            $siteConf = app()->params['sites'][ $siteCode ];
            $domain = $siteConf['home_secondary_domain'][ $pipeline ][ $lang ];
            $beforeResult = array_filter($beforeResult, function ($item) {
                return false !== strstr($item['file_name'], 'preview');
            });
            $data['before_versions'] = array_map(function ($item) use ($domain) {
                $itemStr = substr($item['file_name'], 0, strpos($item['file_name'], '.'));
                $itemArr = explode('_', $itemStr);
                $time = array_pop($itemArr);
         
                return [
                    'log_id'   => $item['id'],
                    'version'  => $this->checkDateIsValid($time) ? $time : date('Ymd', $time),
                    'page_url' => $domain . '/' . $item['file_name']
                ];
            }, $beforeResult);
        }
        
        return app()->helper->arrayResult($this->codeSuccess, 'Success', $data);
    }
    
    /**
     * 校验日期格式是否正确
     *
     * @param string $date 日期
     * @param string $formats 需要检验的格式数组
     * @return boolean
     */
    function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d", 'Ymd')) {
        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }
        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * 首页回滚版本解锁
     *
     * @param string $siteCode
     * @param int    $pageId
     *
     * @return array
     */
    public function removeRollbackLock(string $siteCode, int $pageId)
    {
        $lockKey = app()->redisKey->getHomePageRollbackLockKey($siteCode);
        if (app()->redis->srem($lockKey, $pageId) > 0) {
            return app()->helper->arrayResult($this->codeSuccess, '因首页异常，当前页面执行过版本回退功能，已解锁成功');
        }
        
        return app()->helper->arrayResult($this->codeFail, '因首页异常，当前页面执行过版本回退功能，解锁失败');
    }
}
