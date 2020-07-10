<?php


namespace app\modules\activity\zf\components;


use app\base\PipelineUtils;
use app\base\S3Upload;
use app\base\SitePlatform;
use app\base\Upload;
use app\components\site\zf\PagePreview;
use app\modules\base\models\AdminModel;
use app\modules\common\models\NativePageLayoutModel;
use app\modules\common\models\NativePageTplModel;
use app\modules\common\models\NativePageUiComponentModel;
use app\modules\common\zf\components\NativePageUiComponentDataComponent;
use app\modules\common\zf\models\PageModel;
use yii\web\Response;

class NativePageTplComponent extends NativePageUiComponentDataComponent
{

	//模板模型
	private $tplModel;

	public $errors;

	/*
    *页面模板图片目录
    */
	private $Path = 'uploads/nativePage/tpl';

	/**
	 * 原生页面模板列表
	 *
	 * @param $params
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public function getList ($params)
	{
		$this->tplModel = new NativePageTplModel();
		$return = $this->tplModel->getTplList($params);
		$langList = (array)app()->params['lang'];
		$siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
		$configAllPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($siteGroupCode);
		if (!empty($return['list'])) {
			$users = array_unique(array_column($return['list'], 'create_user'));
			$userList = AdminModel::getByUserNames($users);
			$username = array_column($userList, 'realname', 'username');
			foreach ($return['list'] as &$v) {
				$v['lang'] = [
					'key'   => $v['lang'],
					'value' => isset($langList[$v['lang']]) ? $langList[$v['lang']]['name'] : ''
				];
				$v['real_name'] = isset($username[$v->create_user]) ? $username[$v->create_user] : $v->create_user;
				$v['opt'] = (int)$this->checkAuthority($v->create_user);
				if (!empty($v['pipeline'])) {
					$v['pipeline_name'] = $configAllPipelineList[$v['pipeline']] ?? '';
				}
			}
			unset($users, $userList, $username);
		}

		return $return;
	}

	/**
	 * 新增原生页面模板
	 *
	 * @param $params
	 *
	 * @return array|bool
	 */
	public function add ($params)
	{
		list($name, $pageId, $lang, $siteCode, $pic, $type) = $params;
		if (!isset($name, $pageId, $lang, $siteCode) || !in_array($type, [1, 2])) {
			$this->errors = '参数错误';

			return false;
		}

		/** @var  \app\modules\common\zf\models\PageModel $pageModel */
		$pageModel = PageModel::findOne($pageId);
		$this->tplModel = new NativePageTplModel();
		if ($this->checkName($name)) {
			return false;
		}

		list($layoutInfo, $componentData) = $this->getNativePageInfo($pageId, $lang);//获取页面数据
		$this->tplModel->name = $name;
		$this->tplModel->lang = $lang;
		$this->tplModel->site_code = $siteCode;
		$this->tplModel->layout_data = json_encode($layoutInfo);
		$this->tplModel->ui_data = json_encode($componentData);
		$this->tplModel->create_user = app()->user->username;
		$this->tplModel->create_time = $this->tplModel->update_time = time();
		$this->tplModel->pid = $pageModel->pid;
		$this->tplModel->tpl_type = (int)$type;
		$this->tplModel->pic = $pic;
		$this->tplModel->pipeline = $pageModel->pipeline;
		if (!$this->tplModel->save()) {
			$this->errors = '新增失败：' . $this->tplModel->flattenErrors(', ');

			return false;
		}
		if ($this->tplModel->id > 0) {
			return ['id' => $this->tplModel->id];
		}
		$this->errors = '新增失败';

		return false;
	}

	/**
	 * 编辑原生页面模板
	 *
	 * @param $id
	 * @param $name
	 * @param $pic
	 * @param $type
	 *
	 * @return bool
	 * @throws \Throwable
	 */
	public function edit ($id, $name, $pic, $type)
	{
		$this->tplModel = NativePageTplModel::getById($id);
		if (!$this->tplModel) {
			$this->errors = '模板不存在';

			return false;
		}

		if (!$this->checkAuthority($this->tplModel->create_user)) {
			$this->errors = '没有操作权限';

			return false;
		}

		if ($this->checkName($name, $id)) {
			return false;
		}

		$this->tplModel->name = $name;
		$this->tplModel->pic = $pic;
		$this->tplModel->tpl_type = (int)$type;
		$this->tplModel->update_user = app()->user->username;
		$this->tplModel->update_time = time();
		if ($this->tplModel->save(false)) {
			return true;
		}
		$this->errors = '更新失败';

		return false;
	}

