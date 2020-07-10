<?php

namespace app\modules\common\zf\models;

use app\models\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\base\SitePlatform;
use app\base\SiteConstants;

/**
 * Activity模型
 *
 * @property int    $id
 * @property int    $group_id
 * @property int    $type
 * @property string $site_code
 * @property string $pipeline
 * @property string $lang
 * @property string $name
 * @property string $description
 * @property int    $status
 * @property int    $verify_status
 * @property int    $is_delete
 * @property int    $is_lock
 * @property int    $is_frequently 是否常用活动(0 - 不是； 1 - 是)
 * @property int    $mold
 * @property int    $start_time
 * @property int    $end_time
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 * @property string $verify_user
 * @property int    $verify_time
 */
class ActivityModel extends AbstractBaseModel
{
    //是否删除|0否
    const NOT_DELETE = 0;
    //是否删除|1是
    const IS_DELETE = 1;

    //是否加锁|0否
    const UN_LOCK = 0;
    const IS_LOCK = 1;

    //活动类型|1PC端
    const TYPE_PC = SitePlatform::PLATFORM_TYPE_PC;
    //活动类型|2Mobile端
    const TYPE_MOBILE = SitePlatform::PLATFORM_TYPE_WAP;
    //活动类型|3APP端
    const TYPE_APP = SitePlatform::PLATFORM_TYPE_APP;

    //状态|1待上线
    const STATUS_TO_BE_ONLINE = 1;
    //状态|2已上线
    const STATUS_HAS_ONLINE = 2;
    //状态|3待下线
    const STATUS_TO_BE_OFFLINE = 3;
    //状态|4已下线
    const STATUS_HAS_OFFLINE = 4;

