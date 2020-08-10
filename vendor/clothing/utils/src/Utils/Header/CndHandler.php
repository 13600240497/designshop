<?php
/**
 * Created by PhpStorm.
 * User: liuxiaojie
 * Date: 2020/02/28
 * Time: 11:24
 */

namespace Clothing\Tools\Utils\Header;

class CndHandler
{

    private static $instances;

    private $cookieHeaders = [];

    private $whiteList = [];

    private $isOpenCdn = false;

    private function __construct()
    {
        header_register_callback(function () {
            if (!$this->isOpenCdn) {
                return;
            }
            $this->cookieHeaders = $this->getCookieHeader();
            //清理掉设置cookie
            $this->clearCookieHeader();
            //重写白名单
            $this->reSetWhiteCookie();

        });
    }

    public static function getInstance()
    {
        if (null == self::$instances) {
            self::$instances = new  self();
        }
        return self::$instances;
    }


    /**
     * 设置白名单
     * @param array $whiteList
     * @return $this
     */
    public function setWhiteList(array $whiteList)
    {
        $this->whiteList = $whiteList;
        return $this;
    }

    public function addWhiteList(array $whiteList)
    {
        if (empty($whiteList)) {
            return $this;
        }
        $this->whiteList = array_unique(array_merge($this->whiteList, $whiteList));
        return $this;
    }


    /**
     * 开启cdn
     */
    public function openCdn()
    {
        $this->isOpenCdn = true;
        return $this;
    }


    /**
     * @return $this
     */
    public function closeCdn()
    {
        $this->isOpenCdn = false;
        return $this;
    }

    /**
     * 清理掉
     */
    private function clearCookieHeader()
    {
        header_remove('Set-Cookie');
    }


    /**
     * 重写白名单Cookie头
     */
    private function reSetWhiteCookie()
    {
        if (empty($this->whiteList)) {
            return;
        }
        foreach ($this->whiteList as $name) {
            if (isset($this->cookieHeaders[$name])) {
                foreach ($this->cookieHeaders[$name] as $val) {
                    header("Set-Cookie:{$val}", false);
                }
                //删掉，不然会多次设置,保证顺序
                unset($this->cookieHeaders[$name]);
            }
        }
    }


    /**
     * 获取设置Cookie头
     * @return array
     */
    private function getCookieHeader()
    {
        $list = headers_list();
        $cookieHeader = [];
        foreach ($list as $val) {
            //只要设置头
            if (strpos(strtolower($val), 'set-cookie') === false) {
                continue;
            }
            $cookies = explode(":", $val, 2);
            if (empty($cookies[1])) {
                continue;
            }
            $names = explode("=", $cookies[1], 2);
            if (count($names) == 1) {
                continue;
            }
            $cookieHeader[trim($names[0])][] = $cookies[1];
        }
        return $cookieHeader;
    }

}
