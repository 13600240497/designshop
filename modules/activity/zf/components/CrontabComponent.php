<?php

namespace app\modules\activity\zf\components;


use app\modules\common\zf\components\CommonCrontabComponent;
use app\modules\common\zf\models\SiteUpdateLogModel;

/**
 * 定时任务组件
 */
class CrontabComponent extends CommonCrontabComponent
{

	/**
	 * 定时检查一键刷新头尾页任务执行情况
	 */
	public function checkRefreshTaskStatus ()
	{
		$sitePlatform = ['zf-pc', 'zf-wap', 'zf-app'];
		$exceptionSite = [];
		foreach ($sitePlatform as $siteCode) {
			$logData = SiteUpdateLogModel::getSiteCurrentDateLog($siteCode);
			if (
				!empty($logData)
				&& $logData->status != SiteUpdateLogModel::STATUS_COMPLETED
				&& (($_SERVER['REQUEST_TIME'] - $logData->create_time) > 3600)
			) {
				app()->rms->reportHeadFooterError(
					var_export(
						[
							'site_code'   => $siteCode,
							'task_id'     => $logData->id,
							'page_ids'    => $logData->page_ids,
							'create_time' => date('Y-m-d H:i:s', $logData->create_time),
							'create_user' => $logData->create_user,
							'msg'         => '一键刷新头尾页任务异常.'
						],
						true
					)
				);
				array_push($exceptionSite, $siteCode);
			}
		}

		$message = !empty($exceptionSite) ? (implode(',', $exceptionSite) . '站点一键刷新任务异常.') : '一键刷新任务执行正常';

		return app()->helper->arrayResult($this->codeSuccess, $message);
	}
}
