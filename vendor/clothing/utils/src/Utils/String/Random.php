<?php

namespace Clothing\Tools\Utils\String;

/**
 * 随机字符生成
 *
 * @author zhouyl <zhouyonglong@globalegrow.com>
 */
class Random
{
    /**
     * 经实际测试，在开启 5000 并发下，生成约 100 亿个 id，未发现重复 (未测试更多 id 与并发)
     *
     * @param int $length 唯一 id 的字符长度应当 > 16, < 32
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function uniqueID($length = 24)
    {
        if ($length > 32) {
            $length = 32;
        }

        if ($length < 16) {
            throw new \InvalidArgumentException(
                'Invalid unique id length. (> 16 and < 32)'
            );
        }

        return substr(md5(uniqid('', true) . mt_rand(1000000, 9999999)), 0, 24);
    }
}
