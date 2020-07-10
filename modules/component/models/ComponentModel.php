<?php
namespace app\modules\component\models;

use app\base\SitePlatform;
use app\models\ActiveRecord;

/**
 * 组件模型
 * @property int $id
 * @property string $category
 * @property string $component_key
 * @property string $name
 * @property string $description
 * @property string $logo_url
 * @property int $status
 * @property int $place
 * @property int $verify_status
 * @property int $is_delete
 * @property string $create_user
 * @property string $siteGroups
 */
class ComponentModel extends ActiveRecord
{
    //适用范围|1PC端
    const RANGE_PC = 1;

    //适用范围|2WAP端
    const RANGE_WAP = 2;

    //适用范围|3响应式
    const RANGE_RESPONSIVE = 3;

    // 使用范围|4原生页面
    const RANGE_NATIVE = 4;

    /** @var int 状态 - 已上线 */
    const STATUS_USED = 3;
    /** @var int 状态 - 已下线 */
    const STATUS_OFFLINE = 5;


    //自定义组件标志
    public $isNotCustom = 0;

    //自定义组件标志
    public $isCustom = 1;
    /**
     * @var \yii\db\ActiveQuery
     */
    public $query;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->query = static::find()->select('a.*, c.place')->alias('a')
            ->leftJoin(CategoryModel::tableName().' as c', 'a.category_id = c.id');
    }


    /**
     * 获取最大记录ID
     * @return int
     */
    public function maxId()
    {
        return $this->query
            ->where('1 = 1')
            ->orderBy('a.id DESC')
            ->scalar();
    }

    /**
     *检测是否有自定义组件
     * @param $place
     * @param $type
     * @param $siteCode
     * @return object
     */
    public function checkCustom($place, $type, $siteCode)
    {
        return static::find()->select('a.*, c.place')->alias('a')
            ->leftJoin(CategoryModel::tableName().' as c', 'a.category_id = c.id')
            ->leftJoin(
                ComponentSiteRelationModel::tableName() . ' as csr',
                'a.id = csr.component_id AND csr.type = ' . $type
            )
            ->where(['a.is_custom' => $this->isCustom, 'c.place' => $place, 'csr.site_code' => $siteCode])
            ->groupBy('a.id')
            ->one();
    }

    /**
     * 组件列表
     * @param array $params
     * @return array
     */
    public function getList(array $params)
    {
        $status = null;
        if ($params['status'] != '') {
            $status = !empty($params['status']) ? self::STATUS_USED : self::STATUS_OFFLINE;
        }

        $queryList = static::find()->select('a.*, c.place, group_concat(csr.site_code) as siteGroups')->alias('a')
            ->leftJoin(CategoryModel::tableName().' as c', 'a.category_id = c.id')
            ->leftJoin(
                ComponentSiteRelationModel::tableName() . ' as csr',
                'a.id = csr.component_id AND csr.type = c.type'
            )->where('1 = 1')
            ->andFilterWhere(['like', 'a.name', $params['word'] ?? ''])
            ->andFilterWhere(['a.component_key' => $params['key'] ?? ''])
            ->andFilterWhere(['a.range' => !empty($params['range']) ? $params['range'] : null])
            ->andFilterWhere(['a.status' => $status])
            ->andFilterWhere(['c.type' => !empty($params['type']) ? $params['type'] : null])
            ->andFilterWhere(['c.place' => !empty($params['place']) ? $params['place'] : null])
            ->andFilterWhere(['like', 'csr.site_code', $params['siteGroup'] ?? ''])
            ->groupBy('a.id')
            ->orderBy(['a.status' => SORT_ASC, 'a.id' => SORT_ASC]);
        $pageNo = !empty($params['pageNo']) ? $params['pageNo'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 20;

        $count = $queryList->count();
        if (0 === $count) {
            return array();
        }

        $list = $queryList
            ->offset(($pageNo - 1) * $pageSize)
            ->limit($pageSize)
            ->all();
        if(!empty($list)){
            $component_id = array_column($list,'id');
            $listSite = ComponentSiteRelationModel::find()
                ->where(['component_id'=>$component_id])
                ->andFilterWhere(['type' => !empty($params['type']) ? $params['type'] : null])
                ->groupBy('component_id')
                ->select('group_concat(site_code) as siteGroups,component_id')
                ->asArray()
                ->all();
            $sites = [];
            foreach ($listSite as $row) {
                $sites[$row['component_id']] = $row['siteGroups'];
            }
            foreach ($list as &$item){
                $item->siteGroups = SitePlatform::getSiteGroupsBySiteCodes($sites[$item->id]);
            }
        }


        return array(
            'totalCount' => (int)$count,
            'pageSize' => (int)$pageSize,
            'pageNo' => (int)$pageNo,
            'list' => $list
        );
    }

    /**
     * 通过编码获取组件信息
     * @param  string $key 组件编码
     * @return object 组件信息
     */
    public function getByKey($key)
    {
        static $data = [];
        if (empty($data[$key])) {
            $data[$key] = $this->query
                ->where(['a.component_key' => $key])
                ->one();
        }
        
        return !empty($data[$key]) ? $data[$key] : [];
    }
}
