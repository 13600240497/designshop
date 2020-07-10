<?php
namespace app\modules\test\zf\controllers;

/**
 * 工具组件
 *
 * @property \app\modules\test\zf\components\ToolsComponent $ToolsComponent
 */
class ToolsController extends Controller
{

    /**
     * 博客代理
     *
     * @link /test/zf/tools/blog-proxy
     * @return array
     * @throws \Throwable
     */
    public function actionBlogProxy()
    {
        return $this->ToolsComponent->blogProxy(app()->request->get());
    }

}
