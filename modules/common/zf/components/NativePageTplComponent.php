<?php

namespace app\modules\common\zf\components;

use app\modules\common\zf\models\{
	PageModel, PageTplModel
};
use app\modules\common\models\{NativePageLayoutModel,
	NativePageLayoutDataModel,
	NativePageTplModel,
	NativePageUiComponentModel};
use app\modules\base\models\AdminModel;
use app\modules\common\zf\traits\CommonPublishTrait;
use app\base\Upload;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use yii\helpers\ArrayHelper;

/**
 * 页面模板管理组件
 *
 * @property \app\modules\common\components\CommonPageUiComponentDataComponent $commonPageUiComponentDataComponent
 */
class NativePageTplComponent extends Component
{
	use CommonPublishTrait;

	public $errors;

	//模板模型
	private $tplModel;

	//默认模板状态
	private $defaultStatus = 1;

	//默认导航组件编码
	private $defaultNavComponent = ['U000027', 'U000029', 'U000030'];

	/*
	*页面模板图片目录
	*/
	private $Path = 'uploads/page/tpl';

	/**
	 * 新增模板
	 *
	 * @param array $params 传参数组
	 *                      ['name']       string      模板名称
	 *                      ['pageId']     int         页面ID
	 *                      ['lang']       string      模板语言
	 *                      ['pic']        string      模板预览图
	 *                      ['pid']        string      32位长度的pid，作为查看预览
	 *                      ['type']       int         模板类型 1-公有类型， 2-私有类型
	 *                      ['range']      int         适用范围 1-PC端,2-WAP端,3-响应式
	 *
	 * @return bool|array
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Throwable
	 * @throws \Exception
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
		$this->tplModel = new PageTplModel();
		if ($this->checkName($name)) {
			return false;
		}

		$siteCode = $pageModel->site_code;
		list($layoutInfo, $layoutData, $uiInfo, $uiData) = $this->getPageInfo($pageId, $lang);//获取页面数据
		$insertArr = [
			'name'        => $name,
			'lang'        => $lang,
			'site_code'   => $siteCode,
			'custom_css'  => $pageModel['custom_css'] ?? '',
			'layout'      => json_encode($layoutInfo),
			'layout_data' => json_encode($layoutData),
			'ui'          => json_encode($uiInfo),
			'ui_data'     => json_encode($uiData)
		];
		$this->tplModel->attributes = $insertArr;
		$this->tplModel->create_user = app()->user->username;
		$this->tplModel->create_time = $this->tplModel->update_time = time();
		$this->tplModel->lang = $lang;
		$this->tplModel->pid = $pageModel->pid;
		$this->tplModel->tpl_type = (int)$type;
		$this->tplModel->pic = $pic;
		$this->tplModel->pipeline = $pageModel->pipeline;
		$this->tplModel->platform_type = SitePlatform::getPlatformTypeBySiteCode($siteCode);
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
	 * 编辑页面模板
	 *
	 * @param int    $id   [模板ID]
	 * @param string $name [模板名称]
	 * @param string $pic  [模板预览图]
	 * @param int    $type [模板类型]
	 *
	 * @return  bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function edit ($id, $name, $pic, $type)
	{
		$this->tplModel = PageTplModel::findone($id);
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
	 * 删除页面模板
	 *
	 * @param int $id
	 *
	 * @return bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function delete ($id)
	{
		if (empty($id) || !is_numeric($id)) {
			$this->errors = '参数有误';

			return false;
		}

		$model = PageTplModel::findOne($id);
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
	 * 模板列表
	 *
	 * @param array $params  传参数组
	 *                       ['pageNo']     int        页码
	 *                       ['pageSize']   int        每页数量
	 *                       ['name']       string     模板名称
	 *                       ['lang']       string     模板语言
	 *                       ['siteCode']   string     站点简称
	 *                       ['type']       int        模板类型
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function getList ($params)
	{
		$params['range'] = 0;
		$params['platform_type'] = 0;
		if (!empty($params['site_code'])) {
			$params['platform_type'] = SitePlatform::getPlatformTypeBySiteCode($params['site_code']);
			$params['range'] = $this->getRangeBySiteCode($params['site_code']);
		}

		$this->tplModel = new PageTplModel();
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
				$v['platform_name'] = SitePlatform::getPlatformNameByType($v['platform_type']);
				if (!empty($v['pipeline'])) {
					$v['pipeline_name'] = $configAllPipelineList[$v['pipeline']] ?? '';
				}
				unset($v['platform_type']);
			}
			unset($users, $userList, $username);
		}

		return $return;
	}

	/**
	 * 修改默认模板
	 *
	 * @param int    $id       [模板id]
	 * @param string $siteCode [站点简称]
	 *
	 * @return bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function changeDefaultTpl ($id, $siteCode)
	{
		if (empty($id) || empty($siteCode)) {
			$this->errors = '参数错误';

			return false;
		}
		$this->tplModel = PageTplModel::findOne($id);
		if (empty($this->tplModel)) {
			$this->errors = '模板不存在';

			return false;
		}
		$oldModel = new PageTplModel();
		$oldTplModel = $oldModel->getDefaultInfo($siteCode, $this->defaultStatus);

		$transaction = $this->tplModel->getDb()->beginTransaction();
		try {
			if ($oldTplModel) {
				$oldTplModel->is_default = 0;
				$oldTplModel->save(false);
			}
			$this->tplModel->is_default = $this->defaultStatus;
			$this->tplModel->site_code = $siteCode;
			$this->tplModel->update_user = app()->user->username;
			if (!$this->tplModel->save(false)) {
				$this->errors = '设置失败';

				return false;
			}
			$transaction->commit();
		} catch (\Throwable $e) {
			$transaction->rollBack();
			$this->errors = $e->getMessage();

			return false;
		}

		return true;
	}

	/**
	 * 页面初始化模板数据
	 *
	 * @param array    $pageIds 页面ID
	 * @param int    $tplId  模板id
	 * @param string $lang   语言代码简称
	 *
	 * @return bool
	 */
	public function initPageTpl (array $pageIds, $tplId = 0, $lang)
	{
		$this->tplModel = new NativePageTplModel();
		$tplInfo = $this->tplModel->findOne($tplId);
		$tplInfo = ArrayHelper::toArray($tplInfo);
		foreach ($pageIds as $pageId) {
			$this->copyPage($pageId, $tplInfo, $lang);
		}

		return true;
	}

