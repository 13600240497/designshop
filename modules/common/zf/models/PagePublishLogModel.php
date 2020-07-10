<?php

namespace app\modules\common\zf\models;

use app\models\ActiveRecord;

/**
 * PagePublishLog模型
 *
 * @property int $id
 * @property string $version
 * @property int $log_type
 * @property int $page_id
 * @property string $lang
 * @property int $action_type
 * @property string $file_name
 * @property string $file_type
 * @property string $file_size
 * @property string $file_hash
 * @property string $local_path
 * @property string $s3_url
 * @property string $diff
 * @property int $ip
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 * @property string $rollback_user
 * @property int $rollback_time
 * @property string $online_user
 * @property int $online_time
 */
class PagePublishLogModel extends AbstractBaseModel
{
	/**
	 * 日志类型|1-缓存文件生成日志
	 */
	const LOG_TYPE_CREATE = 1;

	/**
	 * 日志类型|2-发布S3日志
	 */
	const LOG_TYPE_PUBLISH = 2;

	/**
	 * 操作类型|1-上线
	 */
	const ACTION_TYPE_ONLINE = 1;

	/**
	 * 操作类型|2-下线
	 */
	const ACTION_TYPE_OFFLINE = 2;

	/**
	 * 初始化日志配置logConfig
	 */
	public function init()
	{
		parent::init();
		$this->logConfig = false;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[[
				'version',
				'log_type',
				'page_id',
				'lang',
				'action_type',
				'file_name',
				'file_type',
				'file_size',
				'file_hash',
				'local_path'
			], 'required'],
			[['page_id', 'ip'], 'integer'],
		];
	}

	/**
	 * 将新字段加入到attributes，方便数据库查询
	 */
	public function attributes()
	{
		//其他表字段
		$otherAttributes = [
			'page_url',
			'langList',
			'title',
			'pipeline'
		];

		return array_merge(parent::attributes(), $otherAttributes);
	}

	/**
	 * 根据文件hash和类型获取文件本地存储路径
	 * @param string $hash 文件hash
	 * @param string $type 文件后缀
	 * @return string
	 */
	public static function getRelativePath($hash, $type)
	{
		$item = static::findOne([
			'file_hash' => $hash,
			'file_type' => $type
		]);

		return $item ? $item->local_path : '';
	}

	/**
	 * 批量插入
	 * @param array $list 数据
	 * @return int
	 * @throws \yii\db\Exception
	 */
	public static function insertAllData(array $list)
	{
		if (empty($list)) {
			return 0;
		}

		$columns = array_keys($list[0]);
		$data = [];
		foreach ($list as $item) {
			$data[] = array_values($item);
		}

		return parent::insertAll($columns, $data);
	}

	/**
	 * 根据记录ID查询下个记录
	 * @param int $id 本表记录ID
	 * @param array $params 记录参数
	 * @return array|null|\yii\db\ActiveRecord
	 */
	public static function getNextDiffLog($id, $params)
	{
		return static::find()->where([
			'log_type' => $params['log_type'],
			'page_id' => $params['page_id'],
			'lang' => $params['lang'],
			'file_type' => $params['file_type']
		])
			->andFilterWhere(['>', 'id', $id])
			->orderBy('id ASC')
			->one();
	}

	/**
	 * 查询页面最新文件生成记录
	 *
	 * @param int $pageId
	 * @param int $status
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getPageNewestPublishLog(int $pageId, int $status)
	{
		$actionType = ($status === PageModel::PAGE_STATUS_HAS_ONLINE || $status === PageModel::PAGE_STATUS_HAS_RELEASE)
			? self::ACTION_TYPE_ONLINE : self::ACTION_TYPE_OFFLINE;

		$query = static::find()->alias('l')->where([
			'l.log_type' => self::LOG_TYPE_CREATE,
			'l.page_id' => $pageId,
			'l.action_type' => $actionType
		]);
		$version = $query->select('max(l.version) as version')
			->groupBy('l.version')
			->orderBy('l.version desc')
			->asArray()
			->one();

		$data = [];
		if (!empty($version['version'])) {
			$data = $query->select('l.*, pl.page_url')
				->leftJoin(PageLanguageModel::tableName() . ' as pl', 'l.page_id = pl.page_id AND l.lang = pl.lang')
				->andWhere(['l.version' => $version['version']])
				->groupBy('l.page_id, l.lang, l.file_type')
				->asArray()
				->all();
		}

		return $data;
	}

	/**
	 * 查询页面最新文件生成记录
	 *
	 * @param int $pageId
	 * @param int $status
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getHomebNewestPublishLog(int $pageId, int $status)
	{
		$actionType = ($status === PageModel::PAGE_STATUS_HAS_ONLINE || $status === PageModel::PAGE_STATUS_HAS_RELEASE)
			? self::ACTION_TYPE_ONLINE : self::ACTION_TYPE_OFFLINE;

		$query = static::find()->alias('l')->where([
			'l.log_type' => self::LOG_TYPE_CREATE,
			'l.page_id' => $pageId,
			'l.action_type' => $actionType
		]);
		$version = $query->select('max(l.version) as version')
			->groupBy('l.version')
			->orderBy('l.version desc')
			->asArray()
			->one();

		$data = [];
		if (!empty($version['version'])) {
			$data = $query->select('l.*, pl.page_url')
				->leftJoin(PageLanguageModel::tableName() . ' as pl', 'l.page_id = pl.page_id AND l.lang = pl.lang')
				->andWhere(['l.version' => $version['version']])
				->groupBy('l.page_id, l.lang, l.file_type')
				->asArray()
				->all();
		}

		return $data;
	}

	/**
	 * 查询页面最新推送记录
	 *
	 * @param int $pageId
	 * @param string $version
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getPageNewestPushLog(int $pageId, string $version)
	{
		return static::find()->where([
			'log_type' => self::LOG_TYPE_PUBLISH,
			'page_id' => $pageId,
			'version' => $version
		])->asArray()->all();
	}

	/**
	 * 查询页面最新推送的html页面S3记录
	 *
	 * @param int $pageId
	 * @param string $lang
	 * @param string $fileType
	 * @return array|\yii\db\ActiveRecord|null
	 */
	public static function getPagePushS3LocalPath(int $pageId, string $lang, string $fileType = 'html')
	{
		return static::find()->select('id, local_path, s3_url, site_code, page_id')
			->where([
				'log_type' => self::LOG_TYPE_PUBLISH,
				'page_id' => $pageId,
				'lang' => $lang,
				'file_type' => $fileType
			])
			->orderBy('id DESC')
			->asArray()
			->one();
	}

	/**
	 * 获取页面当天发布的历史版本
	 *
	 * @param int $pageId
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getPageCurrentDayPushVersions(int $pageId)
	{
		return static::find()->select('id, file_name')
			->where(
				[
					"FROM_UNIXTIME(create_time, '%Y%m%d')" => date('Ymd'),
					'page_id' => $pageId,
					'file_type' => 'html',
					'log_type' => static::LOG_TYPE_PUBLISH
				]
			)->orderBy('id DESC')
			->groupBy('file_name')
			->asArray()
			->all();
	}

	/**
	 * 获取页面之前发布的历史版本
	 *
	 * @param int $pageId
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getPageBeforeDayPushVersions(int $pageId)
	{
		return static::find()->select('id, file_name')
			->where(
				[
					'page_id' => $pageId,
					'file_type' => 'html',
					'log_type' => static::LOG_TYPE_PUBLISH
				]
			)
			->andWhere('FROM_UNIXTIME(create_time, \'%Y%m%d\') !=' . date('Ymd'))
			->orderBy('id DESC')
			->groupBy(['file_name', 'FROM_UNIXTIME(create_time, \'%Y%m%d\')'])
			->limit(3)
			->asArray()
			->all();
	}

	/**
	 * 记录上线操作日志
	 *
	 * @param $logId
	 */
	public static function onlineActionLog($logId)
	{
		$logModel = static::getById($logId);
		$logModel->online_user = app()->user->username;
		$logModel->online_time = time();
		return $logModel->save(true);
	}
}
