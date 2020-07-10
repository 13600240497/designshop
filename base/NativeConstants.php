<?php
namespace app\base;

/**
 * 原生活动常量
 *
 * @package app\base
 */
interface NativeConstants
{
    /** @var int SKU来源 - 手动输入 */
    const SKU_FROM_INPUT = 1;

    /** @var int SKU来源 - 商品运营平台 */
    const SKU_FROM_SOP = 2;
}