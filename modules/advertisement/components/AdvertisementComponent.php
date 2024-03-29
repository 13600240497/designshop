<?php

namespace app\modules\advertisement\components;

use app\modules\common\components\CommonAdvertisementComponent;
use app\modules\common\models\ActivityModel;
use ego\base\JsonResponseException;

/**
 * 自定义活动组件
 */
class AdvertisementComponent extends CommonAdvertisementComponent
{
    /**
     * 活动权限加/解锁
     *
     * @param $id
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lock($id)
    {
        $model = ActivityModel::getById($id);
        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($model)) {
            throw new JsonResponseException($this->codeFail, '只有活动创建者才具有此权限');
        }
    
        if (!$model) {
            throw new JsonResponseException($this->codeFail, '自定义活动不存在');
        }
        
        return $this->doLock($model);
    }
}
