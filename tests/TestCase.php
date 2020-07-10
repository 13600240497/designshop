<?php
namespace app\tests;

use ego\phpunit\AbstractTestCase;
use app\base\Application;

/**
 * 测试基类
 */
class TestCase extends AbstractTestCase
{
    /**
     * @inheritdoc
     */
    protected function createApp()
    {
        new Application($GLOBALS['CONFIG']);
    }
}
