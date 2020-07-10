<?php

namespace app\components;

use yii\db\Connection;
use Yii;

/**
 * Class MysqlWithTracing
 * mysql连接类记录
 * @package app\components
 */
class MysqlWithTracing extends Connection{

    public $commandClass = 'app\components\MysqlCommandWithTracing';
    public $commandMap = [
        'pgsql' => 'app\components\MysqlCommandWithTracing', // PostgreSQL
        'mysqli' => 'app\components\MysqlCommandWithTracing', // MySQL
        'mysql' => 'app\components\MysqlCommandWithTracing', // MySQL
        'sqlite' => 'yii\db\sqlite\Command', // sqlite 3
        'sqlite2' => 'yii\db\sqlite\Command', // sqlite 2
        'sqlsrv' => 'app\components\MysqlCommandWithTracing', // newer MSSQL driver on MS Windows hosts
        'oci' => 'app\components\MysqlCommandWithTracing', // Oracle driver
        'mssql' => 'app\components\MysqlCommandWithTracing', // older MSSQL driver on MS Windows hosts
        'dblib' => 'app\components\MysqlCommandWithTracing', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => 'app\components\MysqlCommandWithTracing', // CUBRID
    ];

    /**
     * Creates a command for execution.
     * @param string $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     * @return Command the DB command
     * @throws
     */
    public function createCommand($sql = null, $params = [])
    {
        $driver = $this->getDriverName();
        $config = ['class' => 'app\components\MysqlCommandWithTracing'];
        if ($this->commandClass !== $config['class']) {
            $config['class'] = $this->commandClass;
        } elseif (isset($this->commandMap[$driver])) {
            $config = !is_array($this->commandMap[$driver]) ? ['class' => $this->commandMap[$driver]] : $this->commandMap[$driver];
        }
        $config['db'] = $this;
        $config['sql'] = $sql;

        /** @var Command $command */
        $command = Yii::createObject($config);
        return $command->bindValues($params);
    }

}
