<?php
namespace ego\curl;

use ego\base\ArrayAccess;

/**
 * `StandardResponseCurl`结果
 *
 * 包含以下键名：
 * - code: 错误码（有请求本来`code`返回10000，但设置`slient(10000)`后，`code`将为0）
 * - message: 信息（像`soa\Service`那样，可统一转化提示信息给到用户）
 * - data: 数据
 * - rawCode: 原错误码
 * - rawMessage: 原信息
 *
 * @property int $code
 * @property string $message
 * @property ArrayAccess|mixed $data
 * @property int $rawCode
 * @property string $rawMessage
 */
class Result extends ArrayAccess
{
}
