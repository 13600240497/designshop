<?php
namespace ego\socket;

use yii\base\Component;
use ego\socket\exception\SocketException;

/**
 * Client Socket, provide method for working with client socket
 * @package ego\socket\Client
 */
class Client extends Component
{
    /**
     * @var string 远程地址
     */
    private $address;
    /**
     * @var stream_socket_client
     */
    protected $fp;
    /**
     * 数据长度最大字节数
     *
     * @var int
     */
    const MAX_BYTE_COUNT = 5;
    /**
     * socket 连接的timeout时间
     *
     * @var int
     */
    const MAX_TIME_OUT = 3;

    /**
     * Connect to server
     *
     * @throws ConnectionException
     */
    public function connect()
    {
        if (!($this->fp = @stream_socket_client($this->address, $errno, $errstr, self::MAX_TIME_OUT))) {
            throw new SocketException('errorCode['. $errno . ']:' . $errstr);
        }
    }

    /**
     * @inheritDoc
     * @see \yii\base\Object::init()
     */
    public function init()
    {
        parent::init();
        $this->connect();
    }

    /**
     * write data to socket
     *
     * @param string $data
     * @return bool|throw SocketException
     */
    public function write($data)
    {
        if (!fwrite($this->fp, $data)) {
            throw new SocketException("socket write:{$data} error");
        }
        return true;
    }

    /**
     * read data from socket
     *
     * @param number $length
     * @throws SocketException
     * @return string
     */
    public function read($length = 1024)
    {
        if (false === ($data = fread($this->fp, $length))) {
            throw new SocketException('socket read error');
        }
        return $data;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
}
