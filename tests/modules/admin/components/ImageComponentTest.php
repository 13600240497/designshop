<?php
namespace app\tests\modules\admin\components;

use app\modules\admin\components\ImageComponent;
use app\tests\TestCase;

class ImageComponentTest extends TestCase
{
    public function testCrop()
    {
        $image = new ImageComponent();
        $image->crop('test.jpg', 1000, 200, [20, 20]);
    }
}
