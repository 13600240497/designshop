<?php
/**
 * Created by PhpStorm.
 * User: yuanwenguang
 * Date: 2019/4/18
 * Time: 14:25
 */
namespace app\modules\common\components;

use ego\curl\StandardResponseCurl;

class CommonIpsComponent extends Component{

    /**
     * 获取ips系统用户页面权限
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author yuanwenguang 2019/4/18 14:41
     */
    public static function getIpsPageAuth($params = []){
        $is_auto_activity = empty($params['is_auto_activity']) ? 2 : $params['is_auto_activity']; //是否自动选品,1手动，2自动，默认自动
        $ips_params = [
            "username" => app()->user->username,
        ];
        if($is_auto_activity == 1){
            //手动选品
            $ips_params['module'] = "filter";
        }else{
            //自动选品
            $ips_params['module'] = "auto_rule";
        }
        $token_data = json_encode($ips_params);
        $post_data['sn'] = app()->params['soa']['ips']['sn'];
        $post_data["token"] = md5(app()->params['soa']['ips']['key'] . $token_data . $post_data['sn']);
        $post_data['data'] = $token_data;
        $url = app()->params['soa']['ips']['apiUrlPrefix']."/system-user/get-user-auth";
        try{
            $curl = new StandardResponseCurl();
            $response = $curl->slient()->asArray()->request(
                "POST",
                $url,
                ['form_params' => $post_data]
            );
            if(!empty($response)){
                if(isset($response['code']) && $response['code'] == 0){
                    if(isset($response['data']['is_has_auth']) && $response['data']['is_has_auth'] == false){
                        return app()->helper->arrayResult(0,"无ips权限，请走we流程申请",$response['data']);
                    }elseif (isset($response['data']['is_has_auth']) && $response['data']['is_has_auth'] == true){
                        return app()->helper->arrayResult(0,"success",$response['data']);
                    }
                    return app()->helper->arrayResult(0,"ips返回结构有误",$response['data']);
                }
                return app()->helper->arrayResult(1,"ips返回错误信息：".$response['message']);
            }else{
                return app()->helper->arrayResult(1,"请求出错");
            }
        }catch (\Exception $e){
            return app()->helper->arrayResult(1,$e->getMessage());
        }
    }
}