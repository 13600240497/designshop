<?php

namespace app\modules\base\models;

use app\models\ActiveRecord;


/**
 * 请求日志数据模型
 *
 * @property int    $id
 * @property string $request_id
 * @property string $website_code
 * @property string $request_date
 * @property string $username
 * @property int    $page_id
 * @property string $module
 * @property string $request_route
 * @property string $request_url
 * @property string $method
 * @property int    $request_time
 * @property string $user_ip
 * @property string $post_params
 */
class SysRequestLogModel extends ActiveRecord
{
    public $request_route_rule = [
        '/activity/zf/ui-design/get-form' =>'获取组件信息',
        '/activity/zf/ui-design/save-form' =>'配置组件信息',
        '/activity/zf/design/index' =>'装修页面',
        '/activity/zf/activity/list' =>'显示活动列表',
        '/activity/zf/page/list' =>'显示页面列表',
        '/activity/zf/design/batch-release' =>'发布',
        '/activity/zf/native-design/native' =>'装修原生页面',
        '/activity/zf/page/pipeline-newest-urls' =>'获取页面访问链接地址',
        '/activity/zf/goods/tpl-goods-list' =>'模板组件-商品管理列表',
        '/home/zf/ui-design/get-form' =>'获取首页配置信息',
        '/activity/zf/activity/index' =>'打开活动首页',
        '/component/index/list' =>'组件列表',
        '/activity/zf/native-design/native-index' =>'打开原生装修页',
        '/activity/zf/design/get-copy-pipeline' =>'获取渠道配置',
        '/activity/zf/design/copy-pipeline' =>'跨渠道复制',
        '/activity/zf/design/preview' =>'页面预览',
        '/home/zf/ui-design/save-form' =>'配置首页信息',
        '/activity/zf/ui-design/save-native-form' =>'原生页面配置',
        '/home/zf/design/index' =>'首页装修',
        '/activity/zf/layout-design/add-layout' =>'添加布局组件',
        '/activity/zf/ui-design/add-ui' =>'添加UI组件',
        '/activity/zf/page/add' =>'添加页码',
        '/activity/zf/ui-design/move-ui' =>'移动组件',
        '/activity/zf/design/preload-release' =>'预加载发布页面数据',
    ];

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'id';
    }

    /**
     * 查找页面操作数据
     * @param $id
     * @return array|self[]
     */
    public function getRequestLogByid($id)
    {
        return static::find()->select('request_date,username,request_route,request_url,request_time')
            ->where(['page_ids' => (int)$id])
            ->all();
    }

    /**
     * 获取加工后显示的记录信息
     * @return string
     */
    public function getDetailMessage()
    {
        $detail = $this->getDetail($this->request_url);
        $sub = '';
        if (isset($detail[1])) {
            $sub = '参数:'.json_encode($detail[1]);
        }
        $result =  date('Y-m-d h:i:s',$this->request_time) . ' ' . $this->username . ' ' . $detail[0] . ' ' .$sub;
        return $result;
    }


    public function getDetail($str = null)
    {
        $arr = parse_url($str);
        $message = $this->request_route_rule[$arr['path']] ?? $arr['path'];
        $queryArr = null;
        if (isset($arr['query'])) {
            $queryArr = $this->convertUrlQuery($arr['query']);
        }
        return [$message, $queryArr];
    }

    /**
     * 将字符串参数变为数组
     * @param $query
     * @return array
     */
    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
}