<?php

namespace app\commands;

use yii\console\Controller;
use app\commands\component\WorkerComponent;

/**
 *    socket面向对象的
 */
class TaskController extends Controller
{
    const HOST             = '127.0.0.1';
    const PORT             = 9511;
    const WORKER_NUM       = 8;
    const TASK_NUM         = 48;
    const DEBUG_WORKER_NUM = 2; //本地和测试环境
    const DEBUG_TASK_NUM   = 8; //本地和测试环境

    private   $serv;
    protected $workerClass;

    public function actionStart()
    {
        $this->serv = new \swoole_server(self::HOST, self::PORT);
        $this->serv->set(
            [
                //'reactor_num'       => 2,
                'dispatch_mode'            => 3,
                'task_ipc_mode'            => 3,
                'worker_num'               => (in_array(YII_ENV, ['prerelease','product'])) ? self::WORKER_NUM : self::DEBUG_WORKER_NUM,
                'task_worker_num'          => (in_array(YII_ENV, ['prerelease','product'])) ? self::TASK_NUM : self::DEBUG_TASK_NUM,
                'daemonize'                => true,
                'log_file'                 => dirname(__DIR__) . '/runtime/logs/swoole.log',
                //'log_level'             => SWOOLE_LOG_ERROR,
                'pid_file'                 => '/tmp/swooleTask.pid',
                'max_request'              => 100,
                //'max_conn'              => 10000, // 最大连接数
                'task_max_request'         => 100,
                'open_cpu_affinity'        => 1,
                'open_tcp_nodelay'         => 1,
                'open_length_check'        => true,      // 开启协议解析
                'package_length_type'      => 'N',     // 长度字段的类型
                'package_length_offset'    => 0,       //第几个字节是包长度的值
                'package_body_offset'      => 4,       //第几个字节开始计算长度
                'package_max_length'       => 24 * 80 * 1024,  //协议最大长度
                'max_wait_time'            => 30,
                //'buffer_output_size' => 32 * 1024 * 1024,
                //'debug_mode'        => 1,
                //'heartbeat_check_interval' => 10,    // 心跳检测间隔时长(秒)
                //'heartbeat_idle_time'      => 20,   // 连接最大允许空闲的时间
            ]
        );

        $this->serv->on('Start', [$this, 'onStart']);
        //$this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->serv->on('Task', [$this, 'onTask']);
        $this->serv->on('Receive', [$this, 'onReceive']);
        //$this->serv->on('Close', [$this, 'onClose']);
        $this->serv->on('Finish', [$this, 'onFinish']);
        $this->serv->start();
    }

    public function onStart($serv)
    {
        echo "Start\n";
        cli_set_process_title("task_master");
    }

    public function onWorkerStart($serv, $worker_id)
    {
        include 'component/WorkerComponent.php';
        $this->workerClass = new WorkerComponent();
    }

    public function onConnect($serv, $fd, $from_id)
    {
        //echo "Client {$fd} connect\n";
    }

    public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
    {
        //echo "Get Message From Client {$fd}:{$data}\n";
        $info = unpack('N', $data);
        $len = $info[1];
        $body = substr($data, -$len);
        $body = \GuzzleHttp\json_decode($body, true);
        if (!is_array($body) || empty($body['data']) || empty($body['action'])) {
            echo "server receive \$data format error.\n";

            return;
        }

        foreach ($body['data'] as $item) {
            $serv->task(['mode' => $body['mode'], 'action' => $body['action'], 'result' => $item]);
        }
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        //echo "This Task {$task_id} from Worker {$from_id}\n";
        if (method_exists($this->workerClass, $data['action'])) {
            $this->workerClass->{$data['action']}($data);
        }
    }

    public function onFinish($serv, $task_id, $data)
    {
        //echo "Task {$task_id} finish\n";
        //echo "Result: {$data}\n";
    }

    public function onClose($serv, $fd, $from_id)
    {
        //echo "Client {$fd} close connection\n";
    }
}

