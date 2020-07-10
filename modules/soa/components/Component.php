<?php
namespace app\modules\soa\components;

use app\modules\soa\traits\MagicPropertyTrait;
use app\modules\soa\traits\ErrorMessageTrait;
use ego\base\JsonResponseException;
use ego\enums\CommonError;
use app\base\Validate;
use app\base\SitePlatform;

/**
 * SOA服务模块基础组件
 *
 * @author TianHaisen
 * @since 1.5.0
 */
class Component extends \yii\base\Component
{
    use MagicPropertyTrait;
    use ErrorMessageTrait;

    /**
     * 转换geshop站点组简码 到 通用网站简码
     * @param string $groupCode geshop站点组简码
     * @return string
     */
    protected function transGeshopGroupCodeToWebSiteCode($groupCode)
    {
        return app()->params['soa']['common']['groupCodeToWebsiteCodeMapping'][$groupCode] ?? NULL;
    }

    /**
     * 转换geshop站点简码 到 通用网站简码
     * @param string $siteCode geshop站点简码
     * @return string
     */
    protected function transGeshopSiteCodeToWebSiteCode($siteCode)
    {
        $siteGroupCode = SitePlatform::getSiteGroupCodeBySiteCode($siteCode);
        return $this->transGeshopGroupCodeToWebSiteCode($siteGroupCode);
    }

    /**
     * 创建Json返回异常
     *
     * @param string $errorMessage 错误信息
     * @param array $data 返回数据
     * @return \ego\base\JsonResponseException
     */
    public function newJsonException($errorMessage=NULL, $data=[])
    {
        empty($errorMessage) && $errorMessage = $this->msgFail;
        return new JsonResponseException($this->codeFail, $errorMessage, $data);
    }

    /**
     * 成功json返回
     *
     * @param array $data 返回数据
     * @param string $message 成功信息
     * @return array
     */
    protected function jsonSuccess($data=[], $message=NULL)
    {
        empty($message) && $message = $this->msgSuccess;
        return app()->helper->arrayResult($this->codeSuccess, $message, $data);
    }

    /**
     * 检查标准接口返回数据(返回结果包含`code`、`data`、`message`的标准结构)是否错误
     *
     * @param array $result
     * @param string $appName
     * @throws \ego\base\JsonResponseException
     */
    protected function checkApiStandardResponse($result, $appName='')
    {

        if (!is_array($result) || !array_key_exists('code', $result) || !array_key_exists('data', $result)
                || !array_key_exists('message', $result)) {
            throw $this->newJsonException('API接口返回不是标准格式！');
        }

        $code = (int)$result['code']; //CommonError::ERR_CURL_RESPONSE_FAIL
        if (CommonError::ERR_CURL_REQUEST_FAIL === $code) {
            throw $this->newJsonException($appName .'请求API接口失败！');
        }

        if ($code > 0) {
            throw $this->newJsonException(sprintf($appName. 'API接口错误: %s', $result['message']));
        }
    }

    /**
     * 检查请求参数
     *
     * @param array $params 请求参数
     * @param array $rules 验证规则
     * @throws \ego\base\JsonResponseException
     */
    protected function checkRequestParams($params, $rules)
    {
        $validator = new Validate();
        if (!$validator->check($params, $rules)) {
            throw $this->newJsonException($validator->getError());
        }
    }
}