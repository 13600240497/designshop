<?php
namespace ego\soa;

use yii;
use ego\soa\socket\Client;
use Protobuf\Request;
use Protobuf\Request_Header;
use Protobuf\Response;

/**
 * 服务组件
 */
class SocketService extends Service
{
    /**
     * @var string 地址
     */
    public $address;
    /**
     * @var \ego\soa\socket\Client
     */
    private static $_client;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->address = app()->params['service']['tcp_address'];
        $this->url = $this->address;
    }

    /**
     * @inheritDoc
     */
    public function getClient($forceNew = false)
    {
        if ($forceNew) {
            return new Client([
                'address' => $this->address,
            ]);
        } elseif (!self::$_client) {
            self::$_client = new Client([
               'address' => $this->address,
            ]);
        }
        return self::$_client;
    }

    /**
     * @inheritDoc
     */
    protected function buildRequestBody($service, $method, array $body = [])
    {
        $request = new Request();
        $header = new Request_Header();

        $header->setMethod($method);
        $header->setService($service);
        $header->setTokenId(app()->params['service']['tcp_token']);
        $header->setType(1); //1：为数据 2：心跳
        $version = isset($body['_version']) ? $body['_version'] : app()->params['service']['tcp_version'];
        $header->setVersion($version);
        $domain = isset($body['_domain']) ? $body['_domain'] : '';
        unset($body['_version']);
        unset($body['_domain']);
        $header->setDomain($domain);
        //$header->setMId(1); //业务ID
        if (!isset($body['siteCode'])) {
            $body['siteCode'] = app()->site->code;
        } elseif (false === $body['siteCode']) {
            unset($body['siteCode']);
        }
        $this->logItem->params = [
            'header' => [
                'method' => $method,
                'service' => $service,
                'type' => 1,
                'version' => $version,
                'domain' => $domain,
                'tokenId' => app()->params['service']['tcp_token'],
            ],
            'body' => $body
        ];
        $request->setHeader($header);
        $request->setBody(json_encode($body));
        $data = $request->serializeToString();

        return $this->getByte(strlen($data)) . $data;
    }

    /**
     * @inheritdoc
     */
    protected function requestInternal($service, $method, array $params)
    {
        $body = $this->buildRequestBody($service, $method, $params);
        Yii::beginProfile('soa-socket');
        $res = $this->parseSocketResponse($this->getClient()->post($body));
        Yii::endProfile('soa-socket');
        return $res;
    }

    /**
     * 获取二进制字符串前面加上其长度算法字节
     *
     * @param string $data
     * @return string
     */
    private function getByte($value)
    {
        $dataLengthByte = '';
        while (true){
            if (($value & ~0x7F) == 0) {
                $dataLengthByte .= pack('c', $value);
                return $dataLengthByte;
            } else {
               $b = (($value & 0x7F) | 0x80);
               $dataLengthByte .= pack('c', $b);
               $value = $value >> 7;
            }
        }
    }

    /**
     * soa返回过来的数据，解密，并返回response数据
     *
     * @param string $res soa返回过来的数据
     * @return \GuzzleHttp\Psr7\Response
     */
    public function parseSocketResponse($res)
    {
        $response = new Response();
        $response->mergeFromString($res);
        $header = $response->getHeader();
        $body = $response->getBody();

        $body = json_decode($body, true);
        $bodyReturn =[
            'code' => $header->getCode(),
            'message' => $header->getMessage(),
            'data' => isset($body['data']) ? $body['data'] : [],
        ];

        return new \GuzzleHttp\Psr7\Response('200', [], json_encode($bodyReturn));
    }
}
