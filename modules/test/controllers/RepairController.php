<?php

namespace app\modules\test\controllers;


/**
 * 数据修复控制器
 *
 * @property \app\modules\test\components\RepairToolsComponent $RepairToolsComponent
 */
class RepairController extends Controller
{

    /**
     * 修复组件模板数据
     *
     * @return array
     */
    public function actionRepairUiTpl()
    {
        return $this->RepairToolsComponent->repairUiTplData(app()->request->get());
    }

}