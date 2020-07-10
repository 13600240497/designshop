<?php

namespace app\modules\activity\dl\traits;

/**
 * 错误提示
 */
trait ErrorMessageTrait
{
    /**
     * @var int 返回成功的code
     */
    public $codeSuccess = 0;

    /**
     * @var int 返回失败的code
     */
    public $codeFail = 1;

    /**
     * @var string 返回成功的message
     */
    public $msgSuccess = 'success';

    /**
     * @var string 返回失败的message
     */
    public $msgFail = 'fail';
}
