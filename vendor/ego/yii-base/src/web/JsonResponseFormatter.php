<?php
namespace ego\web;

use ego\enums\CommonError;

/**
 * json响应
 *
 * json返回格式抛出异常时，yii默认返回类似：
 * ```php
 *  [
 *      'name' => 'OK',
 *      'message' => 'success'
 *      'code' => 0,
 *      'status' => 500, // 可选
 *      ...
 *  ]
 * ```
 * 转换后，将返回类似数据：
 * ```php
 *  [
 *      'message' => 'success'
 *      'code' => 0,
 *      'data' => null
 *      'localData' => [ // 开发环境下
 *          原始数据
 *      ]
 *  ]
 * ```
 * 如果`$_GET[$jsonpCalbackName]`不为空，自动转化为jsonp格式
 */
class JsonResponseFormatter extends \yii\web\JsonResponseFormatter
{
    /**
     * @var string jsonp请求callback参数名
     */
    public $jsonpCalbackName = 'callback';

    /**
     * @inheritdoc
     */
    public function format($response)
    {
        $this->parseResponse($response);
        parent::format($response);
    }

    /**
     * 解析响应
     *
     * @param \yii\web\Response $response
     * @return mixed
     */
    private function parseResponse($response)
    {
        if (0 !== strpos($response->format, 'json')) {
            return $response->data;
        } elseif (200 != $response->statusCode
            && isset($response->data['code'], $response->data['message'])
            && 0 == $response->data['code']
        ) {
            $response->data = app()->helper->arrayResult(
                CommonError::ERR_SYSTEM_BUSY,
                $response->data['message'],
                null,
                $response->data
            );
        } elseif (isset($response->data['name'], $response->data['code'], $response->data['message'])) {
            $response->data = app()->helper->arrayResult(
                $response->data['code'],
                $response->data['message'],
                null,
                $response->data
            );
        }

        if ($callback = app()->request->get($this->jsonpCalbackName)) {
            $this->useJsonp = true;
            $response->data = [
                'data' => $response->data,
                'callback' => $callback,
            ];
        }
        return $response->data;
    }
}
