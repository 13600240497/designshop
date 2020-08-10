<?php

if (! function_exists('get_local_ipv4_addr')) {
    /**
     * 使用 socket 的方式，获取本机 ipv4 地址
     *
     * @return string
     */
    function get_local_ipv4_addr()
    {
        if (!function_exists('socket_create')) {
            return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        }

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        socket_connect($sock, '8.8.8.8', 53);
        socket_getsockname($sock, $address);
        socket_close($sock);

        return $address;
    }
}

/*
 * Get all HTTP header key/values as an associative array for the current request.
 *
 * @return string[string] The HTTP header key/value pairs
 */
if (! function_exists('get_all_headers')) {
    function get_all_headers(array $servers = null)
    {
        if (! is_array($servers)) {
            $servers = $_SERVER ?: [];
        }

        $headers     = [];
        $copyservers = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];
        foreach ($servers as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (! isset($copyservers[$key]) || ! isset($servers[$key])) {
                    $key           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copyservers[$key])) {
                $headers[$copyservers[$key]] = $value;
            }
        }
        if (! isset($headers['Authorization'])) {
            if (isset($servers['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $servers['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($servers['PHP_AUTH_USER'])) {
                $basic_pass               = isset($servers['PHP_AUTH_PW']) ? $servers['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($servers['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($servers['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $servers['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}

if (! function_exists('exception_as_string')) {
    /**
     * 将异常转化为详细的错误信息
     *
     * @param \Exception|\Throwable $e
     *
     * @return null|string
     */
    function exception_as_string($e)
    {
        if ($e instanceof Exception || $e instanceof Throwable) {
            return sprintf(
                '%s(%s): %s in file %s on line %s.',
                get_class($e),
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
        }

        return null;
    }
}

if (! function_exists('exception_brief')) {
    /**
     * 获取异常的摘要信息（包括了错误所在的文件、行、代码）
     *
     * @param \Exception|\Throwable $e
     *
     * @return null|array
     */
    function exception_brief($e)
    {
        if ($e instanceof Exception || $e instanceof Throwable) {
            return [
                'exception' => get_class($e),
                'code'      => $e->getCode(),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ];
        }

        return null;
    }
}
