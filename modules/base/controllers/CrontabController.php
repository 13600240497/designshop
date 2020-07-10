<?php
namespace app\modules\base\controllers;

use app\base\Upload;
use Yii;

/**
 * 定时任务控制器
 */
class CrontabController extends Controller
{
    public function behaviors()
    {
        return [];
    }

    /**
     * 消费错误队列信息
     * [定时任务，每分钟运行]
     */
    public function actionConsume()
    {
        return app()->rms->consume();
    }

    /**
     * php和mysql自检测试
     * [定时任务，每5分钟运行]
     */
    public function actionPhpMysqlSelfCheck()
    {
        app()->rms->phpSelfCheck();
        app()->rms->mysqlSelfCheck();
        return 'Php and Mysql Self-test is successful.';
    }

    /**
     * 上传图片清除
     * [定时任务，每天一次运行]
     */
    public function actionClear()
    {
        $uploadPath = \yii::getAlias(app()->params['uploadsPath'] ?? '@app/runtime/uploads');
        $upload = new Upload($uploadPath);
        $upload->clear();
        return 'Resource Path Images is clear';
    }

    /**
     * 清理表缓存
     * @return bool
     */
    public function actionClearCache()
    {
        $tableName = app()->request->get('table', '');
        app()->redis->del(app()->components['cache']['keyPrefix'] . $tableName);
        return !empty($tableName) && Yii::$app->db->schema->refreshTableSchema($tableName);
    }
}
