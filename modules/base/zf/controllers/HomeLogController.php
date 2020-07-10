<?php

namespace app\modules\base\zf\controllers;

/**
 * 管理员操作日志控制器
 * @property \app\modules\base\zf\components\HomeLogComponent $HomeLogComponent
 */
class HomeLogController extends Controller
{
    /**
     * 首页
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 列表
     * @param string $site_code
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionList(string $site_code)
    {
        return $this->HomeLogComponent->lists($site_code);
    }
    
    /**
     * 历史发布版本
     *
     * @param string $site_code
     * @param int    $page_id
     * @param string $pipeline
     * @param string $lang
     *
     * @return array
     */
    public function actionHistoryVersion(string $site_code, int $page_id, string $pipeline, string $lang)
    {
        return $this->HomeLogComponent->getHistoryVersion($site_code, $page_id, $pipeline, $lang);
    }
    
    /**
     * 首页回滚版本解锁
     *
     * @param string $site_code
     * @param int    $page_id
     *
     * @return array
     */
    public function actionRemoveRollbackLock(string $site_code, int $page_id)
    {
        return $this->HomeLogComponent->removeRollbackLock($site_code, $page_id);
    }
}
