<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Clothing\Tools\Utils\String\Random;

if (! function_exists('pcntl_fork')) {
    exit('请重新安装编译 PHP，开启 --enable-pcntl 扩展！');
}

if (! class_exists('Memcached')) {
    exit('请安装扩展: pecl install memcached');
}

function writelog($message)
{
    echo sprintf("[%s]%s\n", date('H:i:s'), $message);
}

pcntl_async_signals(true); // 启用异步信号处理

interface RunnerEnable
{
    public function run();
}

abstract class Worker implements RunnerEnable
{
    public function onSignal($signals, $callback)
    {
        if (! is_array($signals)) {
            $signals = [$signals];
        }

        foreach ($signals as $signo) {
            pcntl_signal($signo, function () use ($signo, $callback) {
                return $callback($signo);
            });
        }

        return $this;
    }

    abstract public function run();
}

class SubWorker extends Worker
{
    private $pid = 0;
    private $workerId;
    private $stop = false;

    public function __construct($workerId)
    {
        $this->workerId = $workerId;
        $this->pid      = posix_getpid();
    }

    protected function wait()
    {
        $m = new Memcached();
        $m->addServer('127.0.0.1', 11211);
        while (! $this->stop) {
            $unqId = Random::uniqueID();

            $incr = $m->increment($unqId);
            if ($incr === false) {
                $m->set($unqId, 1);
            } else {
                writelog("ID 重复 [unique id=$unqId, incr=$incr]");
            }
        }
    }

    public function run()
    {
        $this->onSignal(
            [SIGTERM, SIGINT, SIGHUP, SIGQUIT],
            function ($signo) {
                $this->stop = true;
                writelog("子进程 - 接收到信号 [$this->workerId][pid: $this->pid, signo=$signo]");
            }
        );

        $this->wait();

        exit;
    }
}

class MasterWorker extends Worker
{
    private $workers;
    private $pids = [];

    public function __construct($workers = 5)
    {
        $this->workers = (int) $workers;
    }

    protected function wait()
    {
        while (count($this->pids) > 0) {
            foreach ($this->pids as $workerId => $pid) {
                $signo = pcntl_waitpid($pid, $status, WNOHANG);

                if ($signo === -1 || $signo > 0) {
                    writelog("主进程 - 监测到子进程已退出 [workerId=$workerId, pid=$pid, signo=$signo]");
                    unset($this->pids[$workerId]);
                }
            }

            sleep(1);
        }
    }

    // 以守护进程模式运行
    public function daemon()
    {
        $pid = pcntl_fork();

        if ($pid > 0) {
            exit; // 退出当前进程
        }
        if ($pid === -1) {
            exit('服务启动失败！');
        }
        // 子进程脱离用户终端控制,成为新的 session leader
        posix_setsid();

        return $this;
    }

    public function run()
    {
        $this->pids = [];

        for ($workerId = 1; $workerId <= $this->workers; ++$workerId) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                writelog("创建子进程失败! [workerId=$workerId]");
            } elseif ($pid === 0) {
                (new SubWorker($workerId))->run();
            } else {
                writelog("创建子进程成功! [workerId=$workerId, pid=$pid]");
                $this->pids[$workerId] = $pid;
            }
        }

        $this->onSignal(
            [SIGTERM, SIGINT, SIGHUP, SIGQUIT],
            function ($signo) {
                writelog('主进程 - 接收到信号 [pid=' . posix_getpid() . ", signo=$signo]");

                // 向子进程发送同样的信号
                foreach ($this->pids as $workerId => $pid) {
                    posix_kill($pid, $signo);
                }
            }
        );

        $this->wait();

        writelog('所有子进程已退出！');

        return $this;
    }
}

$workers = 500; // 进程数

// (new MasterWorker($workers))->daemon()->run();
(new MasterWorker($workers))->run();
