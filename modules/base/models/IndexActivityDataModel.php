<?php

namespace app\modules\base\models;

use app\models\ActiveRecord;


/**
 * 首页活动页面数据模型
 * @package app\modules\common\models
 *
 * @property int    $id
 * @property int    $buyer_identity
 * @property string $page_name
 * @property string $page_type
 * @property int    $sub_id
 * @property int    $sub_ie_pv
 * @property int    $sub_ic_pv
 * @property double $sub_cl_rate
 * @property int    $location
 * @property int    $module_id
 * @property int    $module_ie_pv
 * @property int    $module_ic_pv
 * @property int    $pit_id
 * @property int    $pit_ie_pv
 * @property int    $pit_ic_pv
 * @property double $pit_cl_rate
 * @property string $platform
 * @property date   $update_time
 * @property string $site
 */
class IndexActivityDataModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'name';
    }

    /**
     * 大数据报表查询走从库
     */
    public static function getDb()
    {
        return app()->get('db_slave');
        //return app()->get('db');
    }

    /**
     * 获取主库连接
     */
    public static function getMasterDb()
    {
        return app()->get('db');
    }

    /**
     * 获取最好一行数据
     * @return ActivityDataModel
     */
    public static function getLastRow()
    {
        return static::find()->orderBy('id DESC')->limit(1)->one();
    }
}
