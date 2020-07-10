<?php


namespace app\modules\common\models;


use app\base\SitePlatform;
use app\models\ActiveRecord;
use app\modules\base\models\AdminModel;

/**
 * NativePageTpl模型
 *
 * @property int $id
 * @property string $name
 * @property string $lang
 * @property string $pid
 * @property string $tpl_type
 * @property string $pic
 * @property string $site_code
 * @property string $pipeline
 * @property string $layout_data
 * @property string $ui_data
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 */
class NativePageTplModel extends ActiveRecord
{
	protected $query;

	public function __construct ($config = [])
	{
		$this->query = self::find();
	}

	public static function tableName ()
	{
		return 'native_page_template';
	}

	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		return [
			'id',
			'pid',
			'name',
			'pic',
			'site_code',
			'lang',
			'pipeline',
			'layout_data',
			'ui_data',
			'tpl_type',
			'is_delete',
			'create_user',
			'create_time',
			'update_user',
			'update_time',
			'is_default',
			'real_name',      //真实姓名
			'pipeline_name',  //渠道名称
			'opt'             //编辑、删除操作权限， 1-有权限；0-无权限
		];
	}

	//获取模板信息
	public function getInfoByName($name)
	{
		return $this->query->where(['name' => $name, 'is_delete' => 0])->one();
	}

	/**
	 * 模板页面列表数据
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function getTplList($params)
	{
		$this->query->where('p.is_delete = 0');
		if (!empty($params['lang'])) {
			$this->query->andWhere(['p.lang' => $params['lang']]);
		}
		if (!empty($params['pipeline'])) {
			$this->query->andWhere(['p.pipeline' => $params['pipeline']]);
		}
		if (!empty($params['create_name'])) {
			$this->query->andWhere(['u.realname' => $params['create_name']]);
		}

		switch ($params['type']) {
			case 1://公有模板
				$this->query->andWhere(['p.tpl_type' => (int)$params['type']]);
				break;
			case 2://私有模板
				$this->query->andWhere(['p.tpl_type' => (int)$params['type'], 'p.create_user' => app()->user->username]);
				break;
			default://所有类型
				$this->query->andWhere('(p.tpl_type=1 or (p.tpl_type=2 and p.create_user="'.app()->user->username.'"))');
				break;
		}

		if (!empty($params['name'])) {
			$this->query->andWhere('p.name like "%' . $params['name'] . '%"');
		}

		$this->query->leftJoin(AdminModel::tableName() .' as u', 'u.username = p.create_user');

		$count = $this->query->alias('p')->count();
		if (0 === $count) {
			return array();
		}

		$pageNo = !empty($params['pageNo']) ? $params['pageNo'] : 1;
		$pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 20;

		$list = $this->query->alias('p')
			->select('p.id,p.pid,p.name,p.pic,p.site_code,p.pipeline,p.lang,p.tpl_type,p.is_default,p.create_user,p.create_time,p.update_time')
			->orderBy('p.update_time desc')
			->offset(($pageNo - 1) * $pageSize)
			->limit($pageSize)
			->all();
		return array(
			'totalCount' => (int)$count,
			'pageSize' => $pageSize,
			'pageNo' => $pageNo,
			'list' => $list
		);
	}
}
