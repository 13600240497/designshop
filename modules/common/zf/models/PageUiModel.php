<?php

namespace app\modules\common\zf\models;

use app\base\SitePlatform;
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
 * @property string $bind_relation
 * @property string $data
 * @property string $template
 * @property array  $logConfig
 * @property string $async_data_format
 */
class PageUiModel extends AbstractBaseModel
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
        return SITE_GROUP_CODE . '_page_ui_component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['layout_id', 'component_key', 'lang', 'next_id', 'position'], 'required'],
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
            'bind_relation',
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
        $moduleId = strtolower(app()->controller->module->module->id);
        if (\in_array($moduleId, ['activity', 'advertisement'], true)) {
            return self::activitySiteCode($uiId);
        }
        if ('home' === $moduleId) {
            return self::homeSiteCode($uiId);
        }

        return '';
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
    public static function getPageUi($pageId)
    {
        return static::find()->alias('p')
            ->select('p.tpl_id')
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
     * 查询对应模板的子页面组件是否存在
     *
     * @param $pageIds
     * @param $tplId
     *
     * @return array
     */
    public static function checkPageUiComponentExsits($uiIds, $tplId)
    {
        $result = static::find()->alias('p')
            ->select('p.id, p.lang')
            ->leftJoin(
                PageLayoutModel::tableName() . ' as l',
                'l.id = p.layout_id'
            )->where(['p.tpl_id' => $tplId])
            ->andWhere(['in', 'p.id', $uiIds])
            ->asArray()
            ->all();

        return !empty($result) ? array_column($result, 'lang', 'id') : [];
    }

    /**
     * 获取页面组件三端绑定关系
     *
     * @param int $uiId
     * @param int $tplId
     *
     * @return array
     */
    public static function getPageUiComponentRelation(int $uiId)
    {
        $result = self::find()
            ->select('bind_relation')
            ->where(['id' => $uiId])
            ->asArray()
            ->one();

        return !empty($result['bind_relation']) ? explode(',', $result['bind_relation']) : [];
    }

    /**
     * 获取组件关联语言
     *
     * @param array $uiIds
     *
     * @return array
     */
    public static function getUiComponentLangForUiId(array $uiIds)
    {
        $result = self::find()->select('id, lang')
            ->where(['in', 'id', $uiIds])
            ->asArray()
            ->all();

        return !empty($result) ? array_column($result, 'lang', 'id') : [];
    }

    /**
     * 获取组件对应的渠道名称
     *
     * @param int $uiId
     *
     * @return string
     */
    public static function getUiComponentPipeName(int $uiId)
    {
        $result = static::find()->alias('u')
            ->select('p.pipeline,p.site_code')
            ->leftJoin(
                PageLayoutModel::tableName() . ' as l',
                'l.id = u.layout_id'
            )
            ->leftJoin(
                PageModel::tableName() . ' as p',
                'p.id = l.page_id'
            )
            ->where(['u.id' => $uiId])//->createCommand()->getRawSql();
            ->asArray()
            ->one();

        return !empty($result['pipeline']) ? $result['pipeline'] : '';
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
