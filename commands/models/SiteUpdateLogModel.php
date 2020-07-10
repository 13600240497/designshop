<?php

namespace app\commands\models;


    class SiteUpdateLogModel extends BaseModel
{
    /**
     * 状态|0-未开始
     */
    const STATUS_NOT_START = 0;
    
    /**
     * 状态|1-进行中
     */
    const STATUS_PROCESSING = 1;
    
    /**
     * 状态|2-已完成
     */
    const STATUS_COMPLETED = 2;
    
    /**
     * 页面刷新成功
     */
    const PAGE_SUCCESS = 1;
    
    /**
     * 页面刷新失败
     */
    const PAGE_FAILED = 2;
    
}