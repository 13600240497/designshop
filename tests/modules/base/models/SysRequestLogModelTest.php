<?php
namespace app\tests\modules\admin\components;

use app\modules\base\models\SysRequestLogModel;
use app\tests\TestCase;

class SysRequestLogModelTest extends TestCase
{

    public function testgetRequestLogByid()
    {
        $log = new SysRequestLogModel();
        $result = $log->getRequestLogByid(36074);
//        var_dump($result);
        foreach ($result as $item) {
            echo $item->getDetailMessage() . "\n";
        }

//        $str = '/activity/zf/design/index?group_id=73a5599ea18d862b26358b18ab87ec6d&pipeline=ZF&lang=en';
//        $res = $log->gettest($str);
//        var_dump($res);
    }


}
