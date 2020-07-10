#!/usr/bin/env php
<?php
// example: /usr/bin/php /path/to/ApolloClient.php --application=GB --namespace=application,gb.cart
ini_set('memory_limit', '32M');
set_time_limit(0);

//指定Apollo的服务地址
$server = empty(getenv('APOLLO_SERVER')) ? 'http://config.gw-ec.com/meta' : getenv('APOLLO_SERVER');

$opts = getopt('a:n:', ['application:', 'namespace:']);
//指定appid
$appid = (isset($opts['a']) && !empty($opts['a'])) ? $opts['a'] : ((isset($opts['application']) && !empty($opts['application'])) ? $opts['application'] : 'GB');
//指定要拉取哪些namespace的配置
$namespaces = (isset($opts['n']) && !empty($opts['n'])) ? $opts['n'] : ((isset($opts['namespace']) && !empty($opts['namespace'])) ? $opts['namespace'] : 'application');
$namespaces = explode(',', $namespaces);

$serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
$apollo = new ApolloClient($server, $appid, $namespaces);
$apollo->setClientIp($serverIp);

//从apollo上拉取的配置默认保存在脚本目录，可自行设置保存目录
$apollo->save_dir = empty(getenv('APOLLO_CONF_DIR')) ? '/data/www/apollo-config' : getenv('APOLLO_CONF_DIR');
if (!is_dir($apollo->save_dir)) {
    mkdir($apollo->save_dir, 0777, true);
}

if ($serverIp) {
    echo "serverIp [$serverIp]\n";
}
$pid = getmypid();
echo "start [$pid]\n";
$restart = false; //失败自动重启
do {
    $error = $apollo->start(); //此处传入回调
    if ($error) echo('error:' . $error . "\n");
} while ($error && $restart);


class ApolloClient
{
    protected $configServer; //apollo服务端地址
    protected $appId; //apollo配置项目的appid
    protected $cluster = 'default';
    protected $clientIp = ''; //绑定IP做灰度发布用
    protected $notifications = [];
    protected $pullTimeout = 10; //获取某个namespace配置的请求超时时间
    protected $intervalTimeout = 90; //每次请求获取apollo配置变更时的超时时间(apoll根据notificationId判断如果都是最新的，则保持住请求60秒，如果60秒内没有配置变化，则返回HttpStatus 304)
    public $save_dir; //配置保存目录

    /**
     * ApolloClient constructor.
     * @param string $configServer apollo服务端地址
     * @param string $appId apollo配置项目的appid
     * @param array $namespaces apollo配置项目的namespace
     */
    public function __construct($configServer, $appId, array $namespaces)
    {
        $this->configServer = $configServer;
        $this->appId = $appId;
        foreach ($namespaces as $namespace) {
            $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
        }
        $this->save_dir = dirname($_SERVER['SCRIPT_FILENAME']);;
    }

    public function setCluster($cluster)
    {
        $this->cluster = $cluster;
    }

    public function setClientIp($ip)
    {
        $this->clientIp = $ip;
    }

    public function setPullTimeout($pullTimeout)
    {
        $pullTimeout = intval($pullTimeout);
        if ($pullTimeout < 1 || $pullTimeout > 300) {
            return;
        }
        $this->pullTimeout = $pullTimeout;
    }

    public function setIntervalTimeout($intervalTimeout)
    {
        $intervalTimeout = intval($intervalTimeout);
        if ($intervalTimeout < 1 || $intervalTimeout > 300) {
            return;
        }
        $this->intervalTimeout = $intervalTimeout;
    }

    public function arr2ini($configs)
    {
        $content = "";

        foreach ($configs as $key => $value) {
            $content .= $key . "=" . $value . "\n";
        }

        return $content;
    }

    //获取单个namespace的配置文件路径
    public function getConfigFile($namespaceName)
    {
        return $this->save_dir . DIRECTORY_SEPARATOR . $this->appId . '.' . $namespaceName . '.ini';
    }

    private function _getReleaseKey($config_file)
    {
        $releaseKey = '';
        if (file_exists($config_file)) {
            $last_config = parse_ini_file($config_file);
            is_array($last_config) && isset($last_config['apolloReleaseKey']) && $releaseKey = $last_config['apolloReleaseKey'];
        }
        return $releaseKey;
    }

