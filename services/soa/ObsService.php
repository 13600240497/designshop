<?php
namespace app\services\soa;

/**
 * OBS系统SOA服务
 *
 * @see http://wiki.hqygou.com:8090/pages/viewpage.action?pageId=83100619
 * @author chenliangliang
 * @since 1.6.2
 */
class ObsService extends Service
{
    /** @var array 选品系统配置 */
    private $config;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset(app()->params['soa']['obs'])) {
            throw new \Exception('没有obs系统配置');
        }

        $config = app()->params['soa']['obs'];
        if (!isset($config['apiUrlPrefix'])) {
            throw new \Exception('没有配置obs系统地址');
        }

        $this->config = $config;
    }

    /**
     * 获取主题信息
	 *
     * @return \ego\curl\Result|array
     */
    public function getThemeList()
    {
        return $this->get($this->getApiUrl('theme'),[]);
    }

    /**
     * 根据主题获取页面
     *
     * @param array $params API参数
     *  - 字段名称   类型    必填     说明
     *  - theme_id   int	 Y     	  主题ID
     *
     * @return \ego\curl\Result|array
     */
    public function getPageList($params)
    {
        return $this->get($this->getApiUrl('theme-page'), $params);
    }

    /**
     * 根据页面获取版块
     *
     * @param array $params API参数
     *  - 字段名称                      类型    必填     说明
     *  - page_id      					int	    Y        页面ID
     *
     * @return \ego\curl\Result|array
     */
    public function getSectionList($params)
    {
        return $this->get($this->getApiUrl('page-section'), $params);
    }

    /**
     * 获取版块已选SKU
     *
     * @param array $params API参数
     *  - 字段名称                类型    必填     说明
     *  - section_id      		  int	   Y       版块ID
     *
     * @return \ego\curl\Result|array
     */
    public function getProductList($params)
    {
        return $this->get($this->getApiUrl('section-goods'), $params);
    }


    /**
     * 获取完整API请求地址
     * @param string $apiUri
     * @return string
     */
    private function getApiUrl($apiUri)
    {
        return $this->getFullApiUrl($this->config['apiUrlPrefix'], $apiUri);
    }

    /**
     * 发送get请求
     * @param   string     $url     请求地址
     * @param   array      $params
     * @return  array
     */
    private function get($url,$params)
    {
        if(!empty($params)){
            $str = '';
            foreach ($params as $key => $value) {
                $str .= '&'.$key.'='.$value;
            }
            $url .= '?'.trim($str,'&');
        }
        $options = [];
        if(app()->env->isLocal()){
            $options['headers'] = ['user-agent'=>'weitengfei003'];
        }
        return $this->request('GET', $url, $options);
    }
}
