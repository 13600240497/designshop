<?php
namespace app\tests\base;

use app\tests\TestCase;

class UserTest extends TestCase
{
    public function testShowUser()
    {
        $arr = app()->user->showUser();
        var_dump($arr);
    }
}
