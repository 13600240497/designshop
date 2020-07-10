<?php

namespace app\modules\common\zf\models;

use app\models\ActiveRecord;
use app\modules\common\models\NativePageUiComponentModel;
use ego\enums\Platform;
use yii\helpers\ArrayHelper;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\base\SiteUtils;

/**
 * Page模型
 *
 * @property int                 $id
 * @property string              $group_id
 * @property string              $pid
 * @property int                 $activity_id
 * @property string              $pipeline
 * @property int                 $type
 * @property string              $lang
 * @property string              $title
 * @property string              $keywords
 * @property string              $description
 * @property string              $background_color
 * @property string              $background_image
 * @property string              $background_position
 * @property string              $background_repeat
 * @property string              $custom_css
 * @property string              $statistics_code
 * @property string              $page_url
 * @property string              $local_files
 * @property string              $s3_files
 * @property string              $site_code
 * @property int                 $is_lock
 * @property int                 $status
 * @property int                 $home_type           ;
 * @property int                 $verify_status
 * @property int                 $auto_refresh
 * @property int                 $refresh_time
 * @property int                 $end_time
 * @property int                 $is_delete
 * @property int                 $is_blog
 * @property string              $create_user
 * @property int                 $create_time
 * @property string              $update_user
 * @property int                 $update_time
 * @property string              $verify_user
 * @property int                 $verify_time
 * @property string              $default_lang
 * @property int                 $is_redirect_country 是否国家站自动跳转(0 - 不跳转； 1 - 跳转)
 * @property int                 $is_native           是否为原生专题(0 - 否 1 - 是)
 *
 * @property PageLanguageModel[] $pageLanguages
 */
class PageModel extends AbstractBaseModel
{
	//是否删除|0否
	const NOT_DELETE = 0;
	//是否删除|1是
	const IS_DELETE = 1;
	//是否加锁|1是
	const IS_LOCK = 2;
	//是否加锁|0否
	const UN_LOCK = 1;
	//首页A
	const HOME_A = 0;
	//首页B
	const HOME_B = 1;

	//是否需要自动刷新|0否
	const NOT_REFRESH = 0;
	//是否需要自动刷新|1是
	const AUTO_REFRESH = 1;

	//页面状态|1待上线
	const PAGE_STATUS_TO_BE_ONLINE = 1;
	//页面状态|2已上线
	const PAGE_STATUS_HAS_ONLINE = 2;
	//页面状态|已发布
	const PAGE_STATUS_HAS_RELEASE = 3;
	//页面状态|已下线
	const PAGE_STATUS_HAS_OFFLINE = 4;

	const HOME_PAGE_STATUS_SHOW_NAME = [1 => '草稿', 2 => '正在使用', 3 => '已发布', 4 => '已下线'];

	//页面审核状态|1未提交
	const VERIFY_STATUS_NOT_COMMIT = 1;
	//页面审核状态|2撤回提交
	const VERIFY_STATUS_RETRACT_COMMIT = 2;
	//页面审核状态|3提交上线审核
	const VERIFY_STATUS_COMMIT_TO_ONLINE_REVIEW = 3;
	//页面审核状态|4上线审核拒绝
	const VERIFY_STATUS_REFUSE_TO_ONLINE = 4;
	//页面审核状态|5上线审核通过
	const VERIFY_STATUS_PASS_TO_ONLINE = 5;
	//页面审核状态|6下线审核提交
	const VERIFY_STATUS_COMMIT_TO_OFFLINE_REVIEW = 6;
	//页面审核状态|7下线审核拒绝
	const VERIFY_STATUS_REFUSE_TO_OFFLINE = 7;
	//页面审核状态|8下线审核通过
	const VERIFY_STATUS_PASS_TO_OFFLINE = 8;
	//类型|1 PC端
	const TYPE_PC = SitePlatform::PLATFORM_TYPE_PC;
	//类型|2 Mobile端
	const TYPE_MOBILE = SitePlatform::PLATFORM_TYPE_WAP;
	//类型|3A PP端
	const TYPE_APP = SitePlatform::PLATFORM_TYPE_APP;

	/**
	 * 初始化日志配置logConfig
	 */
	public function init ()
	{
		parent::init();
		$this->logConfig['nameField'] = 'id';
	}

