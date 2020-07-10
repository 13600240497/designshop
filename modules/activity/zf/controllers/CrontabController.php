<?php
/**
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/05/07
 * Time: 16:29
 */

namespace app\modules\activity\zf\controllers;

/**
 * 定时任务处理类
 * @property \app\modules\activity\zf\components\CrontabComponent CrontabComponent
 * @package app\modules\activity\controllers
 */
class CrontabController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * 刷新需要定时刷新的页面
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionRefreshPage()
    {
        return $this->CrontabComponent->refreshPage();
    }

    /**
     * 对到时间下线而还未下线的活动下线（包括其下的页面）
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionOfflinePage()
    {
        return $this->CrontabComponent->offlinePage();
    }

    /**
     * 推送页面
     * @return array
     * @throws \Throwable
     */
    public function actionPushPage()
    {
        return $this->CrontabComponent->pushPage();
    }

    /**
     * 清理push队列
     * @return array
     * @throws \Throwable
     */
    public function actionCleanPush()
    {
        return $this->CrontabComponent->cleanPushCache();
    }

    /**
     * 查询push队列详情
     * @return array
     */
    public function actionPushInfo()
    {
        return $this->CrontabComponent->getPushCacheInfo();
    }

    /**
     * 查询refresh队列详情
     * @return array
     */
    public function actionRefreshInfo()
    {
        return $this->CrontabComponent->getRefreshCacheInfo();
    }

    /**
     * 查询refresh队列详情
     * @return array
     */
    public function actionSyncBigDataMysqlData()
    {
        return $this->CrontabComponent->syncBigDataMySqlData();
    }

    /**
     * 自动更新上线产品
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionUpdateOnlineGoods()
    {
        return $this->CrontabComponent->updateOnlineGoods();
    }


    /**
     * 获取上线的页面保存redis队列
     *
     * @return array
     */
    public function actionGetOnlinePage()
    {
        return $this->CrontabComponent->getOnlinePages();
    }

     /**
     * 推送活动落地页到网站
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionPushData()
    {
        return $this->CrontabComponent->pushData();
    }

    /**
     * 清理页面缓存
     *
     * @param string $siteCode
     */
    public function actionCleanPagePublishCache(string $siteCode, string $date)
    {
        if ($date != date('Ymd')) {
            return app()->helper->arrayResult(1, '日期必须是当天(' . date('Ymd') . ')，防止误操作!');
        }
        $cacheKey = app()->redisKey->getPagePublishCacheKey($siteCode);

        if (app()->redis->del($cacheKey)) {
            return app()->helper->arrayResult(0, '清理成功');
        } else {
            return app()->helper->arrayResult(1, '清理失败，要删除的key不存在');
        }
    }

    /**
     * UI组件自动刷新任务
     *
     * @return array
     */
    public function actionUiAutoRefresh()
    {
        return $this->CrontabComponent->uiAutoRefresh();
    }

    /**
     * UI组件自动刷新任务(RG)
     *
     * @return array
     */
    public function actionUiAutoRefreshRg()
    {
        return $this->CrontabComponent->uiAutoRefreshRg();
    }

    /**
     * 消费UI组件异步接口兜底队列任务
     * @return array
     */
    public function actionUiAsyncApiFallback()
    {
      return $this->CrontabComponent->uiAsyncApiFallback();
    }

	/**
	 * 定时检查一键刷新头尾页任务执行情况
	 *
	 * @return array
	 */
    public function actionCheckRefreshTaskStatus()
    {
		return $this->CrontabComponent->checkRefreshTaskStatus();
    }
}
