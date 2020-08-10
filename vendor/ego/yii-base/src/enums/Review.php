<?php
namespace ego\enums;

/**
 * 评论接口type常量枚举
 */
class Review extends AbstractEnum
{
    /**
     * 写评论
     * 
     * @var int
     */
    const REVIEW_ADD = 1;
    /**
     * 评论点赞
     * 
     * @var int
     */
    const REVIEW_LIKES = 2;
    /**
     * 能否写评论
     * 
     * @var int
     */
    const REVIEW_CAN_WRITE = 3;
    /**
     * 商品分类更新
     * 
     * @var int
     */
    const REVIEW_GOODSCAT_UPDATE= 4;
    /**
     * 获取评论
     * 
     * @var int
     */
    const REVIEW_GET = 9;
    /**
     * 写评论（app）
     * 
     * @var int
     */
    const REVIEW_APP_WRITE = 9;
    /**
     * 获取评论（app）
     * 
     * @var int
     */
    const REVIEW_APP_GET = 9;
    /**
     * 回复评论
     * 
     * @var int
     */
    const REVIEW_REPLY = 10;
    
}
