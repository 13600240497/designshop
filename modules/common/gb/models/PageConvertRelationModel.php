<?php

namespace app\modules\common\gb\models;


/**
 * 页面转换关系模型
 *
 * @property int    $id
 * @property int    $source_id
 * @property int    $target_id
 * @property string    $site_code
 * @property int    $page_id
 * @property string $pid
 * @property string $group_id
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 */
class PageConvertRelationModel extends BaseModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source_id', 'target_id'], 'required'],
            [['source_id', 'target_id'], 'integer'],
        ];
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'page_id',
            'pid',
            'group_id',
            'site_code'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 获取最后关联的关联页面信息
     *
     * @param int $pageId 页面ID
     *
     * @return PageConvertRelationModel|null
     */
    public static function getLastTargetPid(int $pageId)
    {
        /** @var PageConvertRelationModel $page */
        $page = static::find()->alias('r')
            ->select('p.id as page_id, p.pid, p.group_id, p.site_code')
            ->leftJoin(PageModel::tableName() . ' as p', 'p.id = r.target_id')
            ->leftJoin(ActivityModel::tableName() . ' as a', 'a.id = p.activity_id')
            ->where([
                'r.source_id' => $pageId,
                'p.is_delete' => PageModel::NOT_DELETE,
                'a.is_delete' => ActivityModel::NOT_DELETE
            ])->orderBy('r.update_time DESC')->one();

        return $page ?? null;
    }

    /**
     * 获取最后关联的前置页面信息
     *
     * @param int $pageId 页面ID
     *
     * @return PageConvertRelationModel|null
     */
    public static function getLastSourcePid(int $pageId)
    {
        /** @var PageConvertRelationModel $page */
        $page = static::find()->alias('r')
            ->select('p.id as page_id, p.pid, p.group_id')
            ->leftJoin(PageModel::tableName() . ' as p', 'p.id = r.source_id')
            ->leftJoin(ActivityModel::tableName() . ' as a', 'a.id = p.activity_id')
            ->where([
                'r.target_id' => $pageId,
                'p.is_delete' => PageModel::NOT_DELETE,
                'a.is_delete' => ActivityModel::NOT_DELETE
            ])->orderBy('r.update_time DESC')->one();

        return $page ?? null;
    }
}