	/**
	 * @inheritdoc
	 */
	public function rules ()
	{
		return [
			[['activity_id'], 'required'],
			['activity_id', 'integer'],
		];
	}

	/**
	 * 将PageLanguageModel的字段加入到attributes，方便数据库查询
	 */
	public function attributes ()
	{
		//其他表字段
		$otherAttributes = [
			'lang',
			'page_id',
			'title',
			'page_id',
			'keywords',
			'description',
			'background_color',
			'background_image',
			'background_position',
			'background_repeat',
			'page_url',
			'end_url',
			'statistics_code',
			'local_files',
			's3_files',
			'site_code',
			'create_name',
			'update_name',
			'type',
			'group_id',
			'home_type'
		];

		return array_merge(parent::attributes(), $otherAttributes);
	}

	/**
	 * beforeSave
	 *
	 * @param $insert
	 *
	 * @return bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function beforeSave ($insert)
	{
		if ($insert) {
			//添加时自动插入pid字段
			$this['pid'] = md5(microtime() . random_int(0, 100));
		}

		return parent::beforeSave($insert);
	}

	/**
	 * 根据pid查询page信息
	 *
	 * @param string $pid
	 *
	 * @return null|static
	 */
	public static function getByPId ($pid)
	{
		return static::findOne([
			'pid'       => $pid,
			'is_delete' => static::NOT_DELETE
		]);
	}

	/**
	 * 根据group_id查询page信息
	 *
	 * @param string $group_id
	 * @param string $pipeline
	 *
	 * @return null|static
	 */
	public static function getByGroupId ($group_id, $pipeline)
	{
		return static::findOne([
			'group_id'  => $group_id,
			'pipeline'  => $pipeline,
			'is_delete' => static::NOT_DELETE
		]);
	}

	/**
	 * 获取有效的活动ID
	 *
	 * @param string $id
	 * @param string $site_code
	 * @return \yii\db\ActiveQuery
	 */
	public function getActiveByPageId($id, $site_code)
	{
		$res = static::findOne([
			'id'  => $id,
			'site_code' => $site_code,
			'status'    => static::PAGE_STATUS_HAS_ONLINE,
			'is_delete' => static::NOT_DELETE
		]);
		if ($res) return true;
		return false;
	}

	/**
	 * 获取页面URLs
	 *
	 * @param int    $activityId 活动ID
	 * @param int    $pageId     页面ID
	 * @param string $lang       语言代码简称
	 *
	 * @return array
	 */
	public static function getPageUrls ($activityId, $pageId, $lang)
	{
		$data = ['current' => [], 'all' => []];
		$page = static::findOne($pageId);
		if (!$page) {
			return $data;
		}

		if (!empty($activityId)) {
			$activity = ActivityModel::getActivityInfo($activityId, $page->pipeline);
			$pageList = PageLanguageModel::find()->where(['page_id' => $pageId])->asArray()->all();
			$langList = !empty($activity['langList']) ? $activity['langList'] : [];
			$siteCode = $activity['site_code'];
			$proxyDomainKey = 'secondary_domain';
		} else {
			$siteCode = $page->site_code;
			$pageList = PageLanguageModel::find()->where(['page_id' => $pageId])->asArray()->all();
			$langList = ActivityModel::getLangListByLangString(implode(',', array_column($pageList, 'lang')));
			$proxyDomainKey = 'home_secondary_domain';
		}
		$pageList = $pageList ? array_column($pageList, null, 'lang') : [];

		if (!empty($langList) && \is_array($langList)) {
			$siteConf = app()->params['sites'][$siteCode][$proxyDomainKey][$page->pipeline];
			$defaultDomain = $siteConf[$page->default_lang] ?? '';
			empty($lang) && $lang = $page->default_lang; // 为空时设置第一个语言为当前语言
			/** @var array[] $activity */
			foreach ($langList as $item) {
				$domain = $siteConf[$item['key']] ?? $defaultDomain;
				$item['url'] = isset($pageList[$item['key']])
					? $domain . $pageList[$item['key']]['page_url'] : '';
				$data['all'][] = $item;

				if ($item['key'] === $lang) {
					$data['current'] = $item;
				}
			}
		}

		return $data;
	}

