<?php
/**
 * 资源操作控制器
 */
namespace app\modules\admin\controllers;

use yii\web\Response;

class ResourceController extends Controller
{
    public function actionIndex()
    {
        app()->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }

    /**
     * 只有自己创建的列表
     *
     * @return array
     */
    public function actionList()
    {
        return $this->ResourceComponent->lists(app()->request->get('id', 0));
    }

    /**
     * 只有自己创建的列表搜索
     *
     * @return array
     */
    public function actionSearch()
    {
        return $this->ResourceComponent->search(app()->request->get('search', false));
    }

    /**
     * 全部的素材列表
     *
     * @return array
     */
    public function actionListAll()
    {
        return $this->ResourceComponent->listAll(app()->request->get('id', 0));
    }

    /**
     * 全部目录列表
     * @return array
     */
    public function actionFolderTree()
    {
        return $this->ResourceComponent->folderTree(true);
    }

    /**
     * 搜索全部的素材列表
     *
     * @return array
     */
    public function actionSearchAll()
    {
        return $this->ResourceComponent->searchAll(app()->request->get('search', false));
    }

    /**
     * 获取这个ID的兄弟list
     *
     * @return array
     */
    public function actionListBrother()
    {
        return $this->ResourceComponent->listsBrother(app()->request->get('id', 0));
    }

    /**
     * 新增
     *
     * @return array
     */
    public function actionAdd()
    {
        return $this->ResourceComponent->add(app()->request->post());
    }

    /**
     * 编辑
     *
     * @return array
     */
    public function actionEdit()
    {
        $result = $this->ResourceComponent->edit(
            app()->request->post('id'),
            app()->request->post()
        );
        isset($result['message']) && $result['message'] = '修改成功';
        return $result;
    }

    /**
     * 获取信息
     *
     * @return array
     */
    public function actionInfo()
    {
        return $this->ResourceComponent->info(app()->request->get('id'));
    }

    /**
     * 删除
     *
     * @return array
     */
    public function actionDelete()
    {
        return $this->ResourceComponent->delete(app()->request->post('id'));
    }

    /**
     * 批量删除
     *
     * @return array
     */
    public function actionDeleteIds()
    {
        return $this->ResourceComponent->deleteFromIds(explode(',', app()->request->post('ids')));
    }

    /**
     * 上传文件
     *
     * @return array
     */
    public function actionUpload()
    {
        return $this->UploadComponent->upload();
    }

    /**
     * 多上传文件
     *
     * @return array
     */
    public function actionMultiFileUpload()
    {
        return $this->UploadComponent->multiFileUpload();
    }

    /**
     * 面包屑功能
     *
     * @return array
     */
    public function actionCrumbs()
    {
        return $this->ResourceComponent->crumbs(app()->request->get('id', 0));
    }

    /**
     * 移动资源接口
     *
     * @return array
     */
    public function actionMoveResource()
    {
        $resourceIds = explode(',', app()->request->post('ids', 0));
        return $this->ResourceComponent->moveResource($resourceIds, app()->request->post('parent_id', 0));
    }

}
