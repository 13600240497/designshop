<?php

namespace app\modules\common\zf\components;

use app\components\auto\AutoRefreshUi;
use app\modules\common\models\NativePageLayoutModel;
use app\modules\common\models\NativePageUiComponentModel;
use app\modules\component\models\{
	UiModel, UiTplModel
};
use app\modules\common\zf\models\{
	PageModel,
	PageUiDataModel,
	PageUiModel,
	PageLayoutModel,
	PageUiComponentDataModel,
	PageLanguageModel
};
use ego\base\JsonResponseException;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 页面装修设计-UI组件部分
 */
class CommonPageUiDesignComponent extends CommonPageDesignComponent
{
	const FIELD_LAYOUT_ID = 'layout_id';
	const FIELD_POSITION = 'position';

	/**
	 * 复制UI组件
	 *
	 * @param array $data [
	 *                    'layout_id' => '当前布局组件ID',
	 *                    'prev_id' => '前一个UI组件id',
	 *                    'position' => '布局位置',
	 *                    'copy_id' => '被复制的UI组件ID',
	 *                    'lang' => '语言代码简称（当前展示所属的语言）'
	 *                    ]
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
	public function copyUiComponent ($data)
	{
		$data = array_map('trim', $data);
		if (!isset($data[static::FIELD_LAYOUT_ID], $data['prev_id'], $data['copy_id'],
			$data[static::FIELD_POSITION], $data['lang'], $data['num'])
		) {
			throw new JsonResponseException($this->codeFail, '参数不全');
		}
		$num = (int)$data['num'];
		if ($num > 10 || $num < 1) {
			throw new JsonResponseException($this->codeFail, '请输入1到10之间的数字');
		}

		$this->checkLang($data['lang']);
		$layout = PageLayoutModel::findOne((int)$data[static::FIELD_LAYOUT_ID]);
		if (!$layout || $layout['lang'] !== $data['lang']) {
			throw new JsonResponseException($this->codeFail, '布局组件不存在或已被删除');
		}

		$data['next_id'] = $this->getNextIdByPrevId(
			$data['prev_id'],
			$data[static::FIELD_LAYOUT_ID],
			$data[static::FIELD_POSITION],
			$data['lang']
		);
		$html = '';
		$htmlArr = [];
		$errors = [];
		$datas = [];
		$uiIds = [];
		for ($i = 0; $i < $num; $i++) {
			$ui = new CommonUi();
			$ui->needStatic = true;
			$ui->pipeline = $this->pageInfo->pipeline;
			$ui->siteCode = $this->pageInfo->site_code;
			$ui->lang = $data['lang'];
			$ui->activityId = $this->pageInfo->activity_id;
			$ui->pageId = $this->pageInfo->id;
			if (!$ui->copyUi($data)) {
				throw new JsonResponseException($this->codeFail, $ui->errors);
			}

			$data['next_id'] = $ui->instanceId;

			if (app()->controller->getRoute() == "activity/zf/ui-design/copy-ui") {
				//同步创建IPS活动参数构建
				$page_ui = PageUiModel::findOne($ui->instanceId);
				if (empty($page_ui)) {
					continue;
				}
				$ui_datas = PageUiComponentDataModel::find()->where(['component_id' => $ui->instanceId, "tpl_id" => $page_ui->tpl_id])->asArray()->all();

				$is_need_sync = false;
				$is_ips = false;
				$is_rule = false;
				$ipsFilterInfo = [];
				foreach ($ui_datas as $ui_data) {
					if ($ui_data['key'] == "goodsDataFrom" &&
						$ui_data['value'] == 2) {
						$is_ips = true;
					}
					if ($ui_data['key'] == "ipsMethods" &&
						$ui_data['value'] == 3) {
						$is_rule = true;
					}
					if ($ui_data['key'] == "ipsFilterInfo") {

						if (!empty(json_decode($ui_data['value'], 1))) {

							$ipsFilterInfo = json_decode($ui_data['value'], 1);
						}
					}

				}
				$is_need_sync = $is_ips && $is_rule;
				$tpl_id = PageUiModel::findOne($ui->instanceId);
				if ($is_need_sync && $ipsFilterInfo) {
					//规则选品需同步
					$tmp['page_id'] = $this->pageInfo->id;
					$tmp['lang'] = $data['lang'];
					$tmp['id'] = $ui->instanceId;
					$tmp['tpl_id'] = $tpl_id->tpl_id;
					$tmp['is_auto_activity'] = 2;
					$tmp['ips_activity_child_id'] = $ipsFilterInfo["ips_activity_child_id"];
					$datas[] = $tmp;
				}
			}


			//导航标题数据
			if (!empty($ui->data['nav_menu'])) {
				$ui->data['navData'] = $this->getAvailableNavigation($ui->instanceId, $ui->lang);
			}
			if (false === ($componentHtml = $ui->renderComponent($ui))) {//组件解析
				$errors[] = $ui->errors;
				$componentHtml = sprintf(
					$this->uiParseErrorTpl,
					$ui->key,
					$ui->instanceId,
					'ui组件解析失败：' . $ui->errors
				);
			}
			$htmlArr[$i] = $componentHtml;
			$uiIds[] = $ui->instanceId;
		}

		krsort($htmlArr);
		foreach ($htmlArr as $item) {
			$html .= $item;
		}
		//同步创建IPS活动
		if (app()->controller->getRoute() == "activity/zf/ui-design/copy-ui") {
			\app\modules\common\components\CommonActivityComponent::batchCreateActivityToIps($datas);
		}
		return app()->helper->arrayResult(0, '复制成功', ['uiIds' => $uiIds, 'component_html' => $html], ['errors' => $errors]);
	}

	/**
	 * 添加UI组件
	 *
	 * @param array $data [
	 *                    'layout_id' => '当前布局组件ID',
	 *                    'prev_id' => '前一个组件id',
	 *                    'component_key' => '当前组件key',
	 *                    'position' => '布局位置'
	 *                    ]
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
	public function addUiComponent ($data)
	{
		if (!isset($data[static::FIELD_LAYOUT_ID], $data['lang'], $data['prev_id'], $data['component_key'],
			$data[static::FIELD_POSITION])
		) {
			throw new JsonResponseException($this->codeFail, '参数不全');
		}
		$layout = PageLayoutModel::findOne((int)$data[static::FIELD_LAYOUT_ID]);
		if (!$layout || $layout['lang'] !== $data['lang']) {
			throw new JsonResponseException($this->codeFail, '布局组件不存在或已被删除');
		}
		if (self::SUCCESS_MSG !== ($checkMsg = $this->checkUIParams($data))) {
			throw new JsonResponseException($this->codeFail, $checkMsg);
		}

		if (!(int)$data['prev_id'] && $prevModel = PageUiModel::findOne($data['prev_id'])) {
			// 当prev_id有值时，则layout_id、position等信息从数据库查询
			$data[static::FIELD_LAYOUT_ID] = $prevModel->layout_id;
			$data[static::FIELD_POSITION] = $prevModel->position;
		}

		$data['next_id'] = $this->getNextIdByPrevId(
			$data['prev_id'],
			$data[static::FIELD_LAYOUT_ID],
			$data[static::FIELD_POSITION],
			$data['lang']
		);

        // 如果组件默认模板，不支持当前站点，使用支持的模板列表中的第一个模板
        $defaultTplId = UiModel::getDefaultTplIdByKey($data['component_key']);
        $supportTplList = UiTplModel::getComponentTplList($data['component_key'], $this->pageInfo->site_code);
        $supportTplIds = array_column($supportTplList, 'id');
        if (in_array($defaultTplId, $supportTplIds)) {
            $data['tpl_id'] = $defaultTplId;
        } else {
            $data['tpl_id'] = reset($supportTplIds);
        }

		$ui = new CommonUi();
		if (!$ui->addComponent($data)) {
			throw new JsonResponseException($this->codeFail, $ui->errors);
		}

		$ui->needStatic = true;
		$ui->pipeline = $this->pageInfo->pipeline;
		$ui->siteCode = $this->pageInfo->site_code;
		$ui->lang = $data['lang'];
		$ui->activityId = $this->pageInfo->activity_id;
		$ui->pageId = $this->pageInfo->id;

		// 如果选择组件模板
		$asyncDataInfo = null;
		if (isset($data['ui_tpl_id']) && is_numeric($data['ui_tpl_id'])) {
			$commonPageUiTplComponent = new CommonPageUiTplComponent();
			$commonPageUiTplComponent->saveUiDataFromTpl($data['ui_tpl_id'], $ui);

			if (!empty($ui->model->async_data_format)) {
				$autoRefreshUi = new AutoRefreshUi($this->pageInfo, $ui->model);
				$autoRefreshUi->prepareAsyncData();
				$asyncDataInfo = $autoRefreshUi->getAsyncData();
				$ui->data['is_auto_refresh_ui'] = true;
			}
		}

		$ui->data['navData'] = $this->getAvailableNavigation($ui->instanceId, $data['lang']);
		if (!empty($ui->data['navData']) && empty($ui->data['nav_menu'])) {
			foreach (array_keys($ui->data['navData']) as $v) {
				$ui->data['nav_menu'][] = (string)$v;
			}
		}
		if (false === ($componentHtml = $ui->renderComponent($ui))) {//组件解析
			$componentHtml = sprintf(
				$this->uiParseErrorTpl,
				$ui->key,
				$ui->instanceId,
				'ui组件解析失败：' . $ui->errors
			);
		}

		$jsonData = ['ui_id' => $ui->instanceId, 'component_html' => $componentHtml];
		!empty($asyncDataInfo) && $jsonData['async_data_info'] = $asyncDataInfo;
		return app()->helper->arrayResult(0, '添加成功', $jsonData, ['errors' => $ui->errors]);
	}

	/**
	 * 移动页面UI组件
	 *
	 * @param array $data [
	 *                    'id' => '当前UI组件ID',
	 *                    'prev_id' => '移动位置之后前一个UI组件位置ID,如果没有就为0',
	 *                    'layout_id' => '移动之后的布局组件ID，当layout改变表示UI换了Layout',
	 *                    'position' => '移动之后的组件位置'
	 *                    ]
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 */
	public function moveUiComponent ($data)
	{
		if (!isset($data['id'], $data['prev_id'], $data[static::FIELD_LAYOUT_ID],
			$data[static::FIELD_POSITION], $data['lang'])
		) {
			throw new JsonResponseException($this->codeFail, '参数不全');
		}

		$uiModel = PageUiModel::findOne($data['id']);
		if (!$uiModel || $uiModel['lang'] !== $data['lang']) {
			throw new JsonResponseException($this->codeFail, 'id错误，找不到对应的ui记录');
		}

		$data['oldprev_id'] = $this->getPrevId($data['id'], $uiModel->layout_id, $uiModel->position);

		if ("{$uiModel->layout_id}_{$data['oldprev_id']}" != "{$data['layout_id']}_{$data['prev_id']}") {
			$data['oldnext_id'] = $this->getNextIdByPrevId($data['id'], $uiModel->layout_id, $uiModel->position, $data['lang']);
			$data['next_id'] = $this->getNextIdByPrevId(
				$data['prev_id'],
				$data[static::FIELD_LAYOUT_ID],
				$data[static::FIELD_POSITION],
				$data['lang']
			);
			if ("{$uiModel->layout_id}_{$data['oldnext_id']}" != "{$data['layout_id']}_{$data['next_id']}") {
				$ui = new CommonUi();
				if (!$ui->pageMoveComponent($data)) {
					//如果没有返回组件id
					throw new JsonResponseException($this->codeFail, $ui->errors);
				}
			}
		}

		return app()->helper->arrayResult(0, '保存成功');
	}