	/**
	 * 根据页面ID获取活动渠道和语言信息
	 *
	 * @param int $pageId
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getActivityPipelineByPageId (int $pageId)
	{
		$data = [];
		$page = static::findOne($pageId);
		if (!$page) {
			return $data;
		}

		if (!empty($page->activity_id)) {
			$activity = ActivityModel::getActivityInfo($page->activity_id);
			$data = ActivityModel::getPipelineAndLang($activity['lang'], $activity['site_code']);
		} else {
			$pageList = PageLanguageModel::find()->alias('pl')
				->leftJoin(static::tableName() . ' as p', 'pl.page_id = p.id')
				->select('pl.lang, p.pipeline')
				->where([
					'p.group_id' => $page->group_id
				])
				->asArray()
				->all();

			if (!empty($pageList)) {
				foreach ($pageList as $item) {
					$data[$item['pipeline']][] = $item['lang'];
				}
				$data = ActivityModel::getPipelineAndLang(json_encode($data), $page->site_code);
			}
		}

		return $data;
	}

	/**
	 * 获取没有组件的页面所属渠道
	 *
	 * @param string $groupId
	 *
	 * @return array
	 */
	public static function getNoComponentPagePipeline (string $groupId)
	{
		$list = static::find()->alias('p')
			->leftJoin(PageLayoutModel::tableName() . ' as pl', 'pl.page_id = p.id')
			->select('p.pipeline')
			->where([
				'p.group_id' => $groupId,
				'pl.id'      => null
			])
			->groupBy('p.id')
			->asArray()
			->all();

		return $list ? array_column($list, 'pipeline') : [];
	}

	/**
	 * 获取有装修页面所属渠道语言列表
	 *
	 * @param string $groupId
	 *
	 * @return array
	 */
	public static function getHasDesignPageLang (string $groupId)
	{
		$pageRows = self::find()->select('id, pipeline')->where(['group_id' => $groupId])->asArray()->all();
		if (!$pageRows) {
			return [];
		}

		$pageRows = array_column($pageRows, null, 'id');
		$pageIds = array_keys($pageRows);
		$layoutRows = PageLayoutModel::find()->select('page_id, lang')
			->where(['page_id' => $pageIds])
			->groupBy('page_id, lang')
			->asArray()
			->all();

		$langList = [];
		foreach ($layoutRows as $row) {
			$pipeline = $pageRows[$row['page_id']]['pipeline'];
			!isset($langList[$pipeline]) && $langList[$pipeline] = [];
			$langList[$pipeline][] = $row['lang'];
		}

		return $langList;
	}

	/**
	 * 获取有原生装修页面所属渠道语言列表
	 *
	 * @param string $groupId
	 *
	 * @return array
	 */
	public static function getNativeHasDesignPageLang (string $groupId)
	{
		$pageRows = self::find()->select('id, pipeline')->where(['group_id' => $groupId])->asArray()->all();
		if (!$pageRows) {
			return [];
		}

		$pageRows = array_column($pageRows, null, 'id');
		$pageIds = array_keys($pageRows);
		$componentRows = NativePageUiComponentModel::find()->select('page_id, lang')
			->where(['page_id' => $pageIds])
			->groupBy('page_id, lang')
			->asArray()
			->all();

		$langList = [];
		foreach ($componentRows as $row) {
			$pipeline = $pageRows[$row['page_id']]['pipeline'];
			!isset($langList[$pipeline]) && $langList[$pipeline] = [];
			$langList[$pipeline][] = $row['lang'];
		}

		return $langList;
	}

	/**
	 * 获取页面详情
	 *
	 * @param int    $id            页面ID
	 * @param string $lang          语言代码简称
	 * @param bool   $includeDelete 是否包含已删除的，默认不包含
	 *
	 * @return array
	 */
	public static function getPageInfo ($id, $lang = '', $includeDelete = false)
	{
		$data = [];
		$query = ['id' => (int)$id];
		!$includeDelete && $query['is_delete'] = static::NOT_DELETE;
		$pageModel = static::findOne($query);

		if ($pageModel) {
			$data = ArrayHelper::toArray($pageModel);

			$params = ['page_id' => $pageModel->id];
			!empty($lang) && $params['lang'] = $lang;
			$pageLanguageModel = PageLanguageModel::find()->where($params)->all();
			$pageLanguage = ArrayHelper::toArray($pageLanguageModel);
			$langConf = app()->params['lang'];

			if (!empty($pageLanguage)) {
				foreach ($pageLanguage as &$pageLang) {
					$pageLang['lang_name'] = $langConf[$pageLang['lang']]['name'];
				}
				unset($pageLang);
			}

			$data['pageLanguages'] = $pageLanguage;
		}

		return $data;
	}

