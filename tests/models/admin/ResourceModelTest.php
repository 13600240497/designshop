<?php
namespace app\tests\modules\admin\components;

use app\models\admin\ResourceModel;
use app\tests\TestCase;

class ResourceModelTest extends TestCase
{
    public function testGetCrumbs()
    {
        $resource = ResourceModel::findOne(24);
        var_dump($resource->getCrumbs());
    }
}
