<?php
namespace ego\soa\socket;

use Google\Protobuf\Internal\GPBWire;
use ego\socket\exception\SocketException;
use Google\Protobuf\Internal\InputStream;

/**
 * Client Socket, provide method for working with client socket
 */
class Client extends \ego\socket\Client
{
    /**
     * @var int 一次性读的长度
     */
    const ONE_READ_LENGTH = 8192;

    /**
     * write and read data form soa
     *
     * @param string $data write data
     * @throws SocketException
     * @return string
     */
    public function post($data)
    {
        $this->write($data);

        $content= $this->read(static::MAX_BYTE_COUNT);
        $length = $content;
        if (!empty($length)) {
            $input = new InputStream($length);
            $input->readVarint32($length);
            $lengthByteCount = GPBWire::varint32Size($length);
            $length = $length - self::MAX_BYTE_COUNT + $lengthByteCount;
        } else {
            throw new SocketException('read tag length error');
        }
        return $content .= stream_get_contents($this->fp, $length);
    }
}
