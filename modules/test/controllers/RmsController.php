<?php
namespace app\modules\test\controllers;


class RmsController extends Controller
{
    
    public function actionPhpError()
    {
        WTF();
    }
    
    public function actionMysqlError()
    {
        return app()->db->createCommand('SELECT * FROM post WHERE id=1')
            ->queryOne();
    }
}