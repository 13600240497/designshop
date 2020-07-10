<?php

namespace app\modules\activity\zf\controllers;

/**
 * 活动移动端页面设计管理类
 *
 * @property \app\modules\activity\zf\components\NativePageDesignComponent $NativePageDesignComponent
 *
 */
class NativeDesignController extends Controller
{
	/**
	 * 活动APP原生页面设计
	 *
	 * @param        $group_id
	 * @param        $pipeline
	 * @param string $lang
	 *
	 * @return array|string
	 */
	public function actionNativeIndex($group_id, $pipeline, $lang = '')
	{
		return $this->NativePageDesignComponent->getIndexData($group_id, $lang, $pipeline);
	}

	/**
	 * 预览原生页面
	 *
	 * @param string $pid
	 * @param string $lang
	 *
	 * @return array|string
	 */
	public function actionNativePreview(string $pid, string $lang) {
		$data = $this->NativePageDesignComponent->nativePreview($pid, $lang);
		// return $this->NativePageDesignComponent->nativePreview($pid, $lang);
		// var_dump($data['data']['languages']);
		// return $this->render('/release', $data);
		return $this->render('native-preview', $data);
	}

	/**
	 * 获取跨渠道复制原生页面的渠道配置
	 *
	 * @param int    $page_id
	 * @param string $lang
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 */
	public function actionGetNativeCopyPipeline(int $page_id, string $lang)
	{
		return $this->NativePageDesignComponent->getNativeCopyPipeline($page_id, $lang);
	}

	/**
	 * 获取装修页面编辑锁
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 */
	public function actionAcquireDesignLock()
	{
		return $this->NativePageDesignComponent->acquirePageDesignLock(app()->request->get());
	}

	/**
	 * 释放装修页面编辑锁
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 */
	public function actionReleaseDesignLock()
	{
		return $this->NativePageDesignComponent->releasePageDesignLock(app()->request->get());
	}


	/**
	 * 跨渠道复制原生页面
	 *
	 * @throws \Throwable
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\db\Exception
	 */
	public function actionCopyNativePipeline()
	{
		return $this->NativePageDesignComponent->copyNativePipeline(app()->request->post());
	}

	/**
	 * 【批量】页面发布
	 * @return array
	 * @throws \Throwable
	 */
	public function actionBatchRelease()
	{
		return $this->NativePageDesignComponent->nativeBatchRelease(
			app()->request->post('batch_data', '')
		);
	}

	/**
	 * 页面发布(保存页面)
	 * @return array
	 * @throws \Throwable
	 */
	public function actionRelease()
	{
		return $this->NativePageDesignComponent->activityRelease(
			(int)app()->request->post('page_id', 0),
			app()->request->post('lang', '')
		);
	}

	/**
	 * 新装修页的路由入口
     * @throws \Throwable
	 */
	public function actionNative($group_id, $pipeline, $lang = '')
	{
		$data = $this->NativePageDesignComponent->getNativeIndexData($group_id, $lang, $pipeline);
		$data['code'] = 0;
		return $data;
		// echo json_encode($data, true);
		// return $this->render('//layouts/vue-entry.php', $data);
	}
}