	/**
	 * 获取类似活动表中的lang存储方式
	 *
	 * @param string $groupId
	 *
	 * @return string
	 */
	public static function getPageLangAsActivityLang (string $groupId)
	{
		$list = static::find()->alias('p')
			->select('p.site_code, p.pipeline, pl.lang')
			->leftJoin(PageLanguageModel::tableName() . ' as pl', 'pl.page_id = p.id')
			->where([
				'p.group_id' => $groupId
			])
			->asArray()
			->all();
		if (empty($list)) {
			return '';
		}

		$data = [];
		foreach ($list as $item) {
			$data[$item['pipeline']][] = $item['lang'];
		}

		return $data ? json_encode(static::sortByPipeline($data, $list[0]['site_code'])) : '';
	}

	/**
	 * 根据pipeline排序
	 *
	 * @param array  $list
	 * @param string $siteCode
	 *
	 * @return array
	 */
	public static function sortByPipeline (array $list, string $siteCode)
	{
		$data = [];
		$site = explode('-', $siteCode)[0];
		$sort = array_keys(app()->params['site'][$site]['pipeline']);
		foreach ($sort as $item) {
			if (isset($list[$item])) {
				$data[$item] = $list[$item];
			}
		}

		return $data;
	}

	/**
	 * 获取页面活动信息
	 *
	 * @param int $pageId 页面ID
	 *
	 * @return array|null|\app\modules\common\models\PageModel
	 */
	public static function getPageActivityInfo ($pageId)
	{
		return static::find()->alias('p')->select('p.*, a.site_code, a.type, a.lang, a.is_lock')
			->leftJoin(ActivityModel::tableName() . ' as a', 'a.id = p.activity_id')
			->where(['p.id' => $pageId])
			->one();
	}

	/**
	 * 获取页面缓存文件列表
	 *
	 * @param int $pageId 页面ID
	 *
	 * @return array
	 */
	public static function getPageCacheFileList ($pageId)
	{
		$pageList = static::find()->alias('p')
			->select('l.*, p.activity_id')
			->leftJoin(
				PageLanguageModel::tableName() . ' as l',
				'l.page_id = p.id'
			)->where(['p.id' => $pageId])
			->orderBy('l.id DESC')
			->all();

		return $pageList ? ArrayHelper::toArray($pageList) : [];
	}

	/**
	 * 获取需要自动刷新的页面列表
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getAutoRefreshPages ()
	{
		return static::find()->alias('p')
			->select('p.*')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->where([
				'a.is_delete'    => ActivityModel::NOT_DELETE,
				'a.status'       => ActivityModel::STATUS_HAS_ONLINE,
				'p.is_delete'    => static::NOT_DELETE,
				'p.status'       => static::PAGE_STATUS_HAS_ONLINE,
				'p.auto_refresh' => static::AUTO_REFRESH
			])->asArray()
			->all();
	}

	/**
	 * 获取需要UI自动更新的页面列表
	 *
	 * @param string $pipelineCode 要单独更新的渠道编码，默认null 表示全渠道更新
	 *
	 * @return static[]
	 */
	public static function getUiAutoRefreshPages ($pipelineCode = null)
	{
		$query = static::find()->alias('p')
			->select('p.*')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->where([
				'a.is_delete' => ActivityModel::NOT_DELETE,
				'p.is_delete' => static::NOT_DELETE,
				'p.status'    => static::PAGE_STATUS_HAS_ONLINE,
			]);

		// 只更新活动页面，排除首页
		$query->andWhere(['>', 'p.activity_id', 0]);

		// 查询6个月以前创建的页面
		$time = strtotime('-6 months');
		$query->andWhere(['>', 'p.create_time', $time]);

		$query->filterWhere(['p.pipeline' => $pipelineCode]);

		return $query->orderBy(['id' => SORT_ASC])->all();
	}

