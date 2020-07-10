<?php

namespace app\modules\home\dl\controllers;

use yii\web\Response;

/**
 * 首页管理类
 *
 * @property \app\modules\home\dl\components\PageComponent PageComponent
 * @author wangmeng
 */
class PageController extends Controller
{
    /**
     * 首页装修列表
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 获取首页信息
     *
     * @param int    $id   页面ID
     * @param string $lang 语言代码简称
     *
     * @return array
     */
    public function actionGet($id, $lang = '')
    {
        return $this->PageComponent->get($id, $lang);
    }

    /**
     * 首页列表
     * @return array
     */
    public function actionList()
    {
        $params = app()->request->get();
        $rules = [
            [['pageNo', 'pageSize'], 'integer'],
            ['keywords', 'string']
        ];
        $model = app()->validatorModel->new($rules)->load($params);
        if (false === $model->validate()) {
            return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
        }

        return $this->PageComponent->lists($params);
    }

    /**
     * 三端合一, 新增首页
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @since v1.4.0
     */
    public function actionAdd()
    {
        return $this->PageComponent->multiPlatformAdd(app()->request->post());
    }

    /**
     * 批量编辑页面属性
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionBatchEdit()
    {
        return $this->PageComponent->batchEdit(app()->request->post());
    }

    /**
     * 删除页面
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete(int $id)
    {
        return $this->PageComponent->delete($id);
    }

    /**
     * 页面审核(verify_status可为4/5/7/8)
     * @return array
     * @throws \Throwable
     * @throws \Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     */
    public function actionVerify()
    {
        return $this->PageComponent->verify(
            (int)app()->request->post('id'),
            (int)app()->request->post('status')
        );
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
    public function actionLock(int $id)
    {
        return $this->PageComponent->lock($id);
    }

    /**
     * 获取页面最新访问链接地址
     * @throws \ego\base\JsonResponseException
     */
    public function actionGetPageNewestUrls()
    {
        return $this->PageComponent->getPageNewestUrls(
            app()->request->get('id', 0),
            app()->request->get('btn', 1)
        );
    }

    /**
     * 获取页面最新访问链接地址
     * @throws \ego\base\JsonResponseException
     */
    public function actionGetHomebNewestUrls()
    {
        return $this->PageComponent->getHomebNewestUrls(
            app()->request->get('id', 0),
            app()->request->get('btn', 1)
        );
    }

    /**
     * 一键更新站点头尾
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionRefreshSitePage()
    {
        throw new \ego\base\JsonResponseException(1, '推送服务升级中...');
        //有更新的头尾部数据状态
        define('OBSERVER', 0);
        return $this->PageComponent->refreshSitePage(
            trim(app()->request->post('site_code', '')),
            trim(app()->request->post('logId', 0)),
            trim(app()->request->post('pageIds', ''))
        );
    }

    /**
     * 查看缓存记录
     * @param int $page_id
     * @param string $version
     * @param string $lang
     * @return string
     * @throws \yii\base\ViewNotFoundException
     */
    public function actionViewVersion(int $page_id, string $version, string $lang)
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->PageComponent->viewVersion($page_id, $version, $lang);
    }

    /**
     * 首页版本回滚
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\ViewNotFoundException
     */
    public function actionRollback()
    {
        return $this->PageComponent->rollback(app()->request->post());
    }
}
