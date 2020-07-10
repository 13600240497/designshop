<?php

namespace app\modules\common\zf\components;

use app\base\SitePlatform;
use app\modules\common\zf\models\{PageLanguageModel, PageModel};
use yii\db\Exception;

/**
 * 布局组件数据
 */
class NativePageUiComponentDataComponent extends Component
{

	/**
	 * 存储前端组件公私有字段配置信息
	 *
	 * @var array
	 */
	public static $setting;


	/**
	 * 确认切换模板
	 * 1、删除原有页面模板布局和组件数据
	 * 2、更新page表tpl_id字段
	 * 3、重新生成页面模板布局组件数据
	 *
	 * @param $params
	 *
	 * @return array
	 * @throws \yii\db\Exception
	 */
	public function confirmTpl ($params)
	{
		list($pageId, $tplId, $lang) = $params;
		if (!isset($pageId, $tplId, $lang)) {
			return app()->helper->arrayResult($this->codeFail, '参数不全');
		}

		//开启事物
		$tr = app()->db->beginTransaction();
		try {
			$pageModel = PageModel::getById($pageId);
			$nativeId = PageModel::getNativeAppPageId(
				$pageId,
				$pageModel->site_code,
				$pageModel->pipeline,
				SitePlatform::isAppPlatform($pageModel->site_code) ? false : true
			);
			$pageIds = [$pageId];
			//1、更新page表tpl_id字段
			if (!empty($nativeId)) {
				array_push($pageIds, $nativeId);
			}
			$where = 'page_id IN(' . implode(',', $pageIds) . ')'. ' AND lang="' . $lang . '"';
			$page = PageLanguageModel::find()->where($where)->one();
			if (!empty($page) && false === PageLanguageModel::updateAll(['tpl_id' => $tplId], $where)) {
				throw new Exception('模板数据切换失败-E002');
			}

			//2、重新生成页面模板布局组件数据
			$commonPageTplComponent = new NativePageTplComponent();
			if (!$commonPageTplComponent->initPageTpl($pageIds, $tplId, $lang)) {
				throw new Exception('模板数据切换失败-E003');
			}
		} catch (\Exception $e) {
			$tr->rollBack();

			return app()->helper->arrayResult($this->codeFail, $e->getMessage(), [], ['errors' => $e->getTraceAsString()]);
		}

		$tr->commit();

		return app()->helper->arrayResult($this->codeSuccess, '页面模板切换成功');
	}
}
