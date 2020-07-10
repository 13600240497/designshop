<?php


namespace app\modules\activity\zf\controllers;


/**
 * 页面模板管理类
 *
 * @property \app\modules\activity\zf\components\NativePageTplComponent $NativePageTplComponent
 *
 */
class NativePageTplController extends Controller
{
	/**
	 * 模板列表首页
	 * @return string
	 * @throws \yii\base\InvalidParamException
	 */
	public function actionIndex()
	{
		return $this->render('index');
	}

	/**
	 * 模板列表数据
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionList()
	{
		$params = app()->request->get();
		$rules = [
			[['name', 'lang', 'site_code', 'create_name'], 'string'],
			[['type', 'pageNo', 'pageSize'], 'integer']
		];
		$model = app()->validatorModel->new($rules)->load($params);
		if (false === $model->validate()) {
			return app()->helper->arrayResult(1, implode('|', array_column($model->errors, 0)));
		}
		$params['type'] = isset($params['type']) ? $params['type'] : 0;

		$result = $this->NativePageTplComponent->getList($params);
		return app()->helper->arrayResult(0, 'success', $result);
	}

	/**
	 * 新增模板
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionAdd()
	{
		$request = app()->request;
		$params = array(
			$request->post('name'),
			$request->post('pageId'),
			$request->post('lang'),
			$request->post('site_code'),
			$request->post('pic'),
			$request->post('type', 1),
		);
		$res = $this->NativePageTplComponent->add($params);
		if (!$res) {
			return app()->helper->arrayResult(100, $this->NativePageTplComponent->errors, $this->NativePageTplComponent->errors);
		}
		return app()->helper->arrayResult(0, 'success', $res);
	}

	/**
	 * 编辑模板
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionEdit()
	{
		$request = app()->request;
		$id = $request->post('id');
		$name = $request->post('name');
		$pic = $request->post('pic');
		$type = $request->post('type', 1);
		$res = $this->NativePageTplComponent->edit($id, $name, $pic, $type);
		if ($res) {
			return app()->helper->arrayResult(0, 'success', $res);
		} else {
			return app()->helper->arrayResult(100, $this->NativePageTplComponent->errors, $this->NativePageTplComponent->errors);
		}
	}

	/**
	 * 删除页面模板(暂不支持多条删除)
	 *
	 * @param int $id
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionDelete($id)
	{
		if (true === $this->NativePageTplComponent->delete($id)) {
			return app()->helper->arrayResult(0, 'success', []);
		}

		return app()->helper->arrayResult(100, $this->PageTplComponent->errors, $this->PageTplComponent->errors);
	}

	/**
	 * 确认切换原生页模板
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionConfirmTpl()
	{
		$request = app()->request;
		$param = array(
			$request->post('page_id'),
			$request->post('tpl_id'),
			$request->post('lang')
		);

		return $this->NativePageTplComponent->confirmTpl($param);
	}

	/**
	 * 模板预览图上传
	 */
	public function actionUploadPic()
	{
		return $this->NativePageTplComponent->picUpload(app()->request->post('pic'));
	}

	/**
	 * 查看预览页面模板
	 * @property \app\modules\activity\zf\components\PageDesignComponent $PageDesignComponent
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionPreview()
	{
		$request = app()->request;
		$param = array(
			$request->get('id'),           //页面模板id【page_template.id】
			$request->get('pid'),          //页面32位长度的pid
			$request->get('site_code'),   //站点简称
			$request->get('lang'),          //语种
			$request->get('pipeline'),      //渠道
		);

		return $this->NativePageTplComponent->look($param);
	}
}