	/**
	 * 获取到下线时间但还未下线的页面列表
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getNeedOfflinePages ()
	{
		return static::find()->alias('p')
			->select('p.*')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->where([
				'a.is_delete' => ActivityModel::NOT_DELETE,
				'a.status'    => ActivityModel::STATUS_HAS_ONLINE,
				'p.is_delete' => static::NOT_DELETE,
				'p.status'    => static::PAGE_STATUS_HAS_ONLINE
			])->andWhere(['>', 'p.activity_id', SiteConstants::HOME_PAGE_ACTIVITY_ID])//过滤首页页面
			->andWhere(['between', 'p.end_time', 1, time()])//0为永远不下线
			->asArray()
			->all();
	}

	/**
	 * 检查页面的所有语言项是否设置
	 *
	 * @param int $activityId 活动ID
	 * @param int $pageId     页面ID
	 *
	 * @return bool
	 */
	public static function checkAllLangSet ($activityId, $pageId)
	{
		if (!empty($activityId)) {
			$page = static::find()->alias('p')
				->select('a.lang, p.pipeline')
				->leftJoin(
					ActivityModel::tableName() . ' as a',
					'p.activity_id = a.id'
				)->where(['p.id' => $pageId])
				->asArray()
				->one();
			if (empty($page['lang']) || empty($page['pipeline'])) {
				return false;
			}

			$pipelineLang = json_decode($page['lang'], true);
			if (empty($pipelineLang) || empty($pipelineLang[$page['pipeline']])) {
				return false;
			}
			$pageCount = \count($pipelineLang[$page['pipeline']]);
			$langArr = $pipelineLang[$page['pipeline']];
		} else {
			$page = static::findOne(['id' => $pageId]);
			if (!$page || empty($page->pipeline)) {
				return false;
			}

			$siteCode = $page->site_code;
			$siteConf = app()->params['sites'][$siteCode]['secondary_domain'][$page->pipeline];
			$pageCount = \count($siteConf);
			$langArr = array_keys($siteConf);
		}

		$pageLang = PageLanguageModel::find()
			->where([
				'page_id' => $pageId,
				'lang'    => $langArr
			])->asArray()
			->all();
		$pageLangCount = $pageLang ? \count($pageLang) : 0;

		return $pageCount === $pageLangCount;
	}

