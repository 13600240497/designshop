<?php

namespace app\modules\base\controllers;

/**
 * 数据报表任务控制器
 *
 * @property \app\modules\base\components\ActivityDataComponent $ActivityDataComponent
 */
class ReportController extends Controller
{
    /**
     * 首页活动子页面列表
     */
    public function actionHomePageList()
    {
        return $this->ActivityDataComponent->getHomePageList(app()->request->get());
    }

    /**
     * 根据首页活动子页面ID查询组件列表
     */
    public function actionHomePageComponentList()
    {
        return $this->ActivityDataComponent->getHomePageComponentList(app()->request->get());
    }

    /**
     * 获取首页活动子页面总体数据列表
     */
    public function actionHomePageTotalDataList()
    {
        return $this->ActivityDataComponent->getHomePageTotalDataList(app()->request->get());
    }

    /**
     * 获取首页活动子页面组件或坑位数据列表
     */
    public function actionHomePageDetailDataList()
    {
        return $this->ActivityDataComponent->getHomePageDetailDataList(app()->request->get());
    }

    /**
     * 专题活动子页面列表
     */
    public function actionSpecialPageList()
    {
        return $this->ActivityDataComponent->getSpecialPageList(app()->request->get());
    }

    /**
     * 根据专题活动子页面ID查询组件列表
     */
    public function actionSpecialPageComponentList()
    {
        return $this->ActivityDataComponent->getSpecialPageComponentList(app()->request->get());
    }

    /**
     * 获取专题活动子页面总体数据列表
     */
    public function actionSpecialPageTotalDataList()
    {
        return $this->ActivityDataComponent->getSpecialPageTotalDataList(app()->request->get());
    }

    /**
     * 获取专题活动子页面组件或坑位数据列表
     */
    public function actionSpecialPageDetailDataList()
    {
        return $this->ActivityDataComponent->getSpecialPageDetailDataList(app()->request->get());
    }

    /**
     * 首页统计
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionHomePage()
    {
        return $this->render('home_page');
    }

    /**
     * 首页广告位统计
     * @return string
     */
    public function actionHomeAd()
    {
        return $this->render('home_ad');
    }

    /**
     * 活动页统计
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionActivityPage()
    {
        return $this->render('activity_page');
    }

    /**
     * 活动页广告位统计
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionActivityAd()
    {
        return $this->render('activity_ad');
    }

    /**
     * 活动页整体效果统计
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionActivityTotal()
    {
        return $this->render('activity_total');
    }

    /**
     * 活动页品类销售统计
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionActivityCategory()
    {
        return $this->render('activity_category');
    }
}