	/**
	 * 复制原生页模板数据
	 *
	 * @param        $pageId
	 * @param        $tplInfo
	 * @param string $copyLang
	 */
	public function copyPage ($pageId, $tplInfo, $copyLang = '')
	{
		$page = new PageModel();
		if (!$copyLang) {
			$pages = $page->getPageInfo($pageId);
		} else {
			$pages['pageLanguages'][]['lang'] = $copyLang;
		}

		foreach ($pages['pageLanguages'] as $lang) {
			NativePageLayoutModel::saveFormData([
				'page_id' => $pageId,
				'lang'    => $lang['lang'],
				'data'    => !empty($tplInfo['layout_data']) ? $tplInfo['layout_data'] : ''
			]);
			$uiData = json_decode($tplInfo['ui_data'], true);
			if (!empty($uiData)) {
				foreach ($uiData as &$item) {
					$item['id'] = $item['component_id'];
					$item['template_id'] = $item['tpl_id'];
					$item['template_name'] = $item['tpl_name'];
					$item['template_title'] = $item['tpl_title'];
					$item['style'] = !empty($item['style_data']) ? $item['style_data'] : '';
					$item['goodsSKU'] = !empty($item['sku_data']) ? $item['sku_data'] : '';
					$item['data'] = !empty($item['setting_data']) ? $item['setting_data'] : '';

					unset($item['component_id'], $item['tpl_id'], $item['tpl_name'], $item['tpl_title'], $item['style_data'], $item['sku_data'], $item['setting_data']);
				}
				NativePageUiComponentModel::saveFormData(['page_id' => $pageId, 'lang' => $lang['lang'],'data' => $uiData]);
			}
		}
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
	 * 获取页面组件关联信息
	 *
	 * @param int    $pageId 页面ID
	 * @param string $lang   页面语言
	 *
	 * @return array 布局与UI组件的位置和数据
	 */
	public function getPageInfo ($pageId, $lang)
	{
		$layoutInfo = $layoutData = $uiData = $uiInfo = [];
		$pageInfo = $this->getPageLayoutAndUiByPageId($pageId, $lang);//获取页面组件关系与组件数据(从上到下)
		foreach ($pageInfo[0] as $key => $val) {
			$layoutInfo[$key] = ['component_key' => $val['component_key'], 'id' => $val['id']];
			$layoutData[$key] = [
				'data'             => $val['data'],
				'custom_css'       => $val['custom_css'],
				'background_color' => $val['background_color'],
				'background_img'   => $val['background_img'],
				'style_data'       => $val['style_data']
			];
		}
		foreach ($pageInfo[1] as $key => $layout) {
			foreach ($layout as $pid => $position) {
				foreach ($position as $orderId => $item) {
					$uiInfo[$key][$pid][$orderId] = [
						'component_key'     => $item['component_key'],
						'id'                => $item['id'],
						'next_id'           => $item['next_id'],
						'tpl_id'            => $item['tpl_id'],
						'position'          => $item['position'],
						'async_data_format' => $item['async_data_format'] ?? ''
					];
					$uiData[$key][$pid][$item['id']] = [
						'data'   => $item['data'],
						'tpl_id' => $item['tpl_id']
					];
				}
			}
		}

		return [$layoutInfo, $layoutData, $uiInfo, $uiData];
	}

	/**
	 * 模板预览图上传
	 */
	public function picUpload ()
	{
		$uploadPath = \yii::getAlias('@app/runtime');
		$uploadPath .= DIRECTORY_SEPARATOR . $this->Path;
		$upload = new Upload($uploadPath);
		$file = $upload->uploadS3();
		if (!$file) {
			return app()->helper->arrayResult(1, $file->error);
		}

		return app()->helper->arrayResult(0, 'success', [
			'url' => $file['url']
		]);
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
	 * 根据siteCode获取适用范围
	 *
	 * @param string $siteCode 站点简称
	 *
	 * @return boolean  int
	 */
	private function getRangeBySiteCode ($siteCode)
	{
		$str = strstr($siteCode, '-');

		return isset($this->rangeSetting[$str]) ? $this->rangeSetting[$str] : 100;
	}
}