	/**
	 * 删除页面UI组件
	 *
	 * @param array $data [
	 *                    'id' => '当前UI组件ID',
	 *                    ]
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\db\Exception
	 * @throws \Throwable
	 */
	public function deleteUIComponent ($data)
	{
		if (!isset($data['id'], $data['lang'])) {
			throw new JsonResponseException($this->codeFail, '参数不全');
		}

		$uiModel = PageUiModel::findOne($data['id']);
		if (!$uiModel || $uiModel['lang'] !== $data['lang']) {
			throw new JsonResponseException($this->codeFail, 'id错误，找不到对应的ui记录');
		}

		$data['prev_id'] = $this->getPrevId($data['id'], $uiModel->layout_id, $uiModel->position);
		$data['next_id'] = $this->getNextIdByPrevId($data['id'], $uiModel->layout_id, $uiModel->position, $data['lang']);
		$ui = new CommonUi();
		$ui->siteCode = $this->pageInfo->site_code;
		$ui->pageId = $this->pageInfo->id;
		$ui->pipeline = $this->pageInfo->pipeline;
		$ui->lang = $data['lang'];
		$ui->activityId = $this->pageInfo->activity_id;
		//删除用户保存数据
		$ui->delUserData($data['id']);
		//取消三端数据绑定
		$ui->cancelUiComponentBindRelation($data['id'], $data['lang']);


		if (!$ui->pageRemoveComponent($data, PageUiDataModel::COMPONENT_TYPE)) {
			//如果没有返回组件id
			throw new JsonResponseException($this->codeFail, $ui->errors);
		}

		//同步删除IPS子活动
		$page_model = PageModel::findOne($data['page_id']);
		$sync_data['geshop_activity_id'] = $page_model->activity_id;
		$sync_data['del_info'] = [
			[
				'geshop_component_ui_id' => $data['id']
			]
		];
		\app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);

