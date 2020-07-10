<?php

namespace app\components;

use Clothing\Tools\ServiceTracing\Tracer;
use yii\db\Command;
use yii\db\DataReader;
use yii\db\Exception;
use Yii;

/**
 * Class MysqlWithTracing
 * mysql连接类记录
 * @package app\components
 */
class MysqlCommandWithTracing extends Command{

    protected static $tracerClient;

    public function getTracerClient()
    {
        return self::$tracerClient ?: Tracer::singleton()->newBinaryClient('mysql');
    }

    /**
     * Performs the actual DB query of a SQL statement.
     * @param string $method method of PDOStatement to be called
     * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return mixed the method execution result
     * @throws Exception if the query causes any problem
     * @since 2.0.1 this method is protected (was private before).
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        list($profile, $rawSql) = $this->logQuery('yii\db\Command::query');

        if ($method !== '') {
            $info = $this->db->getQueryCacheInfo($this->queryCacheDuration, $this->queryCacheDependency);
            if (is_array($info)) {
                /* @var $cache \yii\caching\CacheInterface */
                $cache = $info[0];
                $cacheKey = [
                    __CLASS__,
                    $method,
                    $fetchMode,
                    $this->db->dsn,
                    $this->db->username,
                    $rawSql ?: $rawSql = $this->getRawSql(),
                ];
                $result = $cache->get($cacheKey);
                if (is_array($result) && isset($result[0])) {
                    Yii::debug('Query result served from cache', 'yii\db\Command::query');
                    return $result[0];
                }
            }
        }
        $this->prepare(true);
        $request = $this->getTracerClient()->newRequest('query', [$rawSql])->start();
        try {
            $profile and Yii::beginProfile($rawSql, 'yii\db\Command::query');

            $this->internalExecute($rawSql);

            if ($method === '') {
                $result = new DataReader($this);
            } else {
                if ($fetchMode === null) {
                    $fetchMode = $this->fetchMode;
                }
                $result = call_user_func_array([$this->pdoStatement, $method], (array) $fetchMode);
                $this->pdoStatement->closeCursor();
            }

            $profile and Yii::endProfile($rawSql, 'yii\db\Command::query');
        } catch (Exception $e) {
            $profile and Yii::endProfile($rawSql, 'yii\db\Command::query');
            throw $e;
        }

        if (isset($cache, $cacheKey, $info)) {
            $cache->set($cacheKey, [$result], $info[1], $info[2]);
            Yii::debug('Saved query result in cache', 'yii\db\Command::query');
        }
        $request->finish();
        return $result;
    }

    /**
     * Executes the SQL statement.
     * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
     * No result set will be returned.
     * @return int number of rows affected by the execution.
     * @throws Exception execution failed
     */
    public function execute()
    {
        $sql = $this->getSql();
        list($profile, $rawSql) = $this->logQuery(__METHOD__);

        if ($sql == '') {
            return 0;
        }

        $this->prepare(false);
        try {
            $profile and Yii::beginProfile($rawSql, __METHOD__);
            $request = $this->getTracerClient()->newRequest('query', [$rawSql])->start();
            $this->internalExecute($rawSql);
            $n = $this->pdoStatement->rowCount();

            $profile and Yii::endProfile($rawSql, __METHOD__);

            $this->refreshTableSchema();
            $request->finish();
            return $n;
        } catch (Exception $e) {
            $profile and Yii::endProfile($rawSql, __METHOD__);
            throw $e;
        }

    }

    /**
     * Logs the current database query if query logging is enabled and returns
     * the profiling token if profiling is enabled.
     * @param string $category the log category.
     * @return array array of two elements, the first is boolean of whether profiling is enabled or not.
     * The second is the rawSql if it has been created.
     */
    private function logQuery($category)
    {
        if ($this->db->enableLogging) {
            $rawSql = $this->getRawSql();
            Yii::info($rawSql, $category);
        }
        if (SHOEDBSQL) {
            $rawSql = $this->getRawSql();
            DbDebug::info($rawSql);
        };
        if (!$this->db->enableProfiling) {
            return [false, isset($rawSql) ? $rawSql : null];
        }

        return [true, isset($rawSql) ? $rawSql : $this->getRawSql()];
    }

}
