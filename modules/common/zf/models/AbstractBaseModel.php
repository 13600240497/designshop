<?php
namespace app\modules\common\zf\models;

use app\models\ActiveRecord;
use ego\base\JsonResponseException;

/**
 * common 模块
 */
class AbstractBaseModel extends ActiveRecord
{

    public static function tableName()
    {
        $websiteCode = (defined('SITE_GROUP_CODE_FIXED') && !empty(SITE_GROUP_CODE_FIXED))
            ? SITE_GROUP_CODE_FIXED
            : SITE_GROUP_CODE;
        if(empty($websiteCode)){
            throw new JsonResponseException(1, '登录超时请重新登录');
        }
        return $websiteCode . '_' . parent::tableName();
    }
}
