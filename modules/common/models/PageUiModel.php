<?php

namespace app\modules\common\models;

use app\models\ActiveRecord;

/**
 * PageUiComponent模型
 *
 * @property int    $id
 * @property string $component_key
 * @property string $lang
 * @property int    $layout_id
 * @property int    $next_id
 * @property int    $position
 * @property int    $tpl_id
 * @property string $data
 * @property string $template
 * @property array  $logConfig
 * @property string $async_data_format
 */
class PageUiModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'layout_id';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page_ui_component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['layout_id', 'component_key', 'lang',  'next_id', 'position'], 'required'],
            [['layout_id', 'next_id', 'position', 'tpl_id'], 'integer'],
            [['async_data_format'], 'default', 'value' => ''],
            ['next_id', 'validateKey'],
        ];
    }

    /**
     * 将data、need_navigate字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'data',
            'share_data',
            'lang',
            'need_navigate',
            'tpl_id',
            'custom_css',
            'select_tpl_id',
            'async_data_format'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        if (($item = static::findOne([
                'layout_id' => $this->layout_id,
                'next_id'   => $this->next_id,
                'position'  => $this->position,
                'lang'      => $this->lang
            ]))
            && (!$this->id || (int) $item->id !== (int) $this->id)
        ) {
            $this->addError('next_id', 'layout_id&&next_id&&position主键冲突');

            return false;
        }

        return true;
    }

    /**
     * 根据UIId查询站点siteCode
     *
     * @param int $uiId ui组件ID
     *
     * @return string
     */
    public static function getSiteCode($uiId)
    {
        $module = app()->controller->module->id;
        if (in_array(strtolower($module),['activity','advertisement','gbad'])) {
            return self::activitySiteCode($uiId);
        }
        if ('home' === strtolower($module)) {
            return self::homeSiteCode($uiId);
        }
    }
    
    /**
     * 活动站点
     *
     * @param $uiId
     *
     * @return mixed|string
     */
    private static function activitySiteCode($uiId)
    {
        $one = static::find()->alias('u')->select('a.site_code')
            ->leftJoin(PageLayoutModel::tableName() . ' as l', 'l.id = u.layout_id')
            ->leftJoin(PageModel::tableName() . ' as p', 'p.id = l.page_id')
            ->leftJoin(ActivityModel::tableName() . ' as a', 'a.id = p.activity_id')
            ->where(['u.id' => $uiId])
            ->asArray()
            ->one();
    
        return $one ? $one['site_code'] : '';
    }
    
    /**
     * 首页站点
     *
     * @param $uiId
     *
     * @return mixed|string
     */
    private static function homeSiteCode($uiId)
    {
        $one = static::find()->alias('u')->select('p.site_code')
            ->leftJoin(PageLayoutModel::tableName() . ' as l', 'l.id = u.layout_id')
            ->leftJoin(PageModel::tableName() . ' as p', 'p.id = l.page_id')
            ->where(['u.id' => $uiId])
            ->asArray()
            ->one();
    
        return $one ? $one['site_code'] : '';
    }
    
    /**
     * 根据layoutId获取其下的所有ui组件的ID数组
     *
     * @param $layoutId
     *
     * @return array
     */
    public static function getUiIdsByLayoutId($layoutId)
    {
        $ids = static::find()->select('id')->where(['layout_id' => $layoutId])->asArray()->all();

        return $ids ? array_column($ids, 'id') : [];
    }

    /**
     * 根据layoutId获取有数据的列数
     *
     * @param $layoutId
     *
     * @return array
     */
    public static function getPositionsById($layoutId)
    {
        $res = static::find()->select('position')->where(['layout_id' => $layoutId])->asArray()->all();

        return $res ? array_column($res, 'position') : [];
    }

    /**
     * 获取页面UI记录
     *
     * @param $pageId
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getPageUi($pageId)
    {
        return static::find()->alias('p')
            ->select('p.id')
            ->leftJoin(
                PageLayoutModel::tableName() . ' as l',
                'l.id = p.layout_id'
            )->where(['l.page_id' => $pageId])
            ->asArray()
            ->all();
    }

    /**
     * 根据layoutids删除ui组件
     *
     * @param array $layoutIds
     *
     * @return bool|string
     */
    public static function deleteUiByLayoutIds($layoutIds)
    {
        $uiIds = self::find()->select('id')->where(['layout_id' => $layoutIds])->column();

        return self::deleteUi($uiIds);
    }

    /**
     * 根据uiids删除ui组件
     *
     * @param array $uiIds
     *
     * @return bool|string
     */
    public static function deleteUi($uiIds)
    {
        if (!empty($uiIds)) {
            //删除ui_data
            if (false === PageUiComponentDataModel::deleteAll(['component_id' => $uiIds])) {
                return PageUiComponentDataModel::tableName() . '表记录清理失败';
            }

            //删除ui
            if (false === self::deleteAll(['id' => $uiIds])) {
                return self::tableName() . '表记录清理失败';
            }
        }

        return true;
    }

    /**
     * 获取页面自动刷新组件列表
     *
     * @param int $pageId
     * @param string $lang
     * @return static[]
     */
    public static function getPageAutoRefreshUiList($pageId, $lang)
    {
        $subQuery = PageLayoutModel::find()->select('id')->where(['page_id' => $pageId, 'lang' => $lang]);
        return static::find()->where(['layout_id' => $subQuery])->andWhere(['!=', 'async_data_format', ''])->all();
    }
}