	/**
	 * 根据页面ID查询有效的活动ID
	 *
	 * @param array $pageIds 页面ID数组
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getValidActivityIdsByPageIds ($pageIds)
	{
		return static::find()->alias('p')
			->select('p.id, p.activity_id, p.refresh_time')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->where([
				'a.is_delete'    => ActivityModel::NOT_DELETE,
				'a.status'       => ActivityModel::STATUS_HAS_ONLINE,
				'p.is_delete'    => static::NOT_DELETE,
				'p.status'       => static::PAGE_STATUS_HAS_ONLINE,
				'p.auto_refresh' => static::AUTO_REFRESH,
				'p.id'           => $pageIds
			])->andFilterWhere(['>', 'p.refresh_time', 0])
			->asArray()
			->all();
	}

	/**
	 * 根据页面ID查询活动ID
	 *
	 * @param array $pageIds 页面ID数组
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getActivityInfosByPageIds ($pageIds)
	{
		return static::find()->alias('p')
			->select('p.id, pl.title, p.status, p.is_delete, p.refresh_time,
                p.activity_id, a.name, a.status as activity_status, a.is_delete as activity_is_delete')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->leftJoin(
				PageLanguageModel::tableName() . ' as pl',
				'p.id = pl.page_id'
			)->where([
				'p.id' => $pageIds
			])->orderBy('p.id')
			->asArray()
			->all();
	}

	/**
	 * 根据站点siteCode查询活动ID(包含上线的和下线的)
	 *
	 * @param string $siteCode 站点siteCode
	 * @param string $pipeline 渠道编码
	 * @param array  $pageIds  页面ID，多个用英文逗号分隔
	 * @param int    $place    应用环境
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getActivityIdsBySite ($siteCode, $pipeline, $pageIds, $place, $offset, $limit)
	{
		$query = static::find()->alias('p')
			->select('l.id, l.page_id, p.pipeline, l.lang, p.activity_id, p.status, p.is_native')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->leftJoin(
				PageLanguageModel::tableName() . ' as l',
				'p.id = l.page_id'
			)->where([
				'p.is_delete' => static::NOT_DELETE,
				'p.status'    => static::PAGE_STATUS_HAS_ONLINE
			])->andFilterWhere(['p.id' => $pageIds]);

		if (SitePlatform::isAppPlatform($siteCode)) {
			$query->andWhere(['p.is_native' => 0]);
		}

		if ($place === SiteUpdateLogModel::PLACE_ACTIVITY) {
			$query->andWhere('p.activity_id > 0');
			$query->andFilterWhere([
				'a.is_delete' => ActivityModel::NOT_DELETE,
				'a.status'    => ActivityModel::STATUS_HAS_ONLINE,
				'a.site_code' => $siteCode,
				'p.pipeline'  => $pipeline
			]);
		} elseif ($place === SiteUpdateLogModel::PLACE_HOME) {
			$query->andFilterWhere([
				'p.site_code'   => $siteCode,
				'p.activity_id' => 0,
				'p.pipeline'    => $pipeline
			]);
		}

		return $query->groupBy('l.id')->offset($offset)->limit($limit)->asArray()->all();
	}

	public static function getActivityIdsCountBySite ($siteCode, $pipeline, $pageIds, $place)
	{
		$query = static::find()->alias('p')
			->select('l.id')
			->leftJoin(
				ActivityModel::tableName() . ' as a',
				'p.activity_id = a.id'
			)->leftJoin(
				PageLanguageModel::tableName() . ' as l',
				'p.id = l.page_id'
			)->where([
				'p.is_delete' => static::NOT_DELETE,
				'p.status'    => static::PAGE_STATUS_HAS_ONLINE
			])->andFilterWhere(['p.id' => $pageIds]);

		if (SitePlatform::isAppPlatform($siteCode)) {
			$query->andWhere(['p.is_native' => 0]);
		}

		if ($place === SiteUpdateLogModel::PLACE_ACTIVITY) {
			$query->andWhere('p.activity_id > 0');
			$query->andFilterWhere([
				'a.is_delete' => ActivityModel::NOT_DELETE,
				'a.status'    => ActivityModel::STATUS_HAS_ONLINE,
				'a.site_code' => $siteCode,
				'p.pipeline'  => $pipeline
			]);
		} elseif ($place === SiteUpdateLogModel::PLACE_HOME) {
			$query->andFilterWhere([
				'p.site_code'   => $siteCode,
				'p.activity_id' => 0,
				'p.pipeline'    => $pipeline
			]);
		}

		return $query->groupBy('l.id')->asArray()->count();
	}

	/**
	 * 获取当前页面所属活动
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getActivity ()
	{
		return $this->hasOne(ActivityModel::class, ['id' => 'activity_id']);
	}

	/**
	 * 获取当前页面下的页面配置
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getPageLanguages ()
	{
		return $this->hasMany(PageLanguageModel::class, ['page_id' => 'id']);
	}

	/**
	 * 通过活动ID获取子页面数据
	 *
	 * @param array $ids 活动id数组
	 *
	 * @return array
	 */
	public static function getListByActivityId ($ids)
	{
		if (empty($ids) || !\is_array($ids)) {
			return [];
		}

		return static::find()->alias('p')
			->select('p.id, p.activity_id')
			->where([
				'p.is_delete'   => self::NOT_DELETE,
				'p.activity_id' => $ids
			])->all();
	}

	/**
	 * 获取正在上线的首页id
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getHomeOnlineIds ($siteCode, $groupId)
	{
		return static::find()->select('id')
			->where([
				'activity_id' => 0,
				'home_type'   => self::HOME_A,
				'status'      => self::PAGE_STATUS_HAS_ONLINE,
				'site_code'   => $siteCode
			])
			->andWhere(['<>', 'group_id', $groupId])
			->asArray()->all();
	}

	/**
	 * 下线所有正在启用的首页
	 *
	 * @param $pageId
	 */
	public static function offlineHomeOnlinePage($siteCode, $pageId)
	{
		$page = static::findOne($pageId);
		$onlineHomePage = PageModel::getHomeOnlineIds($siteCode, $page->group_id);
		if (!empty($onlineHomePage) && is_array($onlineHomePage)) {
			$onlineIds = array_column($onlineHomePage, 'id');
			$conditions = 'id IN(' . implode($onlineIds, ',') . ')';
			static::updateAll(['status' => self::PAGE_STATUS_HAS_RELEASE], $conditions);
		}
	}

