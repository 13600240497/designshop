<?php


namespace app\modules\common\models;


use app\models\ActiveRecord;
use app\modules\component\models\UiModel;
use yii\db\Exception;
use yii\db\Expression;
use app\modules\component\models\UiTplModel;
use yii\helpers\ArrayHelper;

/**
 * Class NativePageUiComponentModel
 *
 * @property int    $id
 * @property int    $component_id
 * @property int    $page_id
 * @property string $component_key
 * @property string $lang
 * @property int    $tpl_id
 * @property string $tpl_title
 * @property string $tpl_name
 * @property string $style_data
 * @property string $sku_data
 * @property string $setting_data
 * @property string $async_data_format
 *
 * @package app\modules\common\models
 */
class NativePageUiComponentModel extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName ()
	{
		return 'native_page_ui_component';
	}

	/**
	 * 保存表单内容
	 *
	 * @param array $data
	 */
	public static function saveFormData (array $data)
	{
		self::deleteAll(['page_id' => $data['page_id'], 'lang' => $data['lang']]);
		if (!empty($data['data']) && is_array($data['data'])) {
			foreach ($data['data'] as $item) {
				$model = new self();
				$model->page_id = $data['page_id'];
				$model->lang = $data['lang'];
				$model->component_id = $item['id'];
				$model->component_key = $item['component_key'];
				$model->tpl_id = $item['template_id'] ?? 0;
				$model->tpl_title = !empty($item['template_title']) ? $item['template_title'] : '';
				$model->tpl_name = !empty($item['template_name']) ? $item['template_name'] : '';
				$model->style_data = !empty($item['style']) ? (is_array($item['style']) ? json_encode($item['style']) : $item['style']) : '';
				$model->sku_data = !empty($item['goodsSKU']) ? (is_array($item['goodsSKU']) ? json_encode($item['goodsSKU']) : $item['goodsSKU']) : '';
				$model->setting_data = !empty($item['data']) ? (is_array($item['data']) ? json_encode($item['data']) : $item['data']) : '';
				$model->async_data_format = '';

				$model->save();
			}
		}
	}

	/**
	 * 取组件内容
	 *
	 * @param int    $pageId
	 * @param string $lang
	 * @param array  $componentId
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getComponentsData (int $pageId, string $lang, $componentId)
	{
		if (!empty($componentId)) {
			$result = self::find()->alias('pu')
				->select('pu.page_id, pu.component_id, pu.component_key, pu.style_data, pu.sku_data, pu.setting_data, pu.tpl_id, pu.tpl_title, pu.tpl_name, u.name as component_name, u.need_navigate')
				->leftJoin(UiModel::tableName() . ' AS u', 'pu.component_key = u.component_key')
				->where(['pu.page_id' => $pageId, 'pu.lang' => $lang])
				->orderBy(new Expression("FIND_IN_SET(pu.component_id, '" . implode(',', $componentId) . "')"))
				->asArray()
				->all();
		}

		return !empty($result) ? $result : [];
	}

	/**
	 * 复制原生页面商品数据
	 *
	 * @param array $uiEqualList
	 *
	 * @return bool
	 */
	public static function copySku (array $uiEqualList)
	{
		foreach ($uiEqualList as $ui) {
			$pageUiModels = self::find()->where(['id' => [$ui['fromId'], $ui['toId']]])->indexBy('id')->all();
			$toPageModel = $pageUiModels[$ui['toId']];
			if (!empty($pageUiModels[$ui['fromId']]->sku_data)) {
				$fromSkuData = json_decode($pageUiModels[$ui['fromId']]->sku_data, true);
				$fromSku = current($fromSkuData)['sku'];
			}
			if (!empty($toPageModel->sku_data) && !empty($pageUiModels[$ui['fromId']]->sku_data)) {
				$toSkuData = json_decode($toPageModel->sku_data, true);
				$toSkuData[0]['sku'] = $fromSku;
				$toPageModel->sku_data = json_encode($toSkuData);
			} else {
				$toPageModel->sku_data = $pageUiModels[$ui['fromId']]->sku_data;
			}
			$toPageModel->async_data_format = $pageUiModels[$ui['fromId']]->async_data_format;
			if (!$toPageModel->save(false)) {
				return $toPageModel->flattenErrors(', ');
			}
			unset($toPageModel, $pageUiModels);
		}

		return true;
	}
}