    //审核状态|1未提交
    const VERIFY_STATUS_NOT_COMMIT = 1;
    //审核状态|2撤回提交
    const VERIFY_STATUS_RETRACT_COMMIT = 2;
    //审核状态|3提交上线审核
    const VERIFY_STATUS_COMMIT_TO_ONLINE_REVIEW = 3;
    //审核状态|4上线审核拒绝
    const VERIFY_STATUS_REFUSE_TO_ONLINE = 4;
    //审核状态|5上线审核通过
    const VERIFY_STATUS_PASS_TO_ONLINE = 5;
    //审核状态|6下线审核提交
    const VERIFY_STATUS_COMMIT_TO_OFFLINE_REVIEW = 6;
    //审核状态|7下线审核拒绝
    const VERIFY_STATUS_REFUSE_TO_OFFLINE = 7;
    //审核状态|8下线审核通过
    const VERIFY_STATUS_PASS_TO_OFFLINE = 8;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'name';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'type',
                'in',
                'range'   => [1, 2, 3,7],
                'message' => '{attribute}只能是PC端或者wap端'
            ],
            [['group_id', 'type', 'site_code', 'pipeline', 'lang', 'name'], 'required'],
            [['start_time', 'end_time'], 'default', 'value' => 0],
            [['mold'], 'default', 'value' => SiteConstants::ACTIVITY_TYPE_SPECIAL],
            [['description'], 'default', 'value' => ''],
            [['is_frequently'], 'default', 'value' => 0],
            ['name', 'string', 'length' => [1, 100]]
        ];
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'preview',
            'create_name'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 将string类型的lang转成数组类型的
     *
     * @param string $langString lang字段对应的值
     * @param string $pipeline 单个渠道
     *
     * @return array
     */
    public static function getLangListByLangString($langString, $pipeline = '')
    {
        $langArr = app()->params['lang'];
        $langAll = [];

        if (!empty($langString)) {
            /** @var array $pipelineLangArr */
            $pipelineLangArr = json_decode($langString, true);
            if ($pipeline && !empty($pipelineLangArr[$pipeline])) {
                $langAll[$pipeline] = static::getLangInPipeline($pipeline, $langArr, $pipelineLangArr);
            }
            if (empty($pipeline) && !empty($pipelineLangArr)) {
                foreach ($pipelineLangArr as $pipelineItem => $val) {
                    $langAll[$pipelineItem] = static::getLangInPipeline($pipelineItem, $langArr, $pipelineLangArr);
                }
            }
        }

        return $langAll;
    }

    /**
     * 将string类型的lang转成数组类型的
     *
     * @param string $langString lang字段对应的值
     *
     * @return array
     */
    public static function getLangListByLangStringNotPipeline($langString)
    {
        $langArr = app()->params['lang'];
        $langList = [];

        if (!empty($langString)) {
            foreach (explode(',', $langString) as $lang) {
                if(!empty($langArr[ $lang ])){
                    $langList[] = [
                        'key'  => $lang,
                        'name' => $langArr[ $lang ]['name']
                    ];
                }

            }
        }

        return $langList;
    }

    /**
     * 根据activity表的lang字段，获取完整的渠道和语言的数组
     * @param string $activityLang
     * @param string $siteCode
     * @return array
     */
    public static function getPipelineAndLang(string $activityLang, string $siteCode)
    {
        /** @var array $activityLangArr */
        $activityLangArr = json_decode($activityLang, true);
        if (empty($activityLang) || empty($activityLangArr)) {
            return [];
        }

        $siteGroupCode = explode('-', $siteCode)[0];
        $allPipelines = app()->params['site'][ $siteGroupCode ]['pipeline'] ?? [];

        $pipelineLangList = [];
        foreach ($activityLangArr as $pipeline => $langArr) {
            $pipelineLangList[] = [
                'pipeline' => $pipeline,
                'pipeline_name' => $allPipelines[$pipeline] ?? '',
                'langList' => static::getLangByLangKey($pipeline, $langArr)
            ];
        }

        return $pipelineLangList;
    }

    /**
     * 根据语言key获取完整的lang数组
     * @param array $langs
     * @return array
     */
    private static function getLangByLangKey(string $pipeline, array $langs)
    {
        $langList = [];
        $allLangs = app()->params['lang'];
        $allDefaultLang = config('site.' . SITE_GROUP_CODE . '.pipeline_default_lang');
        if (!empty($langs)) {
            foreach ($langs as $lang) {
                $langList[] = [
                    'key' => $lang,
                    'name' => $allLangs[$lang]['name'],
                    'is_default' => empty($allDefaultLang[$pipeline]) ? 1 : intval($allDefaultLang[$pipeline] == $lang)
                ];
            }
        }

        return $langList;
    }

    /**
     * 获取渠道下的语言列表
     * @param string $pipeline
     * @param array $langArr
     * @param array[] $pipelineLangArr
     * @return array
     */
    private static function getLangInPipeline($pipeline, $langArr, $pipelineLangArr)
    {
        $langList = [];
        foreach ($pipelineLangArr[$pipeline] as $lang) {
            if (!empty($langArr[ $lang ])) {
                $langList[] = [
                    'key'  => $lang,
                    'name' => $langArr[ $lang ]['name']
                ];
            }
        }

        return $langList;
    }

    /**
     * 根据活动ID获取lang列表
     *
     * @param int $activityId 活动ID
     *
     * @return array
     */
    public static function getLangListByActivityId($activityId)
    {
        $model = static::findOne([
            'id'        => (int) $activityId,
            'is_delete' => static::NOT_DELETE
        ]);

        $langList = [];
        if ($model) {
            $data = ArrayHelper::toArray($model);
            $langList = static::getLangListByLangString($data['lang']);
        }

        return $langList;
    }

	/**
	 * 获取原生页面活动详情
	 *
	 * @param        $activityId
	 * @param string $groupId
	 * @param string $pipeline
	 *
	 * @return array
	 */
    public static function getNativeActivityInfo($activityId, $groupId = '', $pipeline = '')
    {
        $model = static::findOne([
            'id'        => (int) $activityId,
            'is_delete' => static::NOT_DELETE
        ]);
        $data = [];

        if ($model) {
            $data = ArrayHelper::toArray($model);
            $pagePipeline = PageModel::find()
	            ->select('pipeline')
	            ->where(['is_delete' => PageModel::NOT_DELETE, 'group_id' => $groupId])
	            ->asArray()
	            ->all();
            $pagePipeline = !empty($pagePipeline) ? array_column($pagePipeline, 'pipeline') : [];
	        $data['lang'] = json_decode($data['lang'], true);
            $data['lang'] = array_filter($data['lang'], function ($key) use ($pagePipeline) {
            	return in_array($key, $pagePipeline);
            }, ARRAY_FILTER_USE_KEY);
            $data['lang'] = json_encode($data['lang']);
            $data['langList'] = static::getLangListByLangString($data['lang'], $pipeline);
            if (!empty($pipeline) && !empty($data['langList'][$pipeline])) {
                $data['langList'] = $data['langList'][$pipeline];
            }
        }

        return $data;
    }

	/**
	 * 获取活动详情
	 *
	 * @param int $activityId 活动ID
	 * @param string $pipeline 渠道编码
	 *
	 * @return array
	 */
	public static function getActivityInfo($activityId, $pipeline = '')
	{
		$model = static::findOne([
			'id'        => (int) $activityId,
			'is_delete' => static::NOT_DELETE
		]);
		$data = [];

		if ($model) {
			$data = ArrayHelper::toArray($model);
			$data['langList'] = static::getLangListByLangString($data['lang'], $pipeline);
			if (!empty($pipeline) && !empty($data['langList'][$pipeline])) {
				$data['langList'] = $data['langList'][$pipeline];
			}
		}

		return $data;
	}

    /**
     * 判断活动是否有效（未删除未过期未下线）
     *
     * @param      int              id 活动ID
     * @param bool $checkToBeOnline 是否检查“待上线”状态
     *
     * @return bool|array
     */
    public static function isEnabled($id, $checkToBeOnline = false)
    {
        $id = (int) $id;
        $message = '';
        if ($id <= 0) {
            return app()->helper->arrayResult(1, 'id错误');
        }

        $model = static::findOne([
            'id'        => $id,
            'is_delete' => static::NOT_DELETE,
        ]);
        if (!$model) {
            $message = '无效的活动ID';
        }

        if (empty($message)) {
            return true;
        }

        return app()->helper->arrayResult(1, $message);
    }

    /**
     * 检查活动结束时间是否已小于等于当前时间
     *
     * @param $id
     *
     * @return array
     */
    public static function isTimeUp($id)
    {
        $id = (int) $id;
        $message = '';
        if ($id <= 0) {
            return app()->helper->arrayResult(1, 'id错误');
        }

        return app()->helper->arrayResult(1, $message);
    }

    /**
     * 根据页面ID查询活动信息
     *
     * @param $pageId
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getActivityByPageId($pageId)
    {
        return PageModel::find()->alias('p')->select('a.*')
            ->leftJoin(static::tableName() . ' as a', 'a.id = p.activity_id')
            ->where(['p.id' => $pageId])
            ->one();
    }

    /**
     * 获取分组下所有端口活动列表
     * @param int $groupId 活动分组ID
     * @return array
     */
    public static function getActivitiesByGroupId($groupId)
    {
        return static::find()->where(['group_id' => $groupId])->all();
    }

    /**
     * 检查URL标题是否重复
     *
     * @param int    $activityId 活动ID
     * @param int    $pageId     页面ID
     * @param string $urlName    url标题
     * @param string $lang       语言简码
     * @param string $pipelineCode 渠道简码
     *
     * @return bool
     */
    public static function checkUrlNameExist($activityId, $pageId, $urlName, $lang, $pipelineCode)
    {
        $activity = static::findOne($activityId);

        if (!$activity) {
            return false;
        }

        $page = PageModel::find()->alias('p')->select('p.id')
            ->leftJoin(static::tableName() . ' as a', 'p.activity_id = a.id')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id = p.id')
            ->where([
                'a.is_delete' => static::NOT_DELETE,
                'a.site_code' => $activity->site_code,
                'p.pipeline' => $pipelineCode,
                'l.lang'  => $lang,
                'l.url_name'  => $urlName,
                'p.is_delete' => PageModel::NOT_DELETE
            ])->asArray()->one();

        return !($page && (($pageId && $pageId !== (int) $page['id']) || !$pageId));
    }

    /**
     * 当前活动下所拥有的页面
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(PageModel::class, ['activity_id' => 'id']);
    }

    /**
     * 活动上/下线操作
     *
     * @param int  $activityId
     * @param int  $status
     * @param bool $runValidation
     *
     * @return false|int
     * @throws \Throwable
     * @throws \Exception
     */
    public static function changeOnlineActivity($activityId, $status, $runValidation = true)
    {
        $model = self::getById($activityId);
        $model->status = $status;

        return $model->update($runValidation);
    }

    /**
     * 获取活动支持渠道列表
     * @param string $modelLang 活动model里的lang字段内容
     * @return array
     */
    public static function getSupportPipelineList($modelLang) {
        return empty($modelLang) ? [] : json_decode($modelLang, true);
    }

    /**
     * 检查活动是否加锁，并判断权限
     *
     * @param object|array $activity
     *
     * @return bool
     */
    public static function checkAuth($activity)
    {
        if (
            \is_object($activity)
            && 1 === (int)$activity->is_lock
            && app()->user->admin->is_super < 1
            && app()->user->admin->username !== $activity->create_user
        ) {
            return false;
        }

        if (
            \is_array($activity)
            && 1 === (int)$activity['is_lock']
            && app()->user->admin->is_super < 1
            && app()->user->admin->username !== $activity['create_user']
        ) {
            return false;
        }

        return true;
    }
}