	/**
	 * 下线所有正在启用的首页
	 *
	 * @param $pageId
	 */
	public static function offlineHomeOnlinePageA ($siteCode, $pageId)
	{
		$page = static::findOne($pageId);
		$onlineHomePage = PageModel::getHomeOnlineIds($siteCode, $page->group_id);
		if (!empty($onlineHomePage) && is_array($onlineHomePage)) {
			$onlineIds = array_column($onlineHomePage, 'id');
			$conditions = 'id IN(' . implode($onlineIds, ',') . ')';
			static::updateAll(['status' => self::PAGE_STATUS_HAS_RELEASE], $conditions);
		}
	}

	/**
	 * 下线所有的AB测试页的B页
	 *
	 * @param $pageId
	 */
	public static function offlineHomeOnlinePageB ($siteCode, $pageId)
	{
		$page = static::findOne($pageId);
		$pages = static::find()->select('id')
			->where(['activity_id' => 0, 'home_type' => self::HOME_B, 'site_code' => $siteCode])
			->andWhere(['<>', 'group_id', $page->group_id])
			->asArray()->all();
		if (!empty($pages)) {
			$onlineIds = array_column($pages, 'id');
			$conditions = 'id IN(' . implode($onlineIds, ',') . ')';
			static::updateAll(['status' => self::PAGE_STATUS_HAS_RELEASE], $conditions);
		}
	}

	/**
	 * 更新新页面上线状态
	 *
	 * @param $pageId
	 * @param $status
	 */
	public static function onlineNewHomePage ($pageId, $status)
	{
		$pageModel = static::getById($pageId);
		$pageModel->status = $status;
		$pageModel->save(true);
	}

	/**
	 * 获取活动页面类型(SiteConstants::ACTIVITY_PAGE_TYPE_*)
	 *
	 * @param \app\modules\common\zf\models\PageModel $pageModel
	 *
	 * @return int
	 * @see \app\base\SiteConstants
	 */
	public static function getActivityPageType ($pageModel)
	{
		if (SiteConstants::HOME_PAGE_ACTIVITY_ID === $pageModel->activity_id) {
			return SiteConstants::ACTIVITY_PAGE_TYPE_HOME;
		}

		return SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL;
	}

	/**
	 * 获取首页线上所属的版本号
	 *
	 * @param string $siteCode
	 *
	 * @return string
	 */
	public static function getHomePageOnlineVersion (string $siteCode)
	{
		$version = '';
		$page = static::findOne([
			'activity_id' => 0,
			'site_code'   => $siteCode,
			'status'      => static::PAGE_STATUS_HAS_ONLINE
		]);
		if ($page) {
			$cache = PagePublishCacheModel::findOne([
				'page_id' => $page->id,
				'status'  => PagePublishCacheModel::STATUS_USED
			]);
			$cache && $version = $cache->version;
		}

		return $version;
	}

	/**
	 * 通过id获取site_code
	 *
	 * @param $id
	 *
	 * @return string
	 * @author yuanwenguang 2019/4/4 11:14
	 */
	public static function getSiteCodeById ($id)
	{
		$res = static::findOne($id);

		return empty($res) ? '' : $res->site_code;
	}

	/**
	 * 获取页面分组第一个渠道的状态
	 *
	 * @param string $groupId
	 *
	 * @return int|mixed
	 */
	public static function getGroupFirstPipelineStatus (string $groupId)
	{
		$result = static::find()->select('status')
			->where(['group_id' => $groupId, 'is_delete' => static::NOT_DELETE])
			->asArray()
			->orderBy('id ASC')
			->one();

		return !empty($result['status']) ? $result['status'] : 0;
	}

