<?php

namespace app\modules\common\zf\traits;

use app\modules\common\zf\models\ActivityModel;
use app\modules\common\zf\models\PageModel;

/**
 * 页面和活动审核流程
 * Created by PhpStorm.
 * User: tengjiashun
 * Date: 2018/4/12
 * Time: 8:43
 */
trait CommonVerifyStatusTrait
{
    /**
     * 有效的审核状态变更配置
     * 当前status   变更status   变更verify_status    操作说明
     *     1-----------2--------------5----------------上线
     *     2-----------4--------------8----------------下线
     *     4-----------2--------------5----------------下线后直接再次上线
     * @var array
     */
    protected $verifyConfig = [
        '1-2' => 1,
        '2-2' => 1,
        '2-4' => 1,
        '4-2' => 1,
        '4-4' => 1
    ];

    /**
     * 页面状态审核
     * @param int $currentStatus 当前状态
     * @param int $changeToStatus 需要变为的状态
     * @return array
     */
    public function checkStatus($currentStatus, $changeToStatus)
    {
        $key = $currentStatus . '-' . $changeToStatus;
//        if (!array_key_exists($key, $this->verifyConfig)) {
//            return app()->helper->arrayResult(1, 'status状态不正确！', []);
//        }

        return app()->helper->arrayResult(0, 'success');
    }

    /**
     * 活动审核前置判断
     * @param int $activityId 活动ID
     * @param int $status 活动要变更的状态
     * @return array
     */
    public function beforeVerifyActivity($activityId, $status)
    {

        $activityModel = ActivityModel::findOne([
            'id' => $activityId,
            'is_delete' => ActivityModel::NOT_DELETE
        ]);

        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($activityModel)) {
            return app()->helper->arrayResult($this->codeFail, '只有活动创建者才具有此权限');
        }

        $correctStatus = [
            ActivityModel::STATUS_HAS_ONLINE,
            ActivityModel::STATUS_HAS_OFFLINE
        ];
        if (!\in_array($status, $correctStatus, true)) {
            return app()->helper->arrayResult(1, '操作状态不正确(2/4)');
        }

        if (!$activityModel) {
            return app()->helper->arrayResult(1, '无效的活动ID');
        }

        if (ActivityModel::STATUS_HAS_OFFLINE === $activityModel->status) {
            $res = ActivityModel::isEnabled($activityId, true);
            if (true !== $res) {
                return app()->helper->arrayResult(1, $res['message']);
            }
        }

        $checkStatus = $this->checkStatus((int)$activityModel->status, (int)$status);
        $checkStatus['data']['model'] = $activityModel;

        return $checkStatus;
    }

    /**
     * 页面审核前置判断
     * @param int $pageId 页面ID
     * @param int $status 活动要变更的状态
     * @return array
     */
    public function beforeVerifyPage($pageId, $status)
    {
        $correctStatus = [
            PageModel::PAGE_STATUS_HAS_ONLINE,
            PageModel::PAGE_STATUS_HAS_OFFLINE
        ];
        if (!\in_array($status, $correctStatus, true)) {
            return app()->helper->arrayResult(1, '操作状态不正确(2/4)');
        }

        $pageModel = PageModel::findOne([
            'id' => $pageId,
            'is_delete' => PageModel::NOT_DELETE
        ]);
        if (!$pageModel) {
            return app()->helper->arrayResult(1, '无效的页面ID');
        }

        if ($pageModel->end_time > 0) {
            $endTime = strtotime(date('Y-m-d H:00:00', $pageModel->end_time));
            $nowTime = strtotime(date('Y-m-d H:00:00'));
            if ($endTime <= $nowTime) {
                return app()->helper->arrayResult(1,  '下线时间小于当前时间，请先修改子页面的下线时间');
            }
        }

        if (!PageModel::checkAllLangSet($pageModel->activity_id, $pageId)) {
            return app()->helper->arrayResult(1, '活动设置了多语言，但页面的多语言配置不完整，请先去“页面编辑”下设置');
        }

        if (
            !empty($pageModel->activity_id)
            && true !== ($res = ActivityModel::isEnabled($pageModel->activity_id, true))
        ) {
            return app()->helper->arrayResult(1, '该页面所属活动状态不正确：' . $res['message']);
        }

        $checkStatus = $this->checkStatus((int)$pageModel->status, (int)$status);
        $checkStatus['data']['model'] = $pageModel;

        return $checkStatus;
    }

    /**
     * 页面发布前置判断
     *
     * @param $pageId
     *
     * @return array
     */
    public function beforeVerifyRelease($pageId)
    {
        $pageModel = PageModel::findOne([
            'id' => $pageId,
            'is_delete' => PageModel::NOT_DELETE
        ]);
        if (!$pageModel) {
            return app()->helper->arrayResult(1, '无效的页面ID');
        }

        if (!PageModel::checkAllLangSet($pageModel->activity_id, $pageId)) {
            return app()->helper->arrayResult(1, '活动设置了多语言，但页面的多语言配置不完整，请先去“页面编辑”下设置');
        }
    
        $lockKey = app()->redisKey->getHomePageRollbackLockKey($pageModel->site_code);
        if (app()->redis->sismember($lockKey, $pageId)) {
            return app()->helper->arrayResult(1, '页面由于版本回滚已被锁定，请联系相关人员解除锁定');
        }
        
        if (
            !empty($pageModel->activity_id)
            && true !== ($res = ActivityModel::isEnabled($pageModel->activity_id, true))
        ) {
            return app()->helper->arrayResult(1, $res['message']);
        }

        return app()->helper->arrayResult(0, 'success', $pageModel);
    }
}
