<?php

namespace app\modules\soa\traits;

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
    
    /**
     * @var int 商品sku校验的code
     */
    public $skuCodeFail = 1100001;
    
    /**
     * @var int 商品sku售罄的code
     */
    public $sellOutCodeFail = 1100002;
    
    /**
     * @var int APP专享价校验的code
     */
    public $appPriceTypeCodeFail = 1100003;
    
    /**
     * @var int coupon所属商品sku校验的code
     */
    public $couponGoodsSkuFail = 1100004;
    
    /**
     * @var int coupon不存在或类型错误
     */
    public $couponExsitsCodeFail = 1100005;
}
