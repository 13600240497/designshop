<?php
namespace app\common\dal\model\zf;

use app\modules\common\zf\models\ActivityModel;
use yii\helpers\ArrayHelper;
use app\base\SitePlatform;
use app\base\SiteConstants;

/**
 * Page模型
 *
 * @property int $id
 * @property string $group_id
 * @property string $pid
 * @property int    $activity_id
 * @property string $pipeline
 * @property int    $type
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
 * @property string $site_code
 * @property int    $is_lock
 * @property int    $status
 * @property int    $verify_status
 * @property int    $auto_refresh
 * @property int    $refresh_time
 * @property int    $end_time
 * @property int    $is_delete
 * @property int    $is_blog
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 * @property string $verify_user
 * @property int    $verify_time
 * @property string $default_lang
 *
 * @property ActivityModel $activity
 * @property PageLanguageModel[] $languages
 */
class PageModel extends AbstractZaFulModel
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
            'update_name',
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
     * 获取页面Model
     *
     * @param int $id
     * @param array $with
     * @return static
     */
    public static function getPageById($id, $with = [])
    {
        $query = static::find()->where(['id' => $id]);
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->one();
    }

    /**
     * 根据多ID获取页面Model列表
     *
     * @param array $ids
     * @param array $with
     * @return static[]
     */
    public static function getPageByIds($ids, $with = [])
    {
        $query = static::find()->where(['id' => $ids]);
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->all();
    }

    /**
     * 获取当前页面所属活动
     */
    public function getActivity()
    {
        return $this->hasOne(ActivityModel::class, ['id' => 'activity_id']);
    }

    /**
     * 获取当前页面下的页面配置
     */
    public function getLanguages()
    {
        return $this->hasMany(PageLanguageModel::class, ['page_id' => 'id'])->inverseOf('page');
    }
}
