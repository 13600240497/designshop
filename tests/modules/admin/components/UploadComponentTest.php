<?php
namespace app\tests\modules\admin\components;

use app\modules\admin\components\UploadComponent;
use app\tests\TestCase;

class UploadComponentTest extends TestCase
{

    public function testCheckAndGetImageSize()
    {
        $upload = new UploadComponent();
        $this->expectException(\ErrorException::class);
        $this->invokeMethod($upload, 'checkAndGetImageSize', ['xxx']);
    }
}