    //获取多个namespace的配置-无缓存的方式
    public function pullConfigBatch(array $namespaceNames)
    {
        if (!$namespaceNames) return [];
        $multi_ch = curl_multi_init();
        $request_list = [];
        $base_url = rtrim($this->configServer, '/') . '/configs/' . $this->appId . '/' . $this->cluster . '/';
        $query_args = [];
        if ($this->clientIp) {
            $query_args['ip'] = $this->clientIp;
        };
        foreach ($namespaceNames as $namespaceName) {
            $request = [];
            $config_file = $this->getConfigFile($namespaceName);
            $request_url = $base_url . $namespaceName;
            $query_args['releaseKey'] = $this->_getReleaseKey($config_file);
            $query_string = '?' . http_build_query($query_args);
            $request_url .= $query_string;
            $ch = curl_init($request_url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->pullTimeout);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $request['ch'] = $ch;
            $request['config_file'] = $config_file;
            $request_list[$namespaceName] = $request;
            curl_multi_add_handle($multi_ch, $ch);
        }

        $active = null;
        // 执行批处理句柄
        do {
            $mrc = curl_multi_exec($multi_ch, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi_ch) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($multi_ch, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        }

        // 获取结果
        $response_list = [];
        foreach ($request_list as $namespaceName => $req) {
            $response_list[$namespaceName] = true;
            $result    = curl_multi_getcontent($req['ch']);
            $curl_info = curl_getinfo($req['ch']);
            $code      = $curl_info['http_code'];
            $error     = curl_error($req['ch']);
            curl_multi_remove_handle($multi_ch, $req['ch']);
            curl_close($req['ch']);
            if ($code == 200) {
                $result = json_decode($result, true);
                $configurations = isset($result['configurations']) ? $result['configurations'] : [];
                if (isset($result['releaseKey'])) {
                    $configurations['apolloReleaseKey'] = $result['releaseKey'];
                }
                $content = $this->arr2ini($configurations);
                file_put_contents($req['config_file'], $content);
            } elseif ($code != 304) {
                echo 'pull config of namespace[' . $namespaceName . '] error:' . ($result ?: $error) . " curl info:".json_encode($curl_info)."\n";
                $response_list[$namespaceName] = false;
            }
        }
        curl_multi_close($multi_ch);
        return $response_list;
    }

    protected function _listenChange(&$ch, $callback = null)
    {
        $base_url = rtrim($this->configServer, '/') . '/notifications/v2?';
        $params = [];
        $params['appId'] = $this->appId;
        $params['cluster'] = $this->cluster;
        do {
            sleep(2);
            $params['notifications'] = json_encode(array_values($this->notifications));
            $query = http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $base_url . $query);
            $response  = curl_exec($ch);
            $curl_info = curl_getinfo($ch);
            $httpCode  = $curl_info['http_code'];
            $error = curl_error($ch);
            if ($httpCode == 200) {
                $res = json_decode($response, true);
                $change_list = [];
                foreach ($res as $r) {
                    if ($r['notificationId'] != $this->notifications[$r['namespaceName']]['notificationId']) {
                        $change_list[$r['namespaceName']] = $r['notificationId'];
                    }
                }
                $response_list = $this->pullConfigBatch(array_keys($change_list));
                foreach ($response_list as $namespaceName => $result) {
                    $result && ($this->notifications[$namespaceName]['notificationId'] = $change_list[$namespaceName]);
                }
                //如果定义了配置变更的回调，比如重新整合配置，则执行回调
                ($callback instanceof \Closure) && call_user_func($callback);
            } elseif ($httpCode != 304) {
                throw new \Exception($response ?: $error." curl_info:".json_encode($curl_info)."\n");
            }
        } while (true);
    }

    /**
     * @param $callback 监听到配置变更时的回调处理
     * @return mixed
     */
    public function start($callback = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->intervalTimeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        try {
            $this->_listenChange($ch, $callback);
        } catch (\Exception $e) {
            curl_close($ch);
            return $e->getMessage();
        }
    }
}