	/**
	 * 删除原生页面模板
	 *
	 * @param $id
	 *
	 * @return bool
	 * @throws \Throwable
	 */
	public function delete ($id)
	{
		if (empty($id) || !is_numeric($id)) {
			$this->errors = '参数有误';

			return false;
		}

		$model = NativePageTplModel::getById($id);
		if (!($model)) {
			$this->errors = '模板不存在';

			return false;
		}

		if (!$this->checkAuthority($model->create_user)) {
			$this->errors = '没有操作权限';

			return false;
		}

		$model->is_delete = 1;
		$model->name = 'delete_' . $model->name . '_' . $id;
		if ($model->save()) {
			return true;
		}

		$this->errors = '删除失败';

		return false;
	}
	
	/**
	 * 获取页面组件数据
	 *
	 * @param int    $pageId
	 * @param string $lang
	 *
	 * @return array
	 */
	public function getNativePageInfo (int $pageId, string $lang)
	{
		$componentIds = NativePageLayoutModel::getComponentsSort($pageId, $lang);
		$componentData = NativePageUiComponentModel::getComponentsData($pageId, $lang, $componentIds);

		return [$componentIds, $componentData];
	}

	/**
	 * 检查名称
	 * 1、名称不能为空
	 * 2、字符长度不大于 50字符
	 * 3、不能重复
	 *
	 * @param string $name 模板名称
	 * @param int    $id   模板id
	 *
	 * @return bool
	 */
	private function checkName ($name, $id = 0)
	{
		if (empty($name)) {
			$this->errors = '名称不能为空';

			return true;
		} elseif (mb_strlen($name, 'utf-8') > 50) {
			$this->errors = '名称不能超过50个字符';

			return true;
		}

		$info = $this->tplModel->getInfoByName($name);
		if ($info && (empty($id) || $info['id'] != $id)) {
			$this->errors = '该模板名称已被使用，请更新名称';

			return true;
		}

		return false;
	}

	/**
	 * 校验用户是否有操作权限
	 *
	 * @param string $username 用户名
	 *
	 * @return boolean  true-有操作权限； false-无操作权限
	 * @throws \Exception
	 * @throws \Throwable
	 */
	private function checkAuthority ($username)
	{
		if (empty($username)) {
			return false;
		}

		$isSuper = (int)app()->user->get('is_super');  //超级管理员：1是，0否
		if ($isSuper === 1 || ($isSuper === 0 && $username === app()->user->username)) {
			return true;
		}

		return false;
	}

	/**
	 * 模板预览图上传
	 */
	public function picUpload(string $pic)
	{
		$uploadPath = \yii::getAlias('@app/runtime');
		$uploadPath .= DIRECTORY_SEPARATOR . $this->Path;
		if (!is_dir($uploadPath)) {
			mkdir($uploadPath, 0777, true);
		}
		$fileName = md5(time() . mt_rand(1000, 9999));
		$filePath = "{$uploadPath}/{$fileName}.jpg";
		$content = base64_decode(explode(',', $pic)[1]);
		file_put_contents($filePath, $content);
		$s3Upload = new S3Upload();
		$url = $s3Upload->saveToS3File($filePath);
		if (!$url) {
			app()->helper->arrayResult($this->codeFail, $this->msgFail, '上传S3失败');
		}

		return app()->helper->arrayResult(0, 'success', ['url' => $url]);
	}

	/**
	 * 页面模板-查看页面
	 *
	 * @param array $params  参数数组
	 *                       ['id']         int    页面模板id
	 *                       ['pid']        string 页面32位长度pid
	 *                       ['site_code']  string 站点简称
	 *                       ['lang']       string 语种
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function look ($params)
	{
		list($id, $pid, $siteCode, $lang, $pipeline) = $params;
		if (!isset($id, $pid, $siteCode, $lang)) {
			return app()->helper->arrayResult($this->codeFail, '参数错误');
		}

		//获取页面模板html数据
		$pagePreview = new PagePreview();
		$html = $pagePreview->getNativeTemplatePreview($id, $pid, $lang, $pipeline, $siteCode);
		if (\is_array($html)) {
			return $html;
		}

		app()->response->format = Response::FORMAT_HTML;

		return $html;
	}
}
