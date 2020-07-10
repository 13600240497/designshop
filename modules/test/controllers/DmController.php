<?php

namespace app\modules\test\controllers;

/**
 * 配置文件控制器
 */
class DmController extends \ego\web\Controller
{
    public function actionIndex()
    {
        $sql = app()->request->post('sql');
        $is_explain = app()->request->post('is_explain');
        $is_explain = !empty($is_explain);
        $sqllist = explode(';', $sql);
        foreach ($sqllist as $v)
        {
            $result[] = $this->query($v, $is_explain);
        }
//        $result = $this->query($sql, !empty($is_explain));
        print_r($result);
    }

    public function query($sql, $is_explain = true)
    {
        try {
            $sql = ($is_explain ? 'explain ' : '') . $sql;
            return \Yii::$app->db->createCommand($sql)
                ->queryAll();
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
    
    public function show($data)
    {
        foreach ($data as $item) {
            $data[] = new linger();
            foreach ($item as $row) {

            }
        }
    }
}
