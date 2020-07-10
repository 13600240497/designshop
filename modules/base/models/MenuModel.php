<?php
namespace app\modules\base\models;

use app\base\SitePlatform;
use app\models\ActiveRecord;
use ego\models\UnlimitedTrait;

/**
 * Menu模型
 *
 * @property int $is_delete
 * @property int $is_public
 * @property string $route
 * @property int $status
 */
class MenuModel extends ActiveRecord
{
    use UnlimitedTrait;

    //是否删除|0否
    const NOT_DELETE = 0;
    //是否删除|1是
    const IS_DELETE = 1;

    //状态|0关闭
    const STATUS_OFF = 0;
    //状态|1开启
    const STATUS_ON = 1;

    //是否公开|0否
    const NOT_PUBLIC = 0;
    //是否公开|1是
    const IS_PUBLIC = 1;

    const ROUTE = 'route';

    //配置站点替换菜单
    static  $siteMenu = [
        'gb'=>[
            'activity/activity/index',
            'activity/crontab/push-info',
            'activity/crontab/push-page'
        ],
        'zf'=>[
            'home/page/index',
            'activity/activity/index',
            'activity/page-tpl/index',
            'activity/page-ui-tpl/index',
            'activity/crontab/push-info',
            'activity/crontab/push-page',
            'activity/crontab/clean-push',
            'base/task-log/index',
            'base/home-log/index'
        ],
        'rg'=>[
            'home/page/index',
            'activity/activity/index',
            'activity/page-tpl/index',
            'activity/page-ui-tpl/index',
            'activity/crontab/push-info',
            'activity/crontab/push-page',
            'activity/crontab/clean-push',
            'base/task-log/index',
            'base/home-log/index'
        ],
        'suk'=>[
            'home/page/index',
            'activity/activity/index',
            'activity/page-tpl/index',
            'activity/page-ui-tpl/index',
            'activity/crontab/push-info',
            'activity/crontab/push-page',
            'activity/crontab/clean-push',
            'base/task-log/index',
            'base/home-log/index'
        ],
        'dl' => [
            'home/page/index',
            'activity/activity/index',
            'activity/page-tpl/index',
            'activity/page-ui-tpl/index',
            'activity/crontab/push-info',
            'activity/crontab/push-page',
            'activity/crontab/clean-push',
            'base/task-log/index',
            'base/home-log/index'
        ]
    ];
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'name';
    }

    public function rules()
    {
        return [
            [['parent_id', 'name', static::ROUTE, 'sort'], 'required'],
            [
                ['type','status','is_public'],
                'in',
                'range' => [0, 1],
                'message' => '{attribute}只能为0和1'
            ],
            ['icon_class', 'string'],
            ['remark', 'safe'],
            ['name', 'unique'],
            ['route', 'unique'],
        ];
    }

    /**
     * 根据路由获取菜单
     * @param string $route
     * @return null|array|\app\modules\base\models\MenuModel
     */
    public static function getByRoute($route)
    {
        return static::getBy(static::ROUTE, $route);
    }

    /**
     * 验证name
     * @return bool
     */
    public function validateName()
    {
        if (($menu = static::findOne(['name' => $this->name]))
            && (!$this->id || (int)$menu->id !== (int)$this->id)
        ) {
            $this->addError('name', '菜单名称已经存在');
            return false;
        }

        return true;
    }

    /**
     * 验证route
     * @return bool
     */
    public function validateRoute()
    {
        if (($menu = static::getByRoute($this->route))
            && (!$this->id || (int)$menu->id !== (int)$this->id)
        ) {
            $this->addError(static::ROUTE, 'php路由已经存在');
            return false;
        }

        return true;
    }

    /**
     * 根据指定字段获取菜单
     * @param string $field
     * @param string $value
     * @return null|array|\yii\db\ActiveRecord
     */
    protected static function getBy($field, $value)
    {
        $item = static::find()
            ->where([$field => $value])
            ->one();

        return $item ?: null;
    }

    /**
     * 菜单列表
     * @return array
     */
    public static function menuList()
    {
        $data = self::find()
            ->select('*,sort as order')
            ->where(['is_delete' => self::NOT_DELETE])
            ->orderBy('sort, id')
            ->asArray()
            ->all();

        return $data ? self::getSiteMenuList(array_column($data, null, 'id')) : [];
    }

    /**
     * 可分配的菜单列表（排除被删除、被关闭的，以及父节点被删除、关闭的）
     * @return array
     */
    public static function allocationMenuList()
    {
        $data = self::find()
            ->select('*,sort as order')
            ->where([
                'is_delete' => self::NOT_DELETE,
                'status' => self::STATUS_ON
            ])->orderBy('sort, id')
            ->asArray()
            ->all();

        if ($data) {
            $ids = array_column($data, 'id');
            $data = array_filter($data, function ($v) use ($ids) {
                //为根节点或父节点存在的则返回
                return (int)$v['parent_id'] === 0 || \in_array($v['parent_id'], $ids, true);
            });
            $data = array_column($data, null, 'id');
        }

        return $data;
    }

    /**
     * 按站点替换菜单链接
     * @param   array   $data
     * @return  array
     */
    public static function getSiteMenuList($data)
    {
        $siteCode = SitePlatform::getCurrentSiteGroupDefaultSiteCode();
        if(empty($siteCode)){
            return $data;
        }
        $site = SitePlatform::getSiteBySiteCode($siteCode);
        if(isset(self::$siteMenu[$site])){
            $menu = self::$siteMenu[$site];
            //暂时全部使用ZF作为标准流程,之后再统一修改 12-16
            if ($site !== 'gb') $site = 'zf';
            foreach($data as &$item){
                if(in_array($item['route'],$menu)){
                    $route = explode('/',$item['route']);
                    $route[0] .= '/'.$site;
                    $item['route'] = implode('/',$route);
                }
            }
        }
        return $data;
    }

}
