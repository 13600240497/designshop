<?php

namespace app\modules\gbad\components;

use app\base\SiteConstants;
use app\modules\common\components\CommonConvertAppComponent;

/**
 * 一键转APP
 */
class ConvertAppComponent extends CommonConvertAppComponent
{

    /**
     * @inheritdoc
     */
    public function getCreatorAppLists($activityType=SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT)
    {
        return parent::getCreatorAppLists($activityType);
    }
}
