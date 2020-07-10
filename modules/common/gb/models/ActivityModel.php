<?php

namespace app\modules\common\gb\models;

use yii\helpers\ArrayHelper;
use app\base\SitePlatform;

/**
 * Activity模型
 *
 * @property int    $id
 * @property int    $group_id
 * @property int    $type
 * @property string $site_code
 * @property string $lang
 * @property string $name
 * @property string $description
 * @property int    $status
 * @property int    $verify_status
 * @property int    $is_delete
 * @property int    $is_lock
 * @property int    $start_time
 * @property int    $end_time
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 * @property string $verify_user
 * @property int    $verify_time
 */
class ActivityModel extends BaseModel
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
    //活动类型|4 IOS端
    const TYPE_IOS = SitePlatform::PLATFORM_TYPE_IOS;
    //活动类型|5 IPAD
    const TYPE_IPAD = SitePlatform::PLATFORM_TYPE_IPAD;
    //活动类型|6 ANDROID
    const TYPE_ANDROID= SitePlatform::PLATFORM_TYPE_ANDROID;

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
                'range'   => [1, 2, 3, 4, 5, 6],
                'message' => '{attribute}只能是PC端或者wap端'
            ],
            [['group_id', 'type', 'site_code', 'pipeline', 'name'], 'required'],
            [['start_time', 'end_time'], 'default', 'value' => 0],
            [['description'], 'default', 'value' => ''],
            ['name', 'string', 'length' => [1, 50]],
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
            'create_name',
            'lang_list',
            'special_id'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 将string类型的lang转成数组类型的（过滤不支持渠道和语言）
     * @param string $siteCode  站点
     * @param string $pipelineString lang字段对应的值
     * @param string $pipelineCode  需要获取的渠道
     * @return array
     */
    public static function getPipelineListByPipelineStringPass($siteCode,$pipelineString, $pipelineCode='')
    {
        $langArr = app()->params['lang'];
        $pipeline = [];
        $site = SitePlatform::getSiteBySiteCode($siteCode);
        $configAllPipeline = app()->params['soa'][$site]['pipeline'] ?? [];
        $configSitePipeline = app()->params['sites'][ $siteCode ][ 'secondary_domain' ] ?? [];
        $pipelineList = json_decode($pipelineString,true);
        if (!empty($pipelineList) && is_array($pipelineList)) {
            foreach($pipelineList as $key=>$row){
                if(!isset($configSitePipeline[$key]) || (!empty($pipelineCode) && $key != $pipelineCode) ){
                    continue;
                }
                $langList = [];
                foreach ($row as $lang) {
                    if(!empty($langArr[ $lang ]) && isset($configSitePipeline[$key][$lang])){
                        $langList[] = [
                            'key'  => $lang,
                            'name' => $langArr[ $lang ]['name']
                        ];
                    }
                }
                $pipeline[] = [
                    'name'      =>  $configAllPipeline[$key] ?? '',
                    'key'       => $key,
                    'langList'  => $langList,
                ];
            }
        }
        return $pipeline;
    }
    /**
     * 将string类型的lang转成数组类型的
     * @param string $siteCode  站点
     * @param string $pipelineString lang字段对应的值
     *
     * @return array
     */
    public static function getPipelineListByPipelineString($siteCode,$pipelineString)
    {
        $langArr = app()->params['lang'];
        $pipeline = [];
        $site = SitePlatform::getSiteBySiteCode($siteCode);
        $configAllPipeline = app()->params['soa'][$site]['pipeline'] ?? [];
        $pipelineList = json_decode($pipelineString,true);
        if (!empty($pipelineList) && is_array($pipelineList)) {
            foreach($pipelineList as $key=>$row){
                $langList = [];
                foreach ($row as $lang) {
                    if(!empty($langArr[ $lang ])){
                        $langList[] = [
                            'key'  => $lang,
                            'name' => $langArr[ $lang ]['name']
                        ];
                    }
                }
                $pipeline[] = [
                    'name'      =>  $configAllPipeline[$key] ?? '',
                    'key'       => $key,
                    'langList'  => $langList,
                ];
            }
        }
        return $pipeline;
    }
    /**
     * 将string类型的lang转成数组类型的
     *
     * @param string $langString lang字段对应的值
     *
     * @return array
     */
    public static function getLangListByLangString($langString)
    {
        $langArr = app()->params['lang'];
        $langList = [];
        $langString = is_array($langString) ? $langString : explode(',', $langString);
        if (!empty($langString)) {
            foreach ($langString as $lang) {
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
     * 获取活动详情
     *
     * @param int $activityId 活动ID
     *
     * @return array
     */
    public static function getActivityInfo($activityId)
    {
        $model = static::findOne([
            'id'        => (int) $activityId,
            'is_delete' => static::NOT_DELETE
        ]);
        $data = [];

        if ($model) {
            $data = ArrayHelper::toArray($model);
            $groupActivity = ActivityGroupModel::find()->where(['id'=>$model->group_id])->asArray()->one();
            $data['langList'] = static::getPipelineListByPipelineStringPass($data['site_code'], $groupActivity['lang_list']);
        }

        return $data;
    }

    /**
     * 判断活动是否有效（未删除未过期未下线）
     *
     * @param      int              $id 活动ID
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
     *
     * @return bool
     */
    public static function checkUrlNameExist($activityId, $pageId, $urlName, $lang, $pipeline)
    {
        $activity = static::findOne($activityId);

        if (!$activity || !$pageId) {
            return false;
        }

        $page = PageModel::find()->alias('p')->select('p.id')
            ->leftJoin(static::tableName() . ' as a', 'p.activity_id = a.id')
            ->leftJoin(PageLanguageModel::tableName() . ' as l', 'l.page_id = p.id')
            ->where([
                'a.is_delete' => static::NOT_DELETE,
                'a.site_code' => $activity->site_code,
                'l.lang'  => $lang,
                'l.url_name'  => $urlName,
                'p.pipeline'  => $pipeline,
                'p.is_delete' => PageModel::NOT_DELETE
            ])->asArray()->one();
        
        return !(empty($page) && ($pageId !== (int) $page['id']));
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

    /**
     * 获取站点渠道下的可用语言
     * @param string $siteCode  站点
     * @param string $pipelineString lang字段对应的值
     * @param string $pipeline
     * @return array
     */
    public static function getSitePipelineValidLang($siteCode, $pipelineString, $pipeline)
    {
        $langArr = app()->params['lang'];
        $langList = [];
        $configAllPipeline = app()->params['sites'][ $siteCode ][ 'secondary_domain' ] ?? [];
        $pipelineList = json_decode($pipelineString,true);
        if (!empty($pipelineList) && is_array($pipelineList)) {
            foreach($pipelineList as $key=>$row){
                if($key == $pipeline && isset($configAllPipeline[$key])){
                    foreach ($row as $lang) {
                        if(!empty($langArr[ $lang ]) && isset($configAllPipeline[$key][$lang])){
                            $langList[] = [
                                'key'  => $lang,
                                'name' => $langArr[ $lang ]['name']
                            ];
                        }
                    }
                }
            }
        }
        return $langList;
    }
}