	/**
	 * 获取所有分组下的多语言页面
	 *
	 * @param string $groupId
	 * @param array  $pipelineList
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getLanguagePages (string $groupId, array $pipelineList = [])
	{
		return self::find()->alias('p')
			->select('l.page_id, p.site_code, p.pipeline, l.lang, p.home_type')
			->innerJoin(PageLanguageModel::tableName() . ' as l', 'p.id = l.page_id')
			->where(['l.group_id' => $groupId, 'p.is_delete' => 0])
			->andfilterWhere(['p.pipeline' => $pipelineList])
			->all();
	}

	/**
	 * 获取所有分组下的多语言原生页面
	 *
	 * @param string $groupId
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function getLanguageNativePages (string $groupId, $isNative = 1)
	{
		return self::find()->alias('p')
			->select('l.page_id, p.pipeline, l.lang')
			->innerJoin(PageLanguageModel::tableName() . ' as l', 'p.id = l.page_id')
			->where(['p.group_id' => $groupId, 'p.is_delete' => 0, 'p.is_native' => $isNative, 'p.status' => self::PAGE_STATUS_HAS_ONLINE])
			->all();
	}

	/**
	 * 根据原生页M端获取对应APP的页面ID
	 *
	 * @param int    $mPageId
	 * @param string $siteCode
	 * @param string $pipeline
	 *
	 * @return int|mixed|\yii\db\ActiveRecord
	 */
	public static function getNativeAppPageId (int $mPageId, string $siteCode, string $pipeline, $isApp = true)
	{
		$activity = self::find()->select('activity_id')
			->where(['id' => $mPageId, 'pipeline' => $pipeline, 'site_code' => $siteCode])
			->asArray()
			->one();

		if (!empty($activity['activity_id'])) {
			$activityGroup = ActivityModel::find()->select('group_id')
				->where(['id' => $activity['activity_id'], 'is_delete' => ActivityModel::NOT_DELETE])
				->asArray()
				->one();
			if (!empty($activityGroup['group_id'])) {
				$pageGroup = PageGroupModel::find()->select('page_group_id')
					->where(
						['activity_group_id' => $activityGroup['group_id'], 'page_id' => $mPageId, 'pipeline' => $pipeline]
					)
					->asArray()
					->one();
				if (!empty($pageGroup['page_group_id'])) {
					$page = PageGroupModel::find()->select('page_id')
						->where(
							['page_group_id' => $pageGroup['page_group_id'], 'pipeline' => $pipeline, 'platform_type' => ($isApp === true) ? Platform::IOS : Platform::WAP]
						)
						->asArray()
						->one();
				}
			}
		}

		return !empty($page['page_id']) ? $page['page_id'] : $mPageId;
	}

    /**
     * 获取页面组下其他平台的页面id
     *
     * @param int $pageId
     * @param string $siteCode
     * @param string $pipeline
     * @param int $platformType
     * @return int|mixed
     */
    public static function getGroupPlatformPageId (int $pageId, string $siteCode, string $pipeline, int $platformType)
    {
        $activity = self::find()->select('activity_id')
            ->where(['id' => $pageId, 'pipeline' => $pipeline, 'site_code' => $siteCode])
            ->asArray()
            ->one();

        if (!empty($activity['activity_id'])) {
            $activityGroup = ActivityModel::find()->select('group_id')
                ->where(['id' => $activity['activity_id'], 'is_delete' => ActivityModel::NOT_DELETE])
                ->asArray()
                ->one();
            if (!empty($activityGroup['group_id'])) {
                $pageGroup = PageGroupModel::find()->select('page_group_id')
                    ->where([
                        'activity_group_id' => $activityGroup['group_id'],
                        'page_id' => $pageId,
                        'pipeline' => $pipeline
                    ])
                    ->asArray()
                    ->one();
                if (!empty($pageGroup['page_group_id'])) {
                    $page = PageGroupModel::find()->select('page_id')
                        ->where([
                            'page_group_id' => $pageGroup['page_group_id'],
                            'pipeline' => $pipeline,
                            'platform_type' => $platformType
                        ])
                        ->asArray()
                        ->one();
                    if ($page) {
                        return $page['page_id'];
                    }
                }
            }
        }

        return 0;
    }
}
