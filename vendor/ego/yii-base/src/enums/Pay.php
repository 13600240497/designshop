<?php
namespace ego\enums;

/**
 * 支付常量枚举
 */
class Pay extends AbstractEnum
{
    /**
     * @var string
     */
    const PAYPAL = 'PP';
    /**
     * @var string
     */
    const PAYPAL_CREDIT_CARD = 'PT';
    /**
     * @var string
     */
    const PAYPAL_CREDIT = 'PD';
    /**
     * @var string
     */
    const GLOBALCOLLECT = 'GC';
    /**
     * @var string
     */
    const WORLDPAY = 'WP';
    /**
     * @var string
     */
    const IDEAL = 'ID';
    /**
     * @var string
     */
    const CHECKOUT_CREDIT= 'CC';
    /**
     * @var string
     */
    const BOLETO = 'BE';
    /**
     * @var string
     */
    const QIWI = 'QW';
    /**
     * @var string 线下支付
     */
    const WESTERN_UNION = 'WU';
    /**
     * @var string 线下支付
     */
    const WIRE_TRANSFER = 'WT';
    /**
     * @var string Yandex money
     */
    const YANDEX_MONEY = 'YM';
    /**
     * @var string Ebanx-installment
     */
    const EBANX_INSTALLMENT = 'EI';
    /**
     * @var string Webmoney
     */
    const WEBMONEY = 'WM';

    /**
     * @var array 支付方式常量对应关系
     */
    public static $payments = [
        self::PAYPAL => 'Paypal',
        self::PAYPAL_CREDIT => 'Paypal Credit',
        self::PAYPAL_CREDIT_CARD => 'Paypal Credit Card',
        self::GLOBALCOLLECT => 'Global Collect',
        self::CHECKOUT_CREDIT => 'Checkout Credit',
        self::BOLETO => 'Boleto',
        self::QIWI => 'Qiwi',
        self::WESTERN_UNION => 'Western Union',
        self::WIRE_TRANSFER => 'Wire Transfer',
        self::WORLDPAY => 'World Pay',
        self::IDEAL => 'Ideal',
        self::YANDEX_MONEY => 'Yandex money',
        self::EBANX_INSTALLMENT => 'Ebanx-installment',
        self::WEBMONEY => 'Webmoney',
    ];

    /**
     * 获取支付方式的名称
     *
     * @param int $id
     * @return string
     */
    public static function getName($id)
    {
        return static::$payments[$id] ?? 'unknown';
    }

    /**
     * 判断当前支付是否支持自动退款
     *
     * @param int $id
     * @return boolean
     */
    public static function isAutoRefund($id)
    {
        return !in_array($id, [self::WESTERN_UNION, self::WIRE_TRANSFER]);
    }
}