		return app()->helper->arrayResult(0, '删除成功');
	}

	/**
	 * 获取ui的form表单
	 *
	 * @param int    $pageUiId UI组件在page中的记录ID
	 * @param string $lang     语言代码简称
	 * @param int    $tplId    模板id
	 *
	 * @return array
	 * @throws \ego\base\JsonResponseException
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Throwable
	 * @throws \Exception
	 */
	public function getForm ($pageUiId, $lang, $tplId = 0)
	{
		if (!$uiModel = PageUiModel::findOne($pageUiId)) {
			throw new JsonResponseException($this->codeFail, '未找到对应的UI组件信息');
		}

		$ui = new CommonUi();
		$ui->instanceId = $uiModel->id;
		$ui->key = $uiModel->component_key;
		$ui->tpl = self::FORM_NAME;

		$selected = $ui->tplId = $tplId > 0 ? (int)$tplId : (int)$uiModel->tpl_id;
		if (empty($ui->tplId)) {
			$uiComponent = UiModel::findOne(['component_key' => $ui->key]);
			$ui->tplId = $uiComponent->tpl_id;
		}

		$ui->data = PageUiComponentDataModel::getDataList($uiModel->id, $lang, $ui->tplId);
		$ui->data['navData'] = $this->getAvailableNavigation($uiModel->id, $lang);
		if (!isset($ui->data['nav_menu'])) {
			foreach (array_keys($ui->data['navData']) as $v) {
				$ui->data['nav_menu'][] = (string)$v;
			}
		}

		$selectedTplId = $selected ?: $ui->tplId;
		$selectedTplInfo = UiTplModel::getTplFullInfo($selected ?: $ui->tplId);
		$ui->data['templates'] = [
			'list'          => UiTplModel::getComponentTplList($ui->key, $this->pageInfo->site_code),
			'selected'      => $selectedTplId,
			'selected_name' => $selectedTplInfo->getAttribute('name'),
			'selected_tpl'  => ArrayHelper::toArray($selectedTplInfo)
		];

		// 获取页面分享信息
		$ui->shareData = PageLanguageModel::getShareData($this->pageInfo->id, $uiModel->lang);

		$ui->siteCode = $this->pageInfo->site_code;
		$ui->lang = $uiModel->lang;
		$ui->pipeline = $this->pageInfo->pipeline;
		$ui->activityId = $this->pageInfo->activity_id;
		$ui->pageId = $this->pageInfo->id;
		if (!$componentHtml = $ui->renderComponent($ui)) {//组件解析
			throw new JsonResponseException($this->codeFail, $ui->errors);
		}

		return app()->helper->arrayResult(0, '获取成功', [
			'component_html' => $componentHtml,
			'tpl_id'         => $ui->tplId,
			'bind_relation'  => $uiModel->bind_relation
		], ['data' => $ui->data, 'errors' => $ui->errors]);
	}

	/**
	 * 保存UI组件表单数据
	 *
	 * @param array $data [
	 *                    'id' => '当前UI组件ID',
	 *                    'data' => '用户配置的json格式数据',
	 *                    'tpl_id' => '模板ID',
	 *                    ]
	 *
	 * @return array
	 * @throws \yii\db\Exception
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Exception
	 * @throws \ego\base\JsonResponseException
	 */
	public function saveForm ($data)
	{
		if (!isset($data['id'], $data['private_data'], $data['public_data'], $data['lang'])) {
			throw new JsonResponseException($this->codeFail, '参数不全');
		}

		$uiModel = PageUiModel::findOne($data['id']);
		if (empty($uiModel)) {
			throw new JsonResponseException($this->codeFail, '找不到组件');
		}

		// 兼容下线模板，使用组件当前模板
		if (!isset($data['tpl_id']) || empty($data['tpl_id'])) {
			$data['tpl_id'] = $uiModel->tpl_id;
		}

		// !!!基于前端将goodsSKU随机放在private_data和public_data中，这里做下合并处理
		$uiData = array_merge(json_decode($data['private_data'], true), json_decode($data['public_data'], true));

		// 检查sku是否重复
		if (isset($uiData[PageUiComponentDataModel::KEY_SKU]) && $diffSku = $this->checkDuplicatesForSku($uiData)) {
			throw new JsonResponseException($this->codeFail, 'SKU ' . implode(',', $diffSku) . ' 重复，请核对后重新提交');
		}

		// 检查sku是否存在
		$skus = $this->formSkuToArray($uiData);
		if (!empty($skus)) {
			$goodsData = [
				'uiId'     => $data['id'],
				'skus'     => implode(',', $skus),
				'lang'     => $data['lang'],
				'pipeline' => $this->pageInfo->pipeline
			];
			$noExist = (new CommonGoodsComponent())->checkGoodsExists($goodsData);
			if (!empty($noExist)) {
				throw new JsonResponseException($this->codeFail, "SKU {$noExist} 不存在！");
			}
		}

		$ui = new CommonUi();
		$data['site_code'] = $this->pageInfo->site_code;
		$data['page_id'] = $this->pageInfo->id;
		$data['pipeline'] = $this->pageInfo->pipeline;
		if (!$ui->saveFormData($data)) {
			throw new JsonResponseException($this->codeFail, $ui->errors);
		}

		// 组件自动刷新检查异步数据格式并预处理数据
		$asyncDataInfo = null;
		if (array_key_exists('goodsInfoArr', $data)) {
			if (!empty($data['goodsInfoArr'])) {
				$uiModel->async_data_format = $data['goodsInfoArr'];
				$autoRefreshUi = new AutoRefreshUi($this->pageInfo, $uiModel);
				$autoRefreshUi->prepareAsyncData();

				if (!$uiModel->save(true)) {
					throw new JsonResponseException($this->codeFail, '保存组件数据失败!');
				}

				try {
					$asyncDataInfo = $autoRefreshUi->getAsyncData();
					$ui->data['is_auto_refresh_ui'] = true;
				} catch (\Exception $e) {
					$_data = ['message' => $e->getMessage()];
					throw new JsonResponseException($this->codeFail, '解析组件异步数据格式错误!', $_data);
				}
			}
		} else {
			// 清空异步数据格式，走老渲染方式
			$uiModel->async_data_format = '';
			if (!$uiModel->save(true)) {
				throw new JsonResponseException($this->codeFail, '保存组件数据失败!');
			}
		}

		// 导航标题数据
		if (!empty($ui->data['nav_menu'])) {
			$ui->data['navData'] = $this->getAvailableNavigation($ui->instanceId, $ui->lang);
		}

		$ui->needStatic = true;
		$ui->siteCode = $this->pageInfo->site_code;
		$ui->activityId = $this->pageInfo->activity_id;
		$ui->pageId = $this->pageInfo->id;
		$ui->pipeline = $this->pageInfo->pipeline;
		if (!$componentHtml = $ui->renderComponent($ui)) {//组件解析
			throw new JsonResponseException($this->codeFail, $ui->errors);
		}

		//有传选中的select_tpl_id，则将选中的模板也渲染出来返回
		$selectTplId = $data['select_tpl_id'] ?? $data['tpl_id'];
		$selectComponentHtml = (int)$selectTplId === (int)$data['tpl_id']
			? $componentHtml : $this->getSelectUiHtml($ui, $selectTplId);

		//有传选中的select_tpl_id，则需要同步到ips修改名称
		if (!empty($data['select_tpl_id'])) {
			//修改活动信息需要同步到IPS
			$ui_tpl = UiTplModel::find()->alias('tpl')
				->select('tpl.*, ui.name as ui_name, ui.component_key')
				->leftJoin(UiModel::tableName() . ' as ui', 'tpl.component_key = ui.component_key')
				->where([
					'tpl.id'        => $data['select_tpl_id'],
					'tpl.is_delete' => UiTplModel::NOT_DELETE,
					'ui.is_delete'  => UiModel::NOT_DELETE
				])->asArray()->one();
			if (!empty($ui_tpl['ui_name'])) {
				$geshop_component_name = $ui_tpl['ui_name'];

				if (!empty($ui_tpl['name'])) {
					$geshop_component_name .= "-" . $ui_tpl['name'];
				}
				$sync_data['geshop_activity_id'] = $this->pageInfo->activity_id;
				$sync_data['geshop_component_ui_id'] = $data['id'];
				$sync_data['geshop_component_name'] = $geshop_component_name;
				try {
					\app\modules\common\components\CommonActivityComponent::SyncActivityToIps($sync_data);
				} catch (\Exception $e) {

				}
			}

		}

		$jsonData = [
			'tpl_id'                => $data['tpl_id'],
			'component_html'        => $componentHtml,
			'select_tpl_id'         => $selectTplId,
			'select_component_html' => $selectComponentHtml
		];
		!empty($asyncDataInfo) && $jsonData['async_data_info'] = $asyncDataInfo;
		return app()->helper->arrayResult(0, '保存成功', $jsonData, ['errors' => $ui->errors]);
	}

	/**
	 * 获取选中模板ID对应ui的html
	 *
	 * @param \app\modules\common\zf\components\CommonUi $ui
	 * @param int                                        $selectTplId
	 *
	 * @return string
	 * @throws \yii\base\ViewNotFoundException
	 * @throws \Exception
	 * @throws JsonResponseException
	 */
	private function getSelectUiHtml ($ui, $selectTplId)
	{
		if (empty($selectTplId)) {
			return '';
		}

		$ui->tplId = $selectTplId;
		$ui->data = PageUiComponentDataModel::getDataList($ui->instanceId, $ui->lang, $ui->tplId);

		if (!$componentHtml = $ui->renderComponent($ui)) {//组件解析
			throw new JsonResponseException($this->codeFail, $ui->errors);
		}

		return $componentHtml;
	}

	/**
	 * 表单SKU转成数组
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function formSkuToArray ($data)
	{
		if (empty($data[PageUiComponentDataModel::KEY_SKU])) {
			return [];
		}

		$data[PageUiComponentDataModel::KEY_SKU] = trim($data[PageUiComponentDataModel::KEY_SKU]);
		$goodsSku = [];
		if (json_decode($data[PageUiComponentDataModel::KEY_SKU])) {
			$goodsArray = json_decode($data[PageUiComponentDataModel::KEY_SKU], true);
			if (\is_array($goodsArray) && isset($goodsArray[0]['lists'])) {
				$goodsSku = array_column($goodsArray, 'lists');
			} elseif (\is_string($goodsArray) || \is_int($goodsArray)) {
				$goodsSku = [$goodsArray];
			}
		} else {
			$goodsSku = explode(',', $data[PageUiComponentDataModel::KEY_SKU]);
		}

		return array_filter(array_unique($goodsSku));
	}

	/**
	 * UI组件配置时，校验前端元素传递的值是否合理
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function checkUIParams ($data)
	{
		if (isset($data['component_key']) && !UiModel::findOne(['component_key' => $data['component_key']])) {
			return '非法的参数component_key，查不到对应数据';
		}

		return self::SUCCESS_MSG;
	}

	/**
	 * 检查输入的sku是否有重复
	 *
	 * @param array $data 用户提交的string的json数据
	 *
	 * @return array
	 */
	public function checkDuplicatesForSku ($data)
	{
		if (!empty($data['goodsSKU'])) {
			if (json_decode($data['goodsSKU'])) {
				$goodsSku = json_decode($data['goodsSKU'], true);
				if (!empty($goodsSku) && \is_array($goodsSku)) {
					return $this->checkDuplicatesForMultiSku($goodsSku);
				}
			} else {
				$goodsSku = explode(',', $data['goodsSKU']);
				$uniqueGoodsSku = array_unique($goodsSku);

				return (\count($goodsSku) === \count($uniqueGoodsSku))
					? [] : array_unique(array_diff_assoc($goodsSku, $uniqueGoodsSku));
			}
		}
	}

	/**
	 * 检查多维数组
	 *
	 * @param array $goodsSku
	 *
	 * @return array
	 */
	public function checkDuplicatesForMultiSku ($goodsSku)
	{
		foreach ($goodsSku as $value) {
			$listsSku = explode(',', $value['lists']);
			$uniqueGoodsSku = array_unique($listsSku);
			if (\count($listsSku) !== \count($uniqueGoodsSku)) {
				return array_unique(array_diff_assoc($listsSku, $uniqueGoodsSku));
			}
		}

		return [];
	}

	/**
	 * 根据当前节点ID查询上一节点ID
	 *
	 * @param int $uiId     当前节点ID
	 * @param int $layoutId 布局组件ID
	 * @param int $position ui组件在布局组件中的位置
	 *
	 * @return int
	 */
	public function getPrevId ($uiId, $layoutId, $position)
	{
		$prev = PageUiModel::findOne([
			'layout_id' => $layoutId,
			'next_id'   => $uiId,
			'position'  => $position
		]);

		return $prev ? (int)$prev->id : 0;
	}

	/**
	 * 根据上一节点ID查询下一节点ID
	 *
	 * @param int    $prevId   上一节点ID
	 * @param int    $layoutId 布局组件ID
	 * @param int    $position ui组件在布局组件中的位置
	 * @param string $lang     语言
	 *
	 * @return int
	 */
	public function getNextIdByPrevId ($prevId, $layoutId, $position, $lang)
	{
		$prev = PageUiModel::findOne($prevId);

		return $prev ? (int)$prev->next_id : $this->getFirstId($layoutId, $position, $lang);
	}

	/**
	 * 获取layout对应position位置首位layoutId
	 *
	 * @param int $layoutId 布局组件ID
	 * @param int $position ui组件在布局组件中的位置
	 *
	 * @return int
	 */
	private function getFirstId ($layoutId, $position, $lang)
	{
		$list = PageUiModel::find()
			->where(['layout_id' => $layoutId, 'position' => $position, 'lang' => $lang])
			->asArray()
			->all();

		$list = $this->getOrderedComponents($list);

		if (!$list) {
			//list为空放在getOrderedComponents后面判断，这样可以过滤掉数据库中的脏数据的影响
			return 0;
		}

		return (int)$list[0]['id'];
	}

	/**
	 * 保存原生页UI组件表单数据
	 *
	 * @param array $data
	 *
	 * @return array
	 * @throws JsonResponseException
	 */
	public function saveNativeForm (array $data)
	{
		//校验传参
		$rules = [
			[['page_id', 'group_id', 'pipeline', 'lang'], 'required'],
			[['page_id'], 'integer'],
			[['group_id', 'pipeline', 'lang'], 'string'],
			['layouts', 'each', 'rule' => ['integer']]
		];
		$model = app()->validatorModel->new($rules)->load($data);
		if (false === $model->validate()) {
			throw new JsonResponseException($this->codeFail, implode('|', array_column($model->errors, 0)));
		}
		if (!empty($data['components']) && is_array($data['components'])) {
			foreach ($data['components'] as $components) {
				// 检查sku是否重复
				if (!empty($components[PageUiComponentDataModel::KEY_SKU])) {
					foreach ($components[PageUiComponentDataModel::KEY_SKU] as $componentSku) {
						if ($diffSku = $this->checkDuplicatesForSku($componentSku)) {
							throw new JsonResponseException($this->codeFail, 'SKU ' . implode(',', $diffSku) . ' 重复，请核对后重新提交');
						}
						// 检查sku是否存在
						$skus = $this->formSkuToArray($componentSku);
						if (!empty($skus)) {
							$goodsData = [
								'uiId'     => $data['id'],
								'skus'     => implode(',', $skus),
								'lang'     => $data['lang'],
								'pipeline' => $this->pageInfo->pipeline
							];
							$noExist = (new CommonGoodsComponent())->checkGoodsExists($goodsData);
							if (!empty($noExist)) {
								throw new JsonResponseException($this->codeFail, "SKU {$noExist} 不存在！");
							}
						}
					}
				}
			}
		}

		$transaction = app()->db->beginTransaction();
		try {
			NativePageLayoutModel::saveFormData(['page_id' => $data['page_id'], 'lang' => $data['lang'], 'data' => $data['layouts'] ?? []]);
			NativePageUiComponentModel::saveFormData(['page_id' => $data['page_id'], 'lang' => $data['lang'], 'data' => $data['components'] ?? []]);
			// 同步其它端原生页面组件内容
			if (!empty($asyncPageId = $this->getAsyncNativePageId($data['page_id'], $data['pipeline'], $data['lang']))) {
				NativePageLayoutModel::saveFormData(['page_id' => $asyncPageId, 'lang' => $data['lang'], 'data' => $data['layouts'] ?? []]);
				NativePageUiComponentModel::saveFormData(['page_id' => $asyncPageId, 'lang' => $data['lang'], 'data' => $data['components'] ?? []]);
			}

			$transaction->commit();
		} catch (Exception $exception) {
			throw new JsonResponseException($this->codeFail, '组件表单保存失败!');
			$transaction->rollBack();
		}

		return app()->helper->arrayResult($this->codeSuccess, '保存成功,你可在当前页继续编辑');
	}
}
