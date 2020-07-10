<?php
namespace app\common\util;


/**
 * 根据UI变化版本的值
 */
class CssJsVersionManage
{
    /**
     * 返回修改时间
     * @return false|string
     */
    static public function info()
    {
        return app()->cache->getOrSet('geshop:css_js_version',function (){
            $cssVersion = date('YmdHis');
            \Yii::info('Redis geshop:css_js_version is flush :' . $cssVersion);
            return $cssVersion;
        });
    }

    /**
     * 刷新缓存
     * @param $file String 文件路径
     * @return false|string
     */
    static public function reFlush()
    {
        $cssVersion = date('YmdHis');
        app()->cache->set('geshop:css_js_version',$cssVersion);
        \Yii::info('Redis geshop:css_js_version is flush :' . $cssVersion);
        return $cssVersion;
    }
}