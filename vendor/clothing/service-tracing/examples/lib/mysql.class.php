<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Clothing\Tools\ServiceTracing\Proxy as TracingProxy;

class Mysql
{
    public function query($sql)
    {
        usleep(mt_rand(10000, 999999));

        return ['id'=>1, 'name'=>'foo'];
    }

    public function insert($table, array $data)
    {
        usleep(mt_rand(10000, 999999));

        return true;
    }

    public function truncate($table)
    {
        usleep(mt_rand(10000, 99999));

        throw new \RuntimeException('disable truncate');
    }
}

// class MysqlProxy extends TracingProxy
// {
//     public function __construct()
// }
