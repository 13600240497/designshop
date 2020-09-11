<?php
namespace Globalegrow\YiiPredis;

use yii;
use Predis\Client;

/**
 * predis连接
 */
class Connection extends \yii\redis\Connection
{
    /**
     * @var mixed Connection parameters for one or more servers.
     */
    public $parameters;
    /**
     * @var mixed Options to configure some behaviours of the client.
     */
    public $options;
    /**
     * @var Client
     */
    private $client = false;

    /**
     * @inheritdoc
     */
    public function executeCommand($name, $params = [])
    {
        $this->open();
        Yii::trace("Executing Redis Command: {$name}", __METHOD__);
        return $this->client->executeCommand(
            $this->client->createCommand($name, $params)
        );
    }

    /**
     * 获取`Client`
     *
     * @return Client
     */
    public function getClient()
    {
        $this->open();
        return $this->client;
    }

    /**
     * @inheritdoc
     */
    public function open()
    {
        if (false !== $this->client) {
            return;
        }

        $this->client = new Client($this->parameters, $this->options);
        $this->initConnection();
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        if (false !== $this->client) {
            $this->client->disconnect();
            $this->client = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return false !== $this->client;
    }

    /**
     * @inheritdoc
     */
    public function getDriverName()
    {
        return 'predis';
    }
}
