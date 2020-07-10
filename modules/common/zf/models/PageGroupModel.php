<?php

namespace app\modules\common\zf\models;

/**
 * ActivityGroup模型
 *
 * @property int    $id
 * @property int    $activity_group_id
 * @property int    $page_group_id
 * @property int    $platform_type
 * @property int    $page_id
 * @property string $pipeline
 */
class PageGroupModel extends AbstractBaseModel
{
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
            [['page_group_id', 'platform_type', 'platform_type', 'page_id', 'pipeline'], 'required'],
            [['activity_group_id'], 'default', 'value' => 0],
        ];
    }

    /**
     * 生成子页面分组ID
     * @return string
     */
    public static function generatePageGroupId()
    {
        return md5(microtime() . random_int(0, 100));
    }

    /**
     * 查询子页面分组下所有子页面
     * @param int $groupId
     * @return array
     */
    public static function getPageListByPageGroupId($groupId)
    {
        return static::find()->alias('pg')
	        ->select('pg.*')
	        ->innerJoin(PageModel::tableName() . ' as p', 'p.id = pg.page_id')
	        ->where(['pg.page_group_id' => $groupId, 'p.is_delete' => PageModel::NOT_DELETE])
	        ->all();
    }

    /**
     * 获取子页面分组ID
     * @param int $pageId
     * @return int
     */
    public static function getPageGroupIdByPageId($pageId)
    {
        $model = static::find()->select('page_group_id')->where(['page_id' => $pageId])->asArray()->one();
        return empty($model) ? 0 : $model['page_group_id'];
    }
    
    /**
     * 获取子页面分组下的所有页面语言
     *
     * @param $groupId
     * @param $pipeline
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageLangListByGroupId($groupId, $pipeline)
    {
        return self::find()->alias('g')
            ->select('g.platform_type, g.page_id, g.pipeline, l.lang')
            ->leftJoin(
                PageLanguageModel::tableName() . ' as l',
                'l.page_id = g.page_id'
            )->where(['g.page_group_id' => $groupId, 'g.pipeline' => $pipeline])
            ->asArray()
            ->all();
    }
    
    /**
     * 获取三端下所有渠道的子页面
     *
     * @param string $groupId
     * @param array  $platform
     * @param array  $pipeline
     *
     * @return array
     */
    public static function getPlatformPipelinePageList(string $groupId, array $platform, array $pipeline)
    {
        $result = self::find()->select("platform_type, group_concat(page_id) as c_page_id")
            ->where(['page_group_id' => $groupId])
            ->andWhere(['in', 'platform_type', $platform])
            ->andWhere(['in', 'pipeline', $pipeline])
            ->groupBy('platform_type')
            ->asArray()
            ->all();
        
        return !empty($result) ? array_column($result, 'c_page_id', 'platform_type') : [];
    }
}
