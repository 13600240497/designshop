<?php
namespace ego\base;

use yii\base\Component;

/**
 * 数据加解密组件
 */
class Crypt extends Component
{
    /**
     * @var string 安全密钥
     */
    public $key = '';
    /**
     * @var string 过期加密前缀
     */
    const EXPIRE_PREFIX = 'EXPIRE_PREFIXDcOQplM';

    /**
     * 加密
     *
     * @param string $data 需要加密的字符串
     * @param int $expires 过期时间
     * @param string|null $key 密钥
     * @return string
     */
    public function encode($data, $expires = 0, $key = null)
    {
        if ($expires) {
            $data = static::EXPIRE_PREFIX . (time() + $expires) . $data;
        }
        return base64_encode(app()->getSecurity()->encryptByKey(
            $data,
            null === $key ? $this->key : $key
        ));
    }

    /**
     * 解密
     *
     * @param string $data 需要加密的字符串
     * @param string|null $key 密钥
     * @return string|false|null
     * - 解密失败返回`false`
     * - 过期时返回`null`
     * - 否则返回解密的字符串
     */
    public function decode($data, $key = null)
    {
        $decode = app()->getSecurity()->decryptByKey(
            base64_decode($data),
            null === $key ? $this->key : $key
        );
        if (false === $decode) {
            \Yii::warning('解密字符串失败: ' . $data);
            return false;
        } elseif (0 !== strpos($decode, static::EXPIRE_PREFIX)) {
            return $decode;
        }

        $len = strlen(static::EXPIRE_PREFIX);
        // expired
        $expires = substr($decode, $len, 10);
        if ($expires < time()) {
            return null;
        } else {
            return (string) substr($decode, $len + 10);
        }
    }
}
