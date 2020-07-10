<?php

namespace app\modules\common\models;

use app\base\SiteConstants;
use app\base\SitePlatform;
use app\models\ActiveRecord;
use app\modules\component\models\UiModel;

/**
 * PageUiTemplateModel 模型
 *
 * @property int    $id
 * @property string $website_code
 * @property string $platform_code
 * @property int    $page_id
 * @property int    $place_type
 * @property string $lang
 * @property string $name
 * @property string $pic_url
 * @property int    $ui_key
 * @property int    $tpl_id
 * @property string $ui
 * @property string $ui_data
 * @property int    $view_type
 * @property int    $is_delete
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 */
class PageUiTemplateModel extends ActiveRecord
{
    /** @var int 查看类型：1-公用模板 */
    const VIEW_TYPE_PUBLIC = 1;
    /** @var int 查看类型：2-私有模板 */
    const VIEW_TYPE_PRIVATE = 2;

    const DELETED = 1;
    const NOT_DELETE = 0;

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
            [['id', 'page_id', 'place_type', 'tpl_id', 'view_type', 'is_delete'], 'integer'],
            [['website_code', 'platform_code', 'lang', 'name', 'ui_key', 'ui_data', 'create_user'], 'required'],
        ];
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'create_name',
        ];
        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 获取模板列表
     *
     * @param array $params 参数
     * - username
     * - website_code
     * - platform_code
     * - place_type
     * - lang
     * - view_type
     * - ui_key
     * - keyword
     * - page_no
     * - page_size
     *
     * @return array
     */
    public static function getTplPageList($params)
    {
        $query = static::find()->where(['is_delete' => static::NOT_DELETE]);
        !empty($params['website_code']) && $query->andWhere(['website_code' => $params['website_code']]);
        !empty($params['platform_code']) && $query->andWhere(['platform_code' => $params['platform_code']]);
        !empty($params['place_type']) && $query->andWhere(['place_type' => $params['place_type']]);
        !empty($params['lang']) && $query->andWhere(['lang' => $params['lang']]);

        if (isset($params['view_type']) && is_numeric($params['view_type'])) {
            if (static::VIEW_TYPE_PUBLIC == $params['view_type']) {
                $query->andWhere(['view_type' => $params['view_type']]);
            } elseif (static::VIEW_TYPE_PRIVATE == $params['view_type']) {
                $query->andWhere(['view_type' => $params['view_type'], 'create_user' => $params['username']]);
            } else {
                $query->andWhere('(view_type='. static::VIEW_TYPE_PUBLIC .' or (view_type='.
                    static::VIEW_TYPE_PRIVATE .' and create_user="'. $params['username'] .'"))');
            }
        }

        !empty($params['ui_key']) && $query->andWhere(['ui_key' => $params['ui_key']]);
        !empty($params['keyword']) && $query->andWhere('name like "%'. $params['keyword'] .'%"');

        $total = $query->count();
        if (0 === $total) {
            return [];
        }

        $pageNo = !empty($params['page_no']) ? $params['page_no'] : 1;
        $pageSize = !empty($params['page_size']) ? $params['page_size'] : 20;

        $modelList = $query
            ->limit($pageSize)
            ->offset(($pageNo - 1) * $pageSize)
            ->orderBy('update_time desc')
            ->all();
        return [$total, $modelList];
    }

    /**
     * 根据模板名称获取用户模板
     * @param string $name
     * @return PageUiTemplateModel
     */
    public static function getTplByName($name)
    {
        return static::find()->where(['name' => $name, 'is_delete' => static::NOT_DELETE])->one();
    }

    /**
     * 获取用户组件模板列表
     *
     * @param string $username
     * @param string $siteCode
     * @param int $placeType
     * @param string $lang
     * @return array
     */
    public static function getUserTemplateList($username, $siteCode, $placeType, $lang=NULL)
    {
        $placeTypes = [
            SiteConstants::ACTIVITY_PAGE_TYPE_HOME,
            SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL,
            SiteConstants::ACTIVITY_PAGE_TYPE_ADVERTISEMENT
        ];

        list($websiteCode, $platformCode) = SitePlatform::splitSiteCode($siteCode);
        $where = 'p.is_delete=0 AND p.website_code = "'. $websiteCode .'" AND p.platform_code = "'. $platformCode
            .'" AND (p.view_type='. self::VIEW_TYPE_PUBLIC .' OR (p.view_type='. self::VIEW_TYPE_PRIVATE .' and p.create_user="'. $username .'"))';

        if (in_array($placeType, $placeTypes)) {
            $where .= ' AND p.place_type=' . $placeType;
        }

        if (!empty($lang)) {
            $where .= ' AND p.lang="' . $lang .'"';
        }

        $list = static::find()->alias('p')
            ->select('p.id, p.name, p.tpl_id, p.pic_url, p.view_type, p.ui_key, u.name as ui_name, p.create_user')
            ->leftJoin(UiModel::tableName() . ' as u', 'u.component_key=p.ui_key')
            ->where($where)
            ->orderBy('p.id DESC')
            ->asArray()
            ->all();

        $tplList = ['private'=>[], 'public'=>[]];
        if (empty($list)) {
            return $tplList;
        }

        foreach ($list as $v) {
            if ($v['create_user'] === $username) {
                $uiKey = $v['ui_key'];
                if (!isset( $tplList['private'][$uiKey])) {
                    $tplList['private'][$uiKey] = ['name' => $v['ui_name'], 'tpl_list' => []];
                }
                $tplList['private'][$uiKey]['tpl_list'][] = $v;
            }

            if (self::VIEW_TYPE_PUBLIC === intval($v['view_type']) && $v['create_user'] !== $username) {
                $uiKey = $v['ui_key'];
                if (!isset( $tplList['public'][$uiKey])) {
                    $tplList['public'][$uiKey] = ['name' => $v['ui_name'], 'tpl_list' => []];
                }
                $tplList['public'][$uiKey]['tpl_list'][] = $v;
            }
        }

        return $tplList;
    }
}