<?php
namespace app\modules\test\zf\controllers;

/**
 * 版本升级，旧数据处理组件
 *
 * @property \app\modules\test\zf\components\RepairComponent $RepairComponent
 * @link /test/zf/repair/activity-group-support-list
 */
class RepairController extends Controller
{

    /**
     * 修复首页，将活动页面类型设置为B首页类型
     *
     * @link /test/zf/repair/reset-home
     * @return array
     */
    public function actionResetHome()
    {
        return $this->RepairComponent->resetHomeB();
    }

    /**
     * ZF国家站二期活动分组数据support_list修复
     *
     * @link /test/zf/repair/activity-group-support-list
     * @return array
     * @throws \Throwable
     */
    public function actionActivityGroupSupportList()
    {
        return $this->RepairComponent->repairActivityGroupSupportList();
    }

    /**
     * ZF国家站二期活动组件内容URL前缀替换
     *
     * @link /test/zf/repair/batch-repair-page-url-prefix?ids=11,22&src_pipeline=ZF&target_pipelines=ZFIT
     * @return array
     * @throws \Throwable
     */
    public function actionBatchRepairPageUrlPrefix()
    {
        return $this->RepairComponent->batchRepairPageUrlPrefix(app()->request->get());
    }

    /**
     * 把ZF活动首页发布权限更新到专题页
     *
     * @link /test/zf/repair/special-permissions
     * @return array
     */
    public function actionSpecialPermissions()
    {
        return $this->RepairComponent->updateSpecialPermissions();
    }
}
