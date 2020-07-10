<?php

namespace app\modules\common\dl\models;

use yii\helpers\ArrayHelper;
use app\base\SitePlatform;
use app\base\SiteConstants;
use app\base\SiteUtils;

/**
 * Page模型
 *
 * @property int $id
 * @property string $pid
 * @property int $activity_id
 * @property int $type
 * @property string $lang
 * @property string $title
 * @property string $keywords
 * @property string $description
 * @property string $background_color
 * @property string $background_image
 * @property string $background_position
 * @property string $background_repeat
 * @property string $custom_css
 * @property string $statistics_code
 * @property string $page_url
 * @property string $local_files
 * @property string $s3_files
 * @property string $url_name
 * @property string $site_code
 * @property int    $is_lock
 * @property int    $status
 * @property int    $verify_status
 * @property int    $auto_refresh
 * @property int    $refresh_time
 * @property int    $end_time
 * @property int    $is_delete
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 * @property string $verify_user
 * @property int    $verify_time
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
    //页面状态|4已下线
    const PAGE_STATUS_HAS_OFFLINE = 4;
    //页面状态|5推送中
    const PAGE_STATUS_HAS_PUSH = 5;
    //页面状态|6推送失败
    const PAGE_STATUS_HAS_PUSH_FAIL = 6;
    //页面状态|7正在使用(有更新)
    const PAGE_STATUS_HAS_ONLINE_UPDATE = 7;
    //页面状态|8正在使用(测试)/AB测试页的B
    const PAGE_STATUS_HAS_ONLINE_B = 8;

    const HOME_PAGE_STATUS_SHOW_NAME = [1 => '草稿', 2 => '正在使用', 3 => '已发布', 4 => '已下线', 5 => '更新中', 6 => '更新失败', 7 => '正在使用(有更新)', 8 => '正在使用(测试)'];

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
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id'], 'required'],
            ['activity_id', 'integer'],
        ];
    }

    /**
     * 将PageLanguageModel的字段加入到attributes，方便数据库查询
     */
    public function attributes()
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
            'type',
            'group_id'
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
    public function beforeSave($insert)
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
    public static function getByPId($pid)
    {
        return static::findOne([
            'pid' => $pid,
            'is_delete' => static::NOT_DELETE
        ]);
    }

    /**
     * 获取页面URLs
     *
     * @param int $activityId 活动ID
     * @param int $pageId 页面ID
     * @param string $lang 语言代码简称
     *
     * @return array
     */
    public static function getPageUrls($activityId, $pageId, $lang)
    {
        if (!empty($activityId)) {
            $activity = ActivityModel::getActivityInfo($activityId);
            $pageList = PageLanguageModel::find()->where(['page_id' => $pageId])->asArray()->all();
            $langList = !empty($activity['langList']) ? $activity['langList'] : [];
            $siteCode = $activity['site_code'];
        } else {
            $siteCode = static::findOne(['id' => $pageId])->getAttribute('site_code');
            $pageList = PageLanguageModel::find()->where(['page_id' => $pageId])->asArray()->all();
            $langList = ActivityModel::getLangListByLangString(implode(',', array_column($pageList, 'lang')));
        }
        $pageList = $pageList ? array_column($pageList, null, 'lang') : [];

        $data = ['current' => [], 'all' => []];

        if (!empty($langList) && is_array($langList)) {
            $proxyDomainKey = SiteUtils::isGbAdvertisementModule() ? 'ad_secondary_domain' : 'secondary_domain';
            $siteConf = app()->params['sites'][$siteCode][$proxyDomainKey];
            $defaultDomain = current($siteConf);
            empty($lang) && $lang = $langList[0]['key']; // 为空时设置第一个语言为当前语言
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
     * 获取页面详情
     *
     * @param int $id 页面ID
     * @param string $lang 语言代码简称
     * @param bool $includeDelete 是否包含已删除的，默认不包含
     *
     * @return array
     */
    public static function getPageInfo($id, $lang = '', $includeDelete = false)
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
     * 获取页面活动信息
     *
     * @param int $pageId 页面ID
     *
     * @return array|null|\app\modules\common\models\PageModel
     */
    public static function getPageActivityInfo($pageId)
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
    public static function getPageCacheFileList($pageId)
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
    public static function getAutoRefreshPages()
    {
        return static::find()->alias('p')
            ->select('p.*')
            ->leftJoin(
                ActivityModel::tableName() . ' as a',
                'p.activity_id = a.id'
            )->where([
                'a.is_delete' => ActivityModel::NOT_DELETE,
                'a.status' => ActivityModel::STATUS_HAS_ONLINE,
                'p.is_delete' => static::NOT_DELETE,
                'p.status' => static::PAGE_STATUS_HAS_ONLINE,
                'p.auto_refresh' => static::AUTO_REFRESH
            ])->asArray()
            ->all();
    }

    /**
     * 获取需要UI自动更新的页面列表
     *
     * @return static[]
     */
    public static function getUiAutoRefreshPages()
    {
        $query = static::find()->alias('p')
            ->select('p.*')
            ->leftJoin(
                ActivityModel::tableName() . ' as a',
                'p.activity_id = a.id'
            )->where([
                'a.is_delete' => ActivityModel::NOT_DELETE,
                'p.is_delete' => static::NOT_DELETE,
                'p.status' => static::PAGE_STATUS_HAS_ONLINE,
            ]);

        // 只更新活动页面，排除首页
        $query->andWhere(['>', 'p.activity_id', 0]);

        // 查询3个月以前更新过的页面
        $time = strtotime('-3 months');
        $query->andWhere(['>', 'p.update_time', $time]);

        return $query->orderBy(['id' => SORT_ASC])->all();
    }

    /**
     * 获取到下线时间但还未下线的页面列表
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getNeedOfflinePages()
    {
        return static::find()->alias('p')
            ->select('p.*')
            ->leftJoin(
                ActivityModel::tableName() . ' as a',
                'p.activity_id = a.id'
            )->where([
                'a.is_delete' => ActivityModel::NOT_DELETE,
                'a.status' => ActivityModel::STATUS_HAS_ONLINE,
                'p.is_delete' => static::NOT_DELETE,
                'p.status'    => static::PAGE_STATUS_HAS_ONLINE
            ])->andWhere(['>', 'p.activity_id', SiteConstants::HOME_PAGE_ACTIVITY_ID]) //过滤首页页面
            ->andWhere(['between', 'p.end_time', 1, time()]) //0为永远不下线
            ->asArray()
            ->all();
    }

    /**
     * 检查页面的所有语言项是否设置
     *
     * @param int $pageId 页面ID
     *
     * @return bool
     */
    public static function checkAllLangSet($activity, $pageId)
    {
        if (!empty($activity)) {
            $page = static::find()->alias('p')
                ->select('a.lang')
                ->leftJoin(
                    ActivityModel::tableName() . ' as a',
                    'p.activity_id = a.id'
                )->where(['p.id' => $pageId])
                ->asArray()
                ->one();
            if (empty($page['lang'])) {
                return false;
            }

            $pageCount = substr_count($page['lang'], ',') + 1;
            $langArr = explode(',', $page['lang']);
        } else {
            $siteCode = static::findOne(['id' => $pageId])->getAttribute('site_code');
            $siteConf = app()->params['sites'][$siteCode]['secondary_domain'];
            $pageCount = count($siteConf);
            $langArr = array_keys($siteConf);
        }

        $pageLang = PageLanguageModel::find()
            ->where([
                'page_id' => $pageId,
                'lang' => $langArr
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
    public static function getValidActivityIdsByPageIds($pageIds)
    {
        return static::find()->alias('p')
            ->select('p.id, p.activity_id, p.refresh_time')
            ->leftJoin(
                ActivityModel::tableName() . ' as a',
                'p.activity_id = a.id'
            )->where([
                'a.is_delete' => ActivityModel::NOT_DELETE,
                'a.status' => ActivityModel::STATUS_HAS_ONLINE,
                'p.is_delete' => static::NOT_DELETE,
                'p.status' => static::PAGE_STATUS_HAS_ONLINE,
                'p.auto_refresh' => static::AUTO_REFRESH,
                'p.id' => $pageIds
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
    public static function getActivityInfosByPageIds($pageIds)
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
     * @param array $pageIds 页面ID，多个用英文逗号分隔
     * @param int $place 应用环境
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getActivityIdsBySite($siteCode, $pageIds, $place, $offset, $limit)
    {
        $query = static::find()->alias('p')
            ->select('l.id,l.page_id, l.lang, p.activity_id, p.status')
	        ->leftJoin(
		        PageLanguageModel::tableName() . ' as l',
		        'p.id = l.page_id'
	        )
            ->leftJoin(
                ActivityModel::tableName() . ' as a',
                'p.activity_id = a.id'
            )->where([
                'p.is_delete' => static::NOT_DELETE,
                'p.status' => static::PAGE_STATUS_HAS_ONLINE
            ])->andFilterWhere(['p.id' => $pageIds]);

        if ($place === SiteUpdateLogModel::PLACE_ACTIVITY) {
            $query->andFilterWhere([
                'a.is_delete' => ActivityModel::NOT_DELETE,
                'a.status' => ActivityModel::STATUS_HAS_ONLINE,
                'a.site_code' => $siteCode
            ]);
        } elseif ($place === SiteUpdateLogModel::PLACE_HOME) {
            $query->andFilterWhere([
                'p.site_code' => $siteCode,
                'p.activity_id' => 0
            ]);
        }

        return $query->groupBy('l.id')->offset($offset)->limit($limit)->asArray()->all();
    }

    public static function getActivityIdsCountBySite($siteCode, $pageIds, $place)
    {
        $query = static::find()->alias('p')
            ->select('l.id')
            ->leftJoin(
                PageLanguageModel::tableName() . ' as l',
                'p.id = l.page_id'
            )->leftJoin(
                ActivityModel::tableName() . ' as a',
                'p.activity_id = a.id'
            )->where([
                'p.is_delete' => static::NOT_DELETE,
                'p.status' => static::PAGE_STATUS_HAS_ONLINE
            ])->andFilterWhere(['p.id' => $pageIds]);
    
        if ($place === SiteUpdateLogModel::PLACE_ACTIVITY) {
            $query->andFilterWhere([
                'a.is_delete' => ActivityModel::NOT_DELETE,
                'a.status' => ActivityModel::STATUS_HAS_ONLINE,
                'a.site_code' => $siteCode
            ]);
        } elseif ($place === SiteUpdateLogModel::PLACE_HOME) {
            $query->andFilterWhere([
                'p.site_code' => $siteCode,
                'p.activity_id' => 0
            ]);
        }
    
        return $query->groupBy('l.id')->asArray()->count();
    }
    
    /**
     * 获取当前页面所属活动
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(ActivityModel::class, ['id' => 'activity_id']);
    }

    /**
     * 获取当前页面下的页面配置
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPageLanguages()
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
    public static function getListByActivityId($ids)
    {
        if (empty($ids) || !\is_array($ids)) {
            return [];
        }

        return static::find()->alias('p')
            ->select('p.id, p.activity_id')
            ->where([
                'p.is_delete' => self::NOT_DELETE,
                'p.activity_id' => $ids
            ])->all();
    }

    /**
     * 获取正在上线的首页id
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getHomeOnlineIds($siteCode)
    {
        return static::find()->select('id')
            ->where([
                'activity_id' => 0,
                'status' => [
                    self::PAGE_STATUS_HAS_ONLINE,
                    self::PAGE_STATUS_HAS_ONLINE_UPDATE
                ],
                'site_code' => $siteCode
            ])
            ->asArray()->all();
    }

    /**
     * 下线所有正在启用的首页
     *
     * @param $pageId
     */
    public static function offlineHomeOnlinePage($siteCode)
    {
        $onlineHomePage = PageModel::getHomeOnlineIds($siteCode);
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
    public static function offlineHomeOnlinePageB($siteCode)
    {
        $pages = static::find()->select('id')
            ->where(['activity_id' => 0, 'status' => self::PAGE_STATUS_HAS_ONLINE_B, 'site_code' => $siteCode])
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
    public static function onlineNewHomePage($pageId, $status)
    {
        $pageModel = static::getById($pageId);
        $pageModel->status = $status;
        $pageModel->save(true);
    }

    /**
     * 获取活动页面类型(SiteConstants::ACTIVITY_PAGE_TYPE_*)
     * @param \app\modules\common\models\PageModel $pageModel
     * @return int
     * @see \app\base\SiteConstants
     */
    public static function getActivityPageType($pageModel)
    {
        if (SiteConstants::HOME_PAGE_ACTIVITY_ID === $pageModel->activity_id) {
            return SiteConstants::ACTIVITY_PAGE_TYPE_HOME;
        }
        return SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL;
    }

    /**
     * 获取首页线上所属的版本号
     * @param string $siteCode
     * @return string
     */
    public static function getHomePageOnlineVersion(string $siteCode)
    {
        $version = '';
        $page = static::findOne([
            'activity_id' => 0,
            'site_code' => $siteCode,
            'status' => static::PAGE_STATUS_HAS_ONLINE
        ]);
        if ($page) {
            $cache = PagePublishCacheModel::findOne([
                'page_id' => $page->id,
                'status' => PagePublishCacheModel::STATUS_USED
            ]);
            $cache && $version = $cache->version;
        }

        return $version;
    }
}
