<?php


namespace app\modules\common\models;


use app\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class NativePageLayoutModel
 *
 * @package app\modules\common\models
 * @property int $id
 * @property int $page_id
 * @property string $lang
 * @property string $data
 */
class NativePageLayoutModel extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'native_page_layout_component';
	}

	public static function saveFormData(array $data)
	{
		$model = self::findOne(['page_id' => $data['page_id'], 'lang' => $data['lang']]);
		if ($model) {
			$model->data = is_array($data['data']) ? json_encode($data['data']) : $data['data'];
		} else {
			$model = new self();
			$model->page_id = $data['page_id'];
			$model->lang = $data['lang'];
			$model->data = is_array($data['data']) ? json_encode($data['data']) : $data['data'];
		}

		$model->save();
	}

	/**
	 * 取页面组件
	 *
	 * @param int    $pageId
	 * @param string $lang
	 *
	 * @return array|mixed
	 */
	public static function getComponentsSort(int $pageId, string $lang)
	{
		$result = self::find()
			->select('data')
			->where(['page_id' => $pageId, 'lang' => $lang])
			->asArray()
			->one();

		return !empty($result['data']) ? json_decode($result['data'], true) : [];
	}
}
