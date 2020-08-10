<?php
namespace ego\base;

use yii\base\Component;
use GuzzleHttp\{Client, Pool};
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

/**
 * cdn组件
 */
class Cdn extends Component
{
    /**
     * @var string 清除api
     */
    public $api;
    /**
     * 缓存配置
     * ```php
     *  [
     *      'home/index/index' => 1
     *      'goods/category/index' => 6
     *  ]
     * ```
     * @var array
     */
    public $config = [];

    /**
     * 开启cdn缓存
     *
     * @return bool
     */
    public function open()
    {
        if (empty($this->config[app()->requestedRoute])
            || $this->config[app()->requestedRoute] <= 0
            || $this->config[app()->requestedRoute] < 0
            || 200 != app()->response->statusCode
            || 200 != http_response_code()
        ) {
            return false;
        }
        $expires = $this->config[app()->requestedRoute] * 3600;
        app()->response->getHeaders()
            ->set('Pragma', 'public')
            ->set('Cache-Control', 'public, max-age=' . $expires)
            ->set('Expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        return true;
    }

    /**
     * 关闭cdn缓存
     *
     * @return bool
     */
    public function close()
    {
        app()->response->getHeaders()->remove('Pragma');
        app()->response->getHeaders()->remove('Cache-Control');
        app()->response->getHeaders()->remove('Expires');
        return true;
    }

    /**
     * 清除cdn缓存
     *
     * @param string|array $urls
     * @return array
     */
    public function clear($urls)
    {
        $urls = (array) $urls;
        $result = [];
        $client = new Client();
        $pool = new Pool(
            $client,
            $this->yieldRequests($client, $urls),
            [
                'concurrency' => 25,
                'fulfilled' => function($response, $index) use(&$result, $urls) {
                    /** @var Response $response */
                    $result[$urls[$index]] = $response->getBody() . '';
                },
                'rejected' => function($e, $index) use (&$result, $urls) {
                    /** @var RequestException $e */
                    $result[$urls[$index]] = $e->getMessage();
                },
            ]
        );
        $pool->promise()->wait();
        \Yii::info($result, __METHOD__);
        //return $result;
    }

    /**
     * 生成一个请求
     *
     * @param Client $client
     * @param array $urls
     * @return \Generator
     */
    protected function yieldRequests($client, array $urls)
    {
        foreach ($urls as $url) {
            yield function() use ($client, $url) {
                return $client->getAsync($this->api . $url);
            };
        }
    }
}
