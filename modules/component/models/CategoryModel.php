<?php
namespace app\modules\component\models;

use app\models\ActiveRecord;

/**
 * 分类模型
 * @property int $id
 * @property string $name
 * @property int $type
 * @property int $place
 * @property int $create_time
 * @property string $create_user
 * @property int $update_time
 * @property string $update_user
 */
class CategoryModel extends ActiveRecord
{
    /**
     * @var $query
     */
    public $query;
    
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
        return 'component_category';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            'id',
            'name',
            'type',
            'place',
            'create_time',
            'create_user',
            'update_time',
            'update_user'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'type',
                ],
                'required',
            ]
        ];
    }

    /**
     * 分类列表

     * @return array
     */
    public function categoryList(array $params)
    {
        if (!empty($params['type'])) {
            $this->query->andWhere(['type' => $params['type']]);
        }
        if (!empty($params['key'])) {
            $this->query->andWhere('name like "%' . $params['key'] . '%"');
        }
        if (!empty($params['place'])) {
            $this->query->andWhere(['place' => $params['place']]);
        }
        $pageNo = !empty($params['pageNo']) ? intval($params['pageNo']) : 1;
        $pageSize = !empty($params['pageSize']) ? intval($params['pageSize']) : 20;
        
        $count = $this->query->count();
        if (0 === $count) {
            return array();
        }
        $list = $this->query
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
     * @param $type
     * @param $name
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getInfoByName($type, $name)
    {
        return $this->query->where(array('type' => $type, 'name' => $name))->one();
    }

}
