<?php

namespace app\base;


use yii\base\Exception;

class SwooleClient
{
    public    $client;
    protected $host        = '127.0.0.1';
    protected $port        = 9511;
    protected $refreshPort = 9512;
    protected $timeout     = 10;

    public function init(string $module = 'task')
    {
        if (empty($this->client)) {
        	$this->client = new \swoole_client(SWOOLE_SOCK_TCP);
	        $fp = $this->client->connect(
		        $this->host,
		        ('task' == $module) ? $this->port : $this->refreshPort,
		        $this->timeout
	        );
	        if (!$fp) {
		        throw new Exception($fp->errMsg, $fp->errCode);
	        }
        }

        return $this;
    }

    public function isConnected()
    {
    	return $this->client->isConnected();
    }

    public function send(array $data)
    {
        $data = \GuzzleHttp\json_encode($data);
        $data = pack('N', strlen($data)) . $data;
        $this->client->send($data);
    }

    public function getRecv()
    {
        $data = \GuzzleHttp\json_decode($this->client->recv(), true);

        return $data ?? [];
    }
}
