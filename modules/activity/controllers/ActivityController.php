<?php

namespace app\modules\activity\controllers;

use yii\web\Response;

/**
 * 活动管理类
 * @property \app\modules\activity\components\ActivityComponent $ActivityComponent
 * @author wangmeng
 *
 */
class ActivityController extends Controller
{
    /**
     * 活动列表首页
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        return $this->render('//layouts/vue-entry.php');
    }

    /**
     * 获取活动详情
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionGet()
    {
        return $this->ActivityComponent->get(app()->request->get('id'));
    }

    /**
     * 活动列表数据
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \ego\base\JsonResponseException
     */
    public function actionList()
    {
        return $this->ActivityComponent->lists(app()->request->get());
    }

    /**
     * 三端合一, 新增活动
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @since v1.4.0
     */
    public function actionUserActivityList()
    {
        return $this->ActivityComponent->userActivityList(app()->request->get());
    }

    /**
     * 三端合一, 新增活动
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @since v1.4.0
     */
    public function actionAdd()
    {
        return $this->ActivityComponent->groupAdd(app()->request->post());
    }

    /**
     * 编辑活动
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionEdit()
    {
        return $this->ActivityComponent->groupEdit(app()->request->post());
    }

    /**
     * 审核活动(verify_status可为4/5/7/8)
     * @return array
     * @throws \yii\db\StaleObjectException
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionVerify()
    {
        return $this->ActivityComponent->verify(
            (int)app()->request->post('id'),
            (int)app()->request->post('status')
        );
    }

    /**
     * 设置/取消常用活动
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionFrequently()
    {
        return $this->ActivityComponent->frequently(app()->request->get('id'));
    }

    /**
     * 删除活动
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        return $this->ActivityComponent->delete(app()->request->get('id'));
    }

    /**
     * 活动权限加锁
     *
     * @param $id
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionLock($id)
    {
        return $this->ActivityComponent->lock($id);
    }
}
