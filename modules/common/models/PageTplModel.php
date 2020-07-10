<?php

namespace app\modules\common\models;

use app\base\SiteConstants;
use app\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * PageTplmodel模型
 *
 * @property int $id
 * @property int $place
 * @property int $platform_type
 * @property string $name
 * @property string $lang
 * @property string $pic
 * @property string $site_code
 * @property string $layout
 * @property string $layout_data
 * @property string $ui
 * @property string $ui_data
 * @property string $custom_css
 */
class PageTplModel extends ActiveRecord
{
    public $query;

    // 应用场景：活动页
    const ACTIVITY_PLACE = SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL;
    // 应用场景：首页
    const HOME_PLACE = SiteConstants::ACTIVITY_PAGE_TYPE_HOME;
    //应用场景： 推广页
    const ADVERTISEMENT_PLACR = SiteConstants::ACTIVITY_PAGE_TYPE_ADVERTISEMENT;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->query = static::find();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page_template';
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
            'range',
            'place',
            'platform_type',
            'lang',
            'custom_css',
            'layout',
            'layout_data',
            'ui',
            'ui_data',
            'tpl_type',
            'is_delete',
            'create_user',
            'create_time',
            'update_user',
            'update_time',
            'is_default',
            'real_name',      //真实姓名
            'platform_name',  //平台名称
            'opt'             //编辑、删除操作权限， 1-有权限；0-无权限
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'site_code', 'layout', 'layout_data', 'ui', 'ui_data'], 'required']
        ];
    }

    //获取模板信息
    public function getInfoByName($name)
    {
        return $this->query->where(['name' => $name, 'is_delete' => 0])->one();
    }

    /**
     * 模板列表
     * @param  array   $params  传参数组
     * ['pageNo']     int        页码
     * ['pageSize']   int        每页数量
     * ['name']       string     模板名称
     * ['lang']       string     模板语言
     * ['siteCode']   string     站点简称
     * ['type']       int        模板类型
     * ['range']      int        适用范围 1-pc， 2-wap， 3-响应式
     * ['platform_type']  int    平台类型 参考SitePlatform
     * @see \app\base\SitePlatform
     * @return array
     */
    public function getTplList($params)
    {
        $this->query->where('is_delete = 0');
        if ($params['range'] > 0) {
            $this->query->andWhere(['range' => $params['range']]);
        }
        if (!empty($params['lang'])) {
            $this->query->andWhere(['lang' => $params['lang']]);
        }
        if (!empty($params['place'])) {
            $this->query->andWhere(['place' => $params['place']]);
        }
        if (!empty($params['site_code'])) {
            $this->query->andWhere(['site_code' => $params['site_code']]);
        }
        switch ($params['type']) {
            case 1://公有模板
                $this->query->andWhere(['tpl_type' => (int)$params['type']]);
                break;
            case 2://私有模板
                $this->query->andWhere(['tpl_type' => (int)$params['type'], 'create_user' => app()->user->username]);
                break;
            default://所有类型
                $this->query->andWhere('(tpl_type=1 or (tpl_type=2 and create_user="'.app()->user->username.'"))');
                break;
        }

        //应用端口过滤
        if (!empty($params['platform_type'])) {
            $platformType = (int)$params['platform_type'];
            if ($platformType > 0) {
                $this->query->andWhere(['platform_type' => $platformType]);
            }
        }

        if (!empty($params['name'])) {
            $this->query->andWhere('name like "%' . $params['name'] . '%"');
        }
        $count = $this->query->count();
        if (0 === $count) {
            return array();
        }

        $pageNo = !empty($params['pageNo']) ? $params['pageNo'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 20;

        $list = $this->query
            ->select('id,pid,name,pic,site_code,place,platform_type,lang,tpl_type,is_default,create_user,create_time,update_time')
            ->orderBy('update_time desc')
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

    /**
     * 获取默认模板
     * @param string $siteCode 站点简称
     * @param int $status 状态
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getDefaultInfo($siteCode, $status)
    {
        return $this->query->where(['is_default' => $status, 'site_code' => $siteCode])->one();
    }

    /**
     * 获取页面模板
     * @param int $place 应用场景 1：活动页 2：首页
     * @param array $range 适用范围 1：PC端   2：WAP端  3：响应式
     * @param string $lang
     * @param string $siteCode
     * @return array
     */
    public static function getTemplateList($place, $range, $lang, $siteCode)
    {
        $username = app()->user->username;
        $where = 'is_delete=0 AND site_code = "'.$siteCode.'" AND (tpl_type=1 OR (tpl_type=2 and create_user="'.$username.'")) AND lang = "'.$lang.'"';
        if (in_array($place, [self::ACTIVITY_PLACE, self::HOME_PLACE, self::ADVERTISEMENT_PLACR])) {
            $where = 'place=' . $place . ' AND '.$where;
        }

        if (!empty($range) && \is_array($range)) {
            $where = '`range` IN(' . implode(',', $range) . ') AND '.$where;
        }

        $list = static::find()
            ->select('id, name, pic, tpl_type, create_user')
            ->where($where)
            ->orderBy('id DESC')
            ->asArray()
            ->all();

        if (empty($list)) {
            return ['private'=>[], 'public'=>[]];
        }


        $return = array();
        foreach ($list as $v) {
            if ($v['create_user'] === $username) {
                $return['private'][] = $v;
            }
            if (1 === intval($v['tpl_type']) && $v['create_user'] !== $username) {
                $return['public'][] = $v;
            }
        }

        return $return;
    }
}
