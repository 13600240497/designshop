<?php
namespace app\modules\component\zf\components;

use Globalegrow\Gateway\Client;
use GuzzleHttp\Cookie\CookieJar;
use yii\base\Component;

/**
 * 专题页链接变更通知
 *
 * Class PageEventComponent
 *
 * @package app\modules\component\zf\components
 */
class PageEventComponent extends Component
{

	/**
	 * 通知第三方
	 *
	 * @param array $params
	 */
	public function notifyPageUrlUpdate(array $params)
	{
		$elfApi = config("sites.{$params['site_code']}.elf_notify.url");
		$cmsApi = config("sites.{$params['site_code']}.cms_notify.url");
		$client = new \GuzzleHttp\Client();
		$elfParamsData = $cmsParamsData = ['verify' => false];
		$requestData = ['content' => json_encode($params)];
		$requestData['sign'] = (new Client('', app()->params['gateway']['app_key'], app()->params['gateway']['sign']))->getRequestSign($requestData);

		if (app()->env->isPreRelease()) {
			$_domain = parse_url($elfApi)['host'];
			$cookieJar = CookieJar::fromArray(
				['staging' => 'true'],
				mb_substr($_domain, stripos($_domain, '.'))
			);
			$elfParamsData['cookies'] = $cookieJar;

			$_domain = parse_url($cmsApi)['host'];
			$cookieJar = CookieJar::fromArray(
				['staging' => 'true'],
				mb_substr($_domain, stripos($_domain, '.'))
			);
			$cmsParamsData['cookies'] = $cookieJar;
		}

		try {
			$elfParamsData['form_params'] =  $cmsParamsData['form_params'] = $requestData;
			$promises = [
				'elf'   => $client->postAsync($elfApi, $elfParamsData),
				'cms'   => $client->postAsync($cmsApi, $cmsParamsData)
			];
			// Wait on all of the requests to complete.
			$results = \GuzzleHttp\Promise\unwrap($promises);
			// ELF平台
			if (200 == $results['elf']->getStatusCode()) {
				$elfContent = $results['elf']->getBody()->getContents();
				$elfContent = json_decode($elfContent, true);
				if (200 != $elfContent['status']) {
					// 告警
					app()->rms->reportPlatformApiError('通知ELF专题页链接变更失败：' . var_export($requestData, true));
				}
			} else {
				// 告警
				app()->rms->reportPlatformApiError('通知ELF专题页链接变更失败：' . var_export($requestData, true));
			}

			// CMS平台
			if (200 == $results['cms']->getStatusCode()) {
				$cmsContent = $results['cms']->getBody()->getContents();
				$cmsContent = json_decode($cmsContent, true);
				if (200 != $cmsContent['status']) {
					// 告警
					app()->rms->reportPlatformApiError('通知ELF专题页链接变更失败：' . var_export($requestData, true));
				}
			} else {
				// 告警
				app()->rms->reportPlatformApiError('通知CMS专题页链接变更失败：' . var_export($requestData, true));
			}
		} catch (\Throwable $throwable) {
			// 告警
			app()->rms->reportPlatformApiError('通知第三方专题页链接变更失败' . var_export($requestData, true));
		}
	}
}
