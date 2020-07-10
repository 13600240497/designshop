<?php
namespace app\tests\modules\admin\components;

use app\models\admin\ResourceModel;
use app\modules\admin\components\ResourceComponent;
use app\tests\TestCase;

class ResourceComponentTest extends TestCase
{
    public function testMoveResource()
    {
        $resource = new ResourceComponent();
        $resource->moveResource([4, 5], 11);
        $actual = (ResourceModel::getById(4))->parent_id;
        $this->assertEquals(11, $actual);

        $resource->moveResource([4, 5], 12);
        $actual = (ResourceModel::getById(4))->parent_id;
        $this->assertEquals(12, $actual);
    }
}
