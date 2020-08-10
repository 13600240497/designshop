<?php
namespace ego\enums;

/**
 * 订单常量枚举
 */
class Order extends AbstractEnum
{
    /**
     * @var int 待付款
     */
    const AWAITING_PAYMENT = 0;
    /**
     * @var int 部分支付
     */
    const PARTIAL_PAYMENT = 1;
    /**
     * @var int 已支付
     */
    const PAID = 2;
    /**
     * @var int 待发货
     */
    const TO_BE_SHIPPED = 3;
    /**
     * @var int 已发货
     */
    const SHIPPED = 4;
    /**
     * @var int 已签收
     */
    const RECEIVED = 5;
    /**
     * @var int 退款中
     */
    const REFUND = 6;
    /**
     * @var int 退款成功 （已支付取消成功且退款成功）
     */
    const REFUND_SUCCESS = 7;
    /**
     * @var int -已取消（未支付取消无退款）
     */
    const CANCELED = 8;
    /**
     * @var int 已删除 （暂无）
     */
    const REMOVED = 9;
    /**
     * @var int 支付中
     */
    const PENDING = 10;
    /**
    * @var int 部分发货
    */
    const PARTIAL_SHIPMENT = 11;
    /**
     * @var int 待付款 （联合订单）
     */
    const UNION_AWAITING_PAYMENT = 1;
    /**
     * @var int 待发货 （联合订单）
     */
    const UNION_TO_BE_SHIPPED = 2;
    /**
     * @var int 待签收 （联合订单）
     */
    const UNION_AWAITNG_RECEIVED = 3;
    /**
     * @var int 已签收 （联合订单）
     */
    const UNION_RECEIVED =4;
    /**
     * @var int 已取消 （联合订单未支付取消）
     */
    const UNION_CANCELED = 5;
    /**
     * @var int 已取消 （联合订单已支付退款中）
     */
    const UNION_CANCELED_REFUNDING = 6;
    /**
     * @var int 已取消 （联合订单已支付退款成功）
     */
    const UNION_CANCELED_REFUND_SUCCESS = 7;
    /**
     * @var int 正在支付中（新增）
     */
    const UNION_PENDING = 8;
    /**
     * @var int 未配货 （订单配货）
     */
    const ORDER_DISTRIBUTE_STATUS_NO= 0;
    /**
     * @var int 部分配货 （订单配货）
     */
    const ORDER_DISTRIBUTE_STATUS_PARTIAL = 1;
    /**
     * @var int 完全配货 （订单配货）
     */
    const ORDER_DISTRIBUTE_STATUS_OK = 2;

    /**
     * @var array 子订单常量对应关系
     */
    public static $uuqids = [
        self::AWAITING_PAYMENT => 'Awaiting Payment',
        self::PARTIAL_PAYMENT => 'Partial Payment',
        self::PAID => 'Paid',
        self::TO_BE_SHIPPED => 'To Be Shipped',
        self::SHIPPED => 'Shipped',
        self::RECEIVED => 'Received',
        self::REFUND => 'Refund',
        self::REFUND_SUCCESS => 'Refund Success',
        self::CANCELED => 'Canceled',
        self::REMOVED => 'Removed',
        self::PENDING => 'Pending',
        self::PARTIAL_SHIPMENT => 'Partial Shipment',
    ];

    /**
     * @var array 联合订单常量对应关系
     */
    public static $unionuuqids = [
        self::UNION_AWAITING_PAYMENT => 'Awaiting Payment',
        self::UNION_TO_BE_SHIPPED => 'To Be Shipped',
        self::UNION_AWAITNG_RECEIVED => 'Shipped',
        self::UNION_RECEIVED => 'Received',
        self::UNION_CANCELED => 'Canceled',
        self::UNION_CANCELED_REFUNDING => 'Refund',
        self::UNION_CANCELED_REFUND_SUCCESS => 'Refund Success',
        self::UNION_PENDING => 'Pending',
    ];

    /**
     * 获取子订单状态的名称
     *
     * @param int $id
     * @return string
     */
    public static function getName($id)
    {
        return static::$uuqids[$id] ?? 'unknown';
    }

    /**
     * 获取联合订单状态的名称
     *
     * @param int $id
     * @return string
     */
    public static function getUnionStatusName($id)
    {
        return static::$unionuuqids[$id] ?? 'unknown';
    }

    /**
     * 获取联合订单的状态
     *
     * @param array $orderInfo 子订单数据列表
     * @return false|int
     */
    public static function getParentOrderStatus(array $orderInfo)
    {
        if (!empty($orderInfo)) {
            if (!empty($status = array_column($orderInfo, 'orderStatus'))) {

                if (in_array(self::PENDING, $status)) {
                    return  self::UNION_PENDING;
                }
                //只要子订单中包含未支付或者待支付的订单 -> 待付款
                if (in_array(self::AWAITING_PAYMENT, $status)
                    || in_array(self::PARTIAL_PAYMENT, $status)
                ) {
                    return  self::UNION_AWAITING_PAYMENT;
                }
                //只要子订单中包含退款及以后状态 -> 已取消
                if (in_array(self::REFUND, $status)
                    ||in_array(self::REFUND_SUCCESS, $status)
                    ||in_array(self::CANCELED, $status)
                    ||in_array(self::REMOVED, $status)
                    ) {
                    //区分已取消的具体状态
                    if (in_array(self::REFUND, $status)) {
                        return  self::UNION_CANCELED_REFUNDING;
                    }
                    if (in_array(self::REFUND_SUCCESS, $status)) {
                        return self::UNION_CANCELED_REFUND_SUCCESS;
                    }
                    return  self::UNION_CANCELED;
                }
                if (in_array(self::RECEIVED, $status)
                    && !in_array(self::SHIPPED, $status)
                    && !in_array(self::PAID, $status)
                    && !in_array(self::TO_BE_SHIPPED, $status)
                ) {
                    return  self::UNION_RECEIVED;
                } elseif (!in_array(self::SHIPPED, $status)
                    && !in_array(self::RECEIVED, $status)
                    && (in_array(self::TO_BE_SHIPPED, $status) || in_array(self::PAID, $status))
                ) {
                    return self::UNION_TO_BE_SHIPPED;;
                } else {
                    return self::UNION_AWAITNG_RECEIVED;
                }
            }
        }
        return false;
    }

    /**
     * 判断是否为正常流程的订单（未取消）
     *
     * @param int $id
     * @return boolean
     */
    public static function isNormalProcessOrder($id)
    {
        return in_array(
            $id,
            [
                self::UNION_AWAITING_PAYMENT,
                self::UNION_AWAITNG_RECEIVED,
                self::UNION_TO_BE_SHIPPED,
                self::UNION_RECEIVED
            ]
        );
    }

    /**
     * 截取自定义位有效数字
     *
     * @param float $f
     * @param int $len
     * @return float
     */
    public static function getFloatValue($f, $len)
    {
        if (!is_numeric($f) || !is_numeric($len)) {
            return 0;
        }
        $flag = true;
        //负数
        if ($f < 0) {
            $flag = false;
            $f = abs($f);
        }

        $tmpInt = intval($f);
        if ($len == 0) {
            return $tmpInt;
        }
        $str = bcadd($f , -$tmpInt,10);
        $subStr = strstr($str, '.');
        if (strlen($subStr) < $len + 1) {
            $repeatCount = $len + 1 - strlen($subStr);
            $str = $str . "" . str_repeat("0", $repeatCount);
        }
        $val = $tmpInt . "" . substr($str, 1, 1 + $len);
        return $flag ? $val : ('-' . $val);
    }
}
