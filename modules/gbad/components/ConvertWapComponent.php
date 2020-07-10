<?php

namespace app\modules\gbad\components;

use app\base\SiteConstants;
use app\modules\common\components\CommonConvertWapComponent;

/**
 * 一键PC转Wap
 */
class ConvertWapComponent extends CommonConvertWapComponent
{

    /**
     * @inheritdoc
     */
    public function getCreatorWapLists($activityType=SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT)
    {
        return parent::getCreatorWapLists($activityType);
    }
}
