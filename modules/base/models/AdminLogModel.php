<?php
namespace app\modules\base\models;

use app\models\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * 管理员操作日志模型
 *
 * @property string $request_id
 * @property int $admin_id
 * @property string $admin_name
 * @property string $request_route
 * @property string $record_table
 * @property string $record_id
 * @property string $record_name
 * @property string $detail
 * @property int $detail2diff
 * @property int $ip
 * @property int $is_insert
 * @property string $labels
 */
class AdminLogModel extends ActiveRecord
{
    /**
     * @var string 操作，insert、update、delete
     */
    public $action;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig = false;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            'id',
            'request_id',
            'admin_id',
            'admin_name',
            'request_route',
            'record_table',
            'record_id',
            'record_name',
            'detail',
            'detail2diff',
            'create_time',
            'ip',
            'is_insert',
            'labels',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->processDetail();
        if (!$this->detail && 'delete' !== (string)$this->action) {
            return false;
        }
        $this->admin_id = (int)app()->user->id;
        $this->request_route = (string)app()->requestedRoute;
        $this->ip = app()->ip->get(true);

        $this->admin_name = (string)app()->user->realname;
        if (!$this->admin_name && false !== strpos($this->request_route, 'crontab')) {
            $this->admin_name = '定时任务';
        } elseif (!$this->admin_id && false !== strpos($this->request_route, 'do-login') && $this->record_id) {
            $this->admin_id = $this->record_id;
            $this->admin_name = AdminModel::getUserName($this->admin_id);
        }
        $this->labels = $this->labels ? json_encode($this->labels, JSON_UNESCAPED_UNICODE) : '';

        return parent::beforeSave($insert);
    }

    /**
     * 处理日志详情
     */
    protected function processDetail()
    {
        if (\is_array($this->detail)) {
            if (!$this->detail) {
                $this->detail = '';
            } else {
                // 如果直接使用$current = reset($this->detail)会报以下错误？
                // Indirect modification of overloaded property
                // app\modules\admin\models\AdminLogModel::$detail has no effect
                $detail = $this->detail;
                $current = reset($detail);
                if (\is_array($current)
                    && isset($current['new'], $current['old'], $current['diff'])
                ) {
                    $this->detail2diff = 1;
                    $this->detail = json_encode($this->detail, JSON_UNESCAPED_UNICODE);
                } else {
                    $this->detail = stripslashes(VarDumper::export($this->detail));
                }
            }
        }
    }
}
