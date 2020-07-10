<?php
namespace app\modules\activity\zf\components;

use yii\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use app\modules\common\zf\traits\CommonPublishTrait;

class NativePageFallbackComponent extends Component
{
    use CommonPublishTrait;

    /** @var Client 链接对象 */
    private $client;

    /**
     * 构造函数
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->client = new Client();
    }

    /**
     * @param string $siteCode
     * @param array $pageList
     * @throws \Exception
     */
    public function fallback($siteCode, $pageList)
    {
        $apis = $this->getInterfaceConfig($siteCode);
        $apiInfo = $apis['geshopApi_page_fallback'];

        foreach ($pageList as $pageInfo) {
            $apiParams = [
                'wap' => json_encode($pageInfo['wap']),
                'app' => json_encode($pageInfo['app']),
                'pipeline' => $pageInfo['pipeline'],
                'lang' => $pageInfo['lang'],
            ];

            $fallbackData = $this->getPageFallbackData($apiInfo['url'], $apiParams);
            $format = '请求原生页面兜底数据接口[%s %s],返回: %s';
            \Yii::info(sprintf($format, $apiInfo['url'], json_encode($apiParams), json_encode($fallbackData)));
            $this->pushFallbackJsonToS3($pageInfo, $fallbackData);
        }
    }

    /**
     * @param $pageInfo
     * @param $fallbackData
     * @throws \Exception
     */
    private function pushFallbackJsonToS3($pageInfo, $fallbackData)
    {
        if (!empty($fallbackData['wap'])) {
            $wapFilename = sprintf('wap-component-data-%d.json', $pageInfo['wap']['page_id']);
            $wapJsonBody = Json::encode($fallbackData['wap']);
            $this->pushJsonToS3($pageInfo['wap']['site_code'], $pageInfo['pipeline'], $pageInfo['lang'], $wapFilename, $wapJsonBody);
        }

        if (!empty($fallbackData['app'])) {
            $appFilename = sprintf('app-component-data-%d.json', $pageInfo['app']['page_id']);
            $appJsonBody = Json::encode($fallbackData['app']);
            $this->pushJsonToS3($pageInfo['app']['site_code'], $pageInfo['pipeline'], $pageInfo['lang'], $appFilename, $appJsonBody);
        }
    }

    /**
     * @param $siteCode
     * @param $pipeline
     * @param $lang
     * @param $filename
     * @param $jsonBody
     * @throws \Exception
     */
    private function pushJsonToS3($siteCode, $pipeline, $lang, $filename, $jsonBody)
    {
        $wapRedisData = $this->getPublishRedisData($siteCode, $pipeline, $lang, $filename, $jsonBody);
        app()->swoole->init()->send(['data' => [$wapRedisData], 'action' => 'asyncPushPage', 'mode' => 'multi']);
    }

    /**
     * 获取推送信息
     *
     * @param string $siteCode
     * @param string $pipeline
     * @param string $lang
     * @param string $filename
     * @param string $jsonBody
     * @return array
     * @throws \Exception
     */
    private function getPublishRedisData($siteCode, $pipeline, $lang, $filename, $jsonBody)
    {
        $localFilePath = $this->getPublishFileLocalPath($siteCode, $pipeline, $lang, $filename);
        $absFilePath = $this->getRelativePathByLocalPath($localFilePath);

        if (false === file_put_contents($localFilePath, $jsonBody)) {
            throw new \Exception('文件内容写入失败：' . $localFilePath);
        }

        $item = [
            'local_path'          => $localFilePath,
            's3_key'              => $absFilePath,
            'file_type'           => \app\components\auto\AutoRefreshUi::PUBLISH_ASYNC_DATA_JSON_TYPE,
        ];
        return $item;
    }

    /**
     * 获取推送文件本地路径
     *
     * @param string $siteCode
     * @param string $pipeline
     * @param string $lang
     * @param string $filename
     * @return string 文件本地绝对路径
     */
    private function getPublishFileLocalPath($siteCode, $pipeline, $lang, $filename)
    {
        $publishConfig = app()->params['sites'][ $siteCode ][ 's3PublishPath' ][ $pipeline ] ?? [];
        $publishUri = str_replace('\\', '/', trim($publishConfig[ $lang ], '/'));
        $localPath = \Yii::getAlias('@runtime/'. $publishUri);
        if (!is_dir($localPath) && !mkdir($localPath, 0777, true) && !is_dir($localPath)) {
            return '';
        }
        return $localPath .DIRECTORY_SEPARATOR. $filename;
    }

    /**
     * @param string $apiUrl
     * @param array $params
     * @return array
     * @throws \Exception
     */
    private function getPageFallbackData($apiUrl, $params)
    {
        $options = [
            RequestOptions::VERIFY => false,
            RequestOptions::TIMEOUT => 5
        ];

        if (is_array($params)) {
            $apiUrl .= (strpos($apiUrl, '?') === false) ? '?' : '&';
            $apiUrl .= http_build_query($params);
        }

        if (app()->env->isPreRelease()) {
            $apiDomain = parse_url($apiUrl, PHP_URL_HOST);
            $cookieJar = CookieJar::fromArray(
                ['staging' => 'true'],
                mb_substr($apiDomain, stripos($apiDomain, '.'))
            );
            $options[ RequestOptions::COOKIES ] = $cookieJar;
        }

        $response = $this->client->get($apiUrl, $options);
        $jsonBody = $response->getBody()->getContents();
        $jsonData = Json::decode($jsonBody, true);
        if (!is_array($jsonData) || !array_key_exists('code', $jsonData) || (int)$jsonData['code'] != 0) {
            \Yii::error(sprintf('获取页面兜底数据异常,返回: %s', $jsonBody));
            throw new \Exception('获取页面兜底数据异常');
        }

        return $jsonData['data'];
    }


}
