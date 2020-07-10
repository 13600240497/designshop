<?php

namespace app\base;

class TwigFunctions
{
    /**
     * html编码商品信息里的promotions字段
     * @param array $goodList
     * @return array
     */
    public static function htmlEncoderGoodsPromotions($goodList)
    {
        if (!empty($goodList) && is_array($goodList)) {
            foreach ($goodList as $index => $goodsInfo) {
                if (isset($goodsInfo['promotions']) && is_array($goodsInfo['promotions'])) {
                    foreach ($goodsInfo['promotions'] as $key => $value) {
                        $goodList[$index]['promotions'][$key] = htmlentities($value); //str_replace('"', '&quot;', $value);
                    }
                }
            }
        }
        return $goodList;
    }

    public static function jsonDecode($json) {
        return json_decode($json, true);
    }

    public static function jsonEncodeNoUnicode($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}