<?php

namespace app\modules\activity\zf\controllers;

use yii\web\Response;

/**
 * 活动页面管理类
 *
 * @property \app\modules\activity\zf\components\PageComponent PageComponent
 * @author wangmeng
 */
class PageController extends Controller
{
    /**
     * 获取页面信息
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
     * 活动下页面列表
     *
     * @param int $activity_id 活动ID
     *
     * @return array
     */
    public function actionList($activity_id)
    {
        return $this->PageComponent->lists($activity_id);
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
     *
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
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelete()
    {
        return $this->PageComponent->delete(app()->request->get('id'));
    }

    /**
     * 页面审核(verify_status可为4/5/7/8)
     *
     * @return array
     * @throws \Throwable
     * @throws \Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     */
    public function actionVerify()
    {
        return $this->PageComponent->batchVerify(
            (int) app()->request->post('id'),
            (int) app()->request->post('status'),
            (string) app()->request->post('batch_data', '')
        );
    }

    /**
     * 页面刷新
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     */
    public function actionRefresh()
    {
        return $this->PageComponent->refresh((int) app()->request->post('id'));
    }

    /**
     * 发布文件差异对比记录列表
     *
     * @param int    $id   页面ID
     * @param string $lang 语言代码简称
     *
     * @return array
     */
    public function actionDiffList($id, $lang)
    {
        return $this->PageComponent->diffList($id, $lang);
    }

    /**
     * 查询文件差异详情
     *
     * @param int $id 差异记录ID
     *
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
     *
     * @return array
     */
    public function actionRefreshList()
    {
        return $this->PageComponent->refreshList();
    }

    /**
     * 一键更新站点头尾
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionRefreshSitePage()
    {
        return $this->PageComponent->refreshSitePage(
            trim(app()->request->post('site_code', '')),
            trim(app()->request->post('pipeline', '')),
            trim(app()->request->post('logId', 0)),
            trim(app()->request->post('pageIds', ''))
        );
    }

    /**
     * 解锁一键更新站点头尾
     *
     * @param string $site_code
     * @param string $pipeline
     * @param string $module
     *
     * @return array
     */
    public function actionUnlockRefreshSitePage(string $site_code, string $pipeline, string $module)
    {
        if ('all' == strtolower($pipeline)) {
            $message = [];
            foreach (config('site.zf.pipeline') as $pipeCode => $pipeName) {
                $lockKey = app()->redisKey->getRefreshSiteTaskLockKey($site_code, $module, $pipeCode);
                if (empty(app()->redis->del($lockKey))) {
                    array_push($message, $pipeCode);
                }
            }

            if (!empty($message)) {
                return app()->helper->arrayResult(1, implode(',', $message) . '解锁失败！');
            }
        } else {
            $lockKey = app()->redisKey->getRefreshSiteTaskLockKey($site_code, $module, $pipeline);
            if (empty(app()->redis->del($lockKey))) {
                return app()->helper->arrayResult(1, '解锁失败！');
            }
        }

        return app()->helper->arrayResult(0, '解锁成功');
    }

    /**
     * 查询一键更新站点头尾结果日志
     *
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
     *
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
     *
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
     *
     * @throws \ego\base\JsonResponseException
     */
    public function actionPipelineNewestUrls()
    {
        return $this->PageComponent->getPipelineNewestUrls(app()->request->get());
    }

    /**
     * 获取页面最新访问链接地址
     *
     * @throws \ego\base\JsonResponseException
     */
    public function actionDelPipelineAllPages()
    {
        return $this->PageComponent->delPipelineAllPages(app()->request->get());
    }

    /**
     * 获取页面渠道信息
     */
    public function actionPipelineInfo()
    {
        return $this->PageComponent->pipelineInfo(app()->request->get());
    }

    /**
     * 获取三端绑定日志
     *
     * @return array
     * @throws \ego\base\JsonResponseException
     * @author yuanwenguang 2019/4/1 13:58
     */
    public function actionGetOperateLogList()
    {
        return $this->PageComponent->getOperateLogList(app()->request->post());
    }

	/**
	 * 获取原生专题页面所有的deeplink链接
	 *
	 * @param string $page_id
	 * @param string $group_id
	 * @param string $site_code
	 *
	 * @return array
	 */
    public function actionNativeDeepLink(int $page_id, string $group_id, string $site_code)
    {
		return $this->PageComponent->getNativeDeepLinks($page_id, $group_id, $site_code);
    }

    /**
     * 获取H5专题页面所有的页面ID,渠道语言
     *
     * @param string $page_id
     * @param string $group_id
     * @param string $site_code
     *
     * @return array
     */
    public function actionPageIdList(string $group_id, string $site_code)
    {
        return $this->PageComponent->getWebPageIds($group_id, $site_code);
    }
}
