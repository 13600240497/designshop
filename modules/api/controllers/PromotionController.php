<?php


namespace app\modules\api\controllers;


use app\base\SitePlatform;
use app\modules\common\zf\models\PageGroupModel;
use app\modules\common\zf\models\PageLanguageModel;
use yii\helpers\ArrayHelper;

class PromotionController extends Controller
{

	/**
	 * 根据页面分组ID获取对应app页面的id和链接
	 *
	 * @return array
	 */
	public function actionGetAppUrl ()
	{
		$params = app()->request->post();
		$rules = [['content', 'required'], ['content', 'string']];
		$model = app()->validatorModel->new($rules)->load($params);
		if (false === $model->validate()) {
			return app()->helper->arrayResult(401, 'The parameter is invalid');
		}

		if (json_decode(app()->request->post('content'))) {
			$params = json_decode(app()->request->post('content'), true);

			$rules = [
				[['group_code', 'site_code', 'lang', 'pipeline'], 'required'],
				[['group_code', 'site_code', 'lang', 'pipeline'], 'string']
			];

			$model = app()->validatorModel->new($rules)->load($params);
			if (false === $model->validate()) {
				return app()->helper->arrayResult(401, 'The parameter is invalid');
			}

			if (false === SitePlatform::siteExists($params['site_code'])) {
				return app()->helper->arrayResult(401, 'Site does not exist');
			}

			if (true === SitePlatform::isPcPlatform($params['site_code'])) {
				return app()->helper->arrayResult(401, 'Platform not be pc');
			}
		}
		try {
            ges_set_site_group_code($params['site_code']);
			$pageResult = PageGroupModel::find()
				->select('page_id, platform_type')
				->where(
					[
						'page_group_id' => $params['group_code'],
						'pipeline'      => $params['pipeline']
					]
				)
				->all();

			if (!empty($pageResult)) {
				$pageResult = ArrayHelper::toArray($pageResult);
				$pageResult = array_column($pageResult, 'page_id', 'platform_type');
				$pageLanguageResult = PageLanguageModel::find()->select('page_url')
					->where(
						[
							'page_id' => $pageResult[SitePlatform::PLATFORM_TYPE_WAP],
							'lang'    => $params['lang']
						]
					)
					->one();

				if (!empty($pageLanguageResult->page_url)) {
					$siteGroupCode = SitePlatform::getSiteBySiteCode($params['site_code']);
					$wapSiteCode = SitePlatform::getWapPlatformSiteCode($siteGroupCode);
					$siteConf = app()->params['sites'][$wapSiteCode];
					$domain = $siteConf['secondary_domain'][$params['pipeline']][$params['lang']];
					$pageUrl = $domain . $pageLanguageResult->page_url;

					return app()->helper->arrayResult(
						200,
						'Success',
						[
							'app_page_id'  => $pageResult[SitePlatform::PLATFORM_TYPE_APP],
							'wap_page_url' => $pageUrl
						]
					);
				}
			}

			return app()->helper->arrayResult(200, 'Success', []);
		} catch (\Throwable $throwable) {
			return app()->helper->arrayResult(500, 'The system is busy', []);
		}
	}
}
