<?php

namespace app\modules\activity\gb\controllers;

use yii\web\Response;

/**
 * 活动页面管理类
 * @property \app\modules\activity\gb\components\PageComponent PageComponent
 * @author wangmeng
 */
class PageController extends Controller
{
    /**
     * 获取页面信息
     * @param int $id 页面ID
     * @param string $lang 语言代码简称
     * @return array
     */
    public function actionGet($id, $lang = '')
    {
        return $this->PageComponent->get($id, $lang);
    }

    /**
     * 活动下页面列表
     * @param int $activity_id 活动ID
     * @return array
     */
    public function actionList()
    {
        return $this->PageComponent->lists(app()->request->get());
    }

    /**
     * 三端合一, 新增子页面
     *
     * @return array
     * @since v1.4.0
     */
    public function actionAdd()
    {
        $params = app()->request->post();
        return $this->PageComponent->groupAdd($params);
    }

    /**
     * 批量编辑页面属性
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionBatchEdit()
    {
        $params = app()->request->post();
        return $this->PageComponent->groupEdit($params);
    }

    /**
     * 删除页面
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete()
    {
        return $this->PageComponent->delete(app()->request->get('id'));
    }
    
    /**
     * 批量删除页面
     *
     * @return array
     */
    public function actionBatchDelete()
    {
        return $this->PageComponent->batchDelete(app()->request->post('ids'));
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
     * 页面刷新
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionRefresh()
    {
        return $this->PageComponent->refresh((int)app()->request->post('id'));
    }

    /**
     * 发布文件差异对比记录列表
     * @param int $id 页面ID
     * @param string $lang 语言代码简称
     * @return array
     */
    public function actionDiffList($id, $lang)
    {
        return $this->PageComponent->diffList($id, $lang);
    }

    /**
     * 查询文件差异详情
     * @param int $id 差异记录ID
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionDiffInfo($id)
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->PageComponent->diffInfo($id);
    }

    /**
     * 查询
     * @return array
     */
    public function actionRefreshList()
    {
        return $this->PageComponent->refreshList();
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
        //有更新的头尾部数据状态
        define('OBSERVER', 0);
        return $this->PageComponent->refreshSitePage(
            trim(app()->request->post('site_code', '')),
            trim(app()->request->post('logId', 0)),
            trim(app()->request->post('pageIds', ''))
        );
    }

    /**
     * 查询一键更新站点头尾结果日志
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionRefreshSiteLogList()
    {
        return $this->PageComponent->refreshSiteLogList(trim(app()->request->get('site_code', '')));
    }

    /**
     * 查询一键更新站点头尾结果日志
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionRefreshSiteLogDetail()
    {
        return $this->PageComponent->refreshSiteLogDetail(
            app()->request->get('id', 0),
            app()->request->get('status', 0)
        );
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
     * 获取页面所有渠道最新访问链接地址
     * @throws \ego\base\JsonResponseException
     */
    public function actionGetPageNewestUrlList()
    {
        return $this->PageComponent->getPageNewestUrlList(
            app()->request->get('id', 0),
            app()->request->get('btn', 1)
        );
    }
    
    /**
     * 获取渠道语言列表
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionPipelineList()
    {
        return $this->PageComponent->getPipelineList(app()->request->get('group_id'));
    }
}
