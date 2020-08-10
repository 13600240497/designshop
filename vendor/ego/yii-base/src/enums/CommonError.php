<?php
namespace ego\enums;

use yii;

/**
 * 通用错误
 *
 * 错误码范围：9000~9999
 */
class CommonError extends AbstractEnum
{
    /**
     * @var string 错误翻译信息分类
     */
    const CATEGORY = 'common-error';
    /**
     * @var int 系统繁忙错误码
     */
    const ERR_SYSTEM_BUSY = 9000;
    /**
     * @var int curl请求失败，如404，500等状态码
     */
    const ERR_CURL_REQUEST_FAIL = 9001;
    /**
     * @var int curl返回格式错误，如`code`不等于0
     */
    const ERR_CURL_RESPONSE_FAIL = 9002;

    /**
     * 获取错误翻译信息
     *
     * @param int $code 错误码
     * @param array $params 翻译信息中占位符替换键值对数组
     * @param string $category 错误分类
     * @return string
     */
    public static function getMessage($code, $params = [], $category = null)
    {
        $flipConstants = static::getAll(true);
        if (isset($flipConstants[$code])) {
            return Yii::t(
                $category ?: static::CATEGORY,
                $flipConstants[$code],
                $params
            );
        } else {
            return Yii::t(self::CATEGORY, 'ERR_SYSTEM_BUSY', $params);
        }
    }
}
