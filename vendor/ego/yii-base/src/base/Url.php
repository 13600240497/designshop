<?php
namespace ego\base;

use yii\base\Component;

/**
 * url组件
 */
class Url extends Component
{
    /**
     * 追加查询字符串
     *
     * 该方法自动判断是添加**?**或者**&**
     *
     * @param string $url 需要追加的url
     * @param array|string|null $append 追加的查询字符串或者一个用于`http_build_query`的数组
     * @return string
     */
    public function append($url, $append)
    {
        if ('' === $append || null === $append) {
            return $url;
        } elseif (is_array($append)) {
            $append = http_build_query($append);
        }

        if (false === strpos($url, '?')) {
            return $url . '?' . $append;
        } else {
            return $url . '&' . $append;
        }
    }
}
