<?php

namespace app\modules\base\components;


use app\common\util\PlatformUtils;
use app\modules\base\models\PageLanguageDataModel;
use app\modules\base\models\AdminModel;
use app\base\Upload;
use app\modules\base\models\PageLanguagePackageModel;
use app\modules\common\models\PageUiComponentDataModel;
use app\modules\common\models\PageUiModel;
use app\modules\component\models\UiComponentLanguageRelationModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use yii\db\Exception;

class LanguageDataComponent extends Component
{
	protected $importError = []; //导入语言错误内容

	protected $objPHPExcel;

	/**
	 * UploadComponent constructor.
	 */
	public function init ()
	{
		parent::init();
		$uploadPath = \yii::getAlias(app()->params['uploadsPath'] ?? '@app/runtime/excles');
		\yii::setAlias('@resourcePath', $uploadPath);
	}

	public function initExcel (string $file = '')
	{

		if (!empty($file)) {
			$excleReader = new \PHPExcel_Reader_Excel2007();
			if (!$excleReader->canRead($file)) {
				$excleReader = new \PHPExcel_Reader_Excel5();
			}
			$excleReader->setReadDataOnly(true);
			$this->objPHPExcel = $excleReader->load($file)->getActiveSheet();
		} else {
			$this->objPHPExcel = new \PHPExcel();
		}
	}

	/**
	 * 获取语言包内容列表
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function lists (array $params)
	{
		$langData = PageLanguagePackageModel::getSiteLanguageList($params['site_code']);
		$langArray = array_keys($langData);

		$data = PageLanguageDataModel::getLangDataList($langArray, 'id,key,update_user,update_time', $params);
		if (!empty($data['list']) && is_array($data['list'])) {
			foreach ($data['list'] as &$value) {
				$value['lang_value'] = PageLanguageDataModel::getLangValues($value['key']);
				if (!empty($value['lang_value'])) {
					$value['lang_value'] = array_map(function ($item) {
						return stripslashes($item);
					}, $value['lang_value']);
				}
				$modifyData = PageLanguageDataModel::getLangKeyLastModify($value['key']);
				$value['update_user'] = !empty($modifyData['update_user'])
					? AdminModel::getRealNameByUserName($modifyData['update_user'])
					: '';
				$value['update_time'] = !empty($modifyData['update_time'])
					? date('Y-m-d H:i:s', $modifyData['update_time'])
					: '';
			}
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}

	/**
	 * 新增多语言key
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function addKeys (array $params)
	{
		$keys = explode(',', $params['key']);
		$langZh = explode(',', $params['lang_zh']);
		$hasKey = PageLanguageDataModel::checkExistsLangData(['zh'], $keys);
		if (!empty($hasKey)) {
			foreach ($hasKey as $value) {
				$tips[] = "{$value['lang']}:{$value['key']}";
			}

			return app()->helper->arrayResult($this->codeFail, implode('|', $tips) . '已存在');
		}

		$combine = array_combine($keys, $langZh);
		$data = [];
		foreach ($combine as $key => $text) {
			$data[] = ['zh', $key, $text, app()->user->username, time(), app()->user->username, time()];
		}

		$columns = ['lang', 'key', 'value', 'create_user', 'create_time', 'update_user', 'update_time'];
		if (PageLanguagedataModel::insertAll($columns, $data)) {
			return app()->helper->arrayResult($this->codeSuccess, '保存成功');
		}

		return app()->helper->arrayResult($this->codeFail, '保存失败');
	}

	/**
	 * 修改多语言文案
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function editKeyValue (array $params)
	{
		$hasValue = PageLanguageDataModel::findOne(['key' => $params['key'], 'lang' => $params['lang']]);
		if ($hasValue) {
			$hasValue->value = addslashes(trim($params['value']));
			$hasValue->update_user = app()->user->username;
			$hasValue->update_time = $_SERVER['REQUEST_TIME'];
			$update = $hasValue->save();
		} else {
			$columns = ['key', 'lang', 'value', 'create_user', 'create_time', 'update_user', 'update_time'];
			$data = [
				$params['key'],
				$params['lang'],
				$params['value'],
				app()->user->username,
				time(),
				app()->user->username,
				time()
			];
			$update = PageLanguageDataModel::insertAll($columns, [$data]);
		}

		if (false !== $update) {
			return app()->helper->arrayResult($this->codeSuccess, '保存成功');
		}

		return app()->helper->arrayResult($this->codeFail, '保存失败');
	}

	/**
	 * 导出语言包
	 *
	 * @param array $params
	 */
	public function exportPackage (array $params)
	{
		$exportData = [];
		if (!empty($params['export_data']) && is_string($params['export_data'])) {
			$exportData = json_decode($params['export_data'], true);
		} elseif (!empty($params['lang'])) {
			$langArray = explode(',', $params['lang']);
			sort($langArray, SORT_STRING);
			if ($keyIndex = array_search('zh', $langArray)) {
				unset($langArray[$keyIndex]);
			}
			//如果要导出中文，就把中文放到首位
			array_unshift($langArray, 'zh');
			$langArray = array_values($langArray);
			$langResult = PageLanguageDataModel::getDataForLangs($langArray);
			if (!empty($langResult) && is_array($langResult)) {
				$langReturn = PageLanguagePackageModel::getLanguageZhName($params['site_code'], $langArray);
				if (!empty($langReturn)) {
					foreach ($langReturn as $lang => $name) {
						$langNames[] = "{$name}({$lang})";
					}
				}
				unset($langReturn);
				$headRow = array_merge(['key'], $langNames ?? []);
				$exportData = $this->buildExportData($langArray, $langResult);
				$exportData = array_merge([$headRow], $exportData);
			}
		}

		$this->initExcel();
		//设置当前的sheet
		$this->objPHPExcel->setActiveSheetIndex(0);
		//设置sheet的name
		$this->objPHPExcel->getActiveSheet()->setTitle("多语言文案");
		if (!empty($exportData) && is_array($exportData)) {
			$this->objPHPExcel->getActiveSheet()->setCellValue(
				'A1',
				'注：请不要修改key值、中文文案和语言后的简码,不需要的语言列可直接删除;样式中请不要合并单元格和使用背景颜色.'
			);
			$this->objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->getColor()->setRGB('FF0000');
			$string = \PHPExcel_Cell::stringFromColumnIndex(
				empty($params['export_data']) ? count($headRow) : count(current($exportData))
			);
			$this->objPHPExcel->getActiveSheet()->mergeCells('A1' . ':' . "{$string}1");
			//设置单元格的值
			foreach ($exportData as $row => $data) {
				foreach ($data as $col => $value) {
					$key = intToChr($col) . ($row + 2);
					$this->objPHPExcel->getActiveSheet()->setCellValue($key, $value);
				}
			}
		}
		ob_end_clean();
		//保存excel—2007格式
		$objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		//告诉浏览器数据excel2007文件
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//告诉浏览器将输出文件的名称
		header('Content-Disposition: attachment;filename=多语言文案管理.xlsx');
		//禁止缓存
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
	}

	/**
	 * 组装要导出的数据
	 *
	 * @param array $langArray
	 * @param array $langResult
	 *
	 * @return array
	 */
	private function buildExportData (array $langArray, array $langResult)
	{
		$exportData = $rowData = [];
		if (!empty($langArray) && !empty($langResult)) {
			foreach ($langResult as $result) {
				$rowData[$result['key']][$result['lang']] = $result['value'];
			}
			unset($langResult);
			foreach ($rowData as $keyRow => $itemRow) {
				$temp = [];
				foreach ($langArray as $lang) {
					array_push($temp, $itemRow[$lang] ?? '');
				}
				array_unshift($temp, $keyRow);
				$exportData[] = $temp;
			}
		}

		return $exportData;
	}

	/**
	 * 导入多语言包
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function importPackage (array $params)
	{
		if (empty($params['lang']) || empty($params['site_code'])) {
			return app()->helper->arrayResult($this->codeFail, '参数不正确.');
		}
		$langArray = explode(',', $params['lang']);
		$upload = new Upload(\yii::getAlias('@resourcePath'));
		$file = $upload->uploadPath();
		if (0 != $file->error) {
			return app()->helper->arrayResult(1, $file->error);
		}
		//检查选择的语言是否存在于站点语言包
		$hasLang = PageLanguagePackageModel::checkExistsLangPackage($params['site_code'], $langArray, 'lang');
		$intersectLang = array_intersect($langArray, $hasLang);
		$diffLang = array_diff($langArray, $intersectLang);
		if (!empty($diffLang)) {
			return app()->helper->arrayResult(
				$this->codeFail,
				implode(',', $diffLang) . "在{$params['site_code']}站点语言包中不存在"
			);
		}

		$this->initExcel($file);
		$highestRow = $this->objPHPExcel->getHighestRow(); // 取得总行数
		$highestColumn = $this->objPHPExcel->getHighestColumn(); // 取得总列数(字母格式)
		//将字母格式的最大列数转换为数字
		$highestColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn) - \PHPExcel_Cell::columnIndexFromString('A') + 1;
		$headLangArray = $this->getExcelSheetRow($highestColumn, 2, 1);
		$head = $this->getExcelSheetRow($highestColumn, 2);
		$intersectLang = array_intersect($langArray, $headLangArray);
		$diffLang = array_diff($langArray, $intersectLang);
		if (!empty($diffLang)) {
			return app()->helper->arrayResult($this->codeFail, implode(',', $diffLang) . '表格中不存在');
		}

		foreach ($this->yieldPackageRowData($highestRow, $highestColumn, $headLangArray, $langArray, $params['is_cover']) as $row) {
			if (isset($row['data']) && !empty($row['data'])) {
				if (false === PageLanguageDataModel::saveLanguagePackage($row['data'])) {
					$this->importError[] = $this->getExcelSheetRow($highestColumn, $row['row_index']);
				}
			}
		}

		$failToal = count($this->importError);
		$successToal = $highestRow - $failToal;
		$tips = "上传记录：总共上传{$highestRow}条数据，成功{$successToal}条，失败{$failToal}条！查看失败详情请下载失败文件.";

		return app()->helper->arrayResult(
			$this->codeSuccess,
			$tips,
			!empty($this->importError) ? json_encode(array_merge([$head], $this->importError)) : []
		);
	}

	/**
	 * 协程读取语言包Excel文件内容
	 *
	 * @param int   $highestRow
	 * @param int   $highestColumn
	 * @param array $head
	 * @param array $langArray
	 * @param int   $isCover
	 *
	 * @return \Generator
	 */
	private function yieldPackageRowData (int $highestRow, int $highestColumn, array $head, array $langArray, int $isCover = 0)
	{
		for ($row = 3; $row <= $highestRow; $row++) {
			$yieldData = $langs = $langResult = [];
			for ($col = 0; $col < $highestColumn; $col++) {
				$rowValue = $this->objPHPExcel->getCellByColumnAndRow($col, $row)->getValue();
				if (0 == $col) {
					if (!empty($rowValue)) {
						$langKey = addslashes(trim($rowValue));
						$langResult = PageLanguageDataModel::getLangsForKey($langKey);
					} else {
						$this->importError[] = $this->getExcelSheetRow($highestColumn, $row);
					}
				} else {
					//排除中文文案，只处理选择的要导入的语言，文案为空时直接忽略
					if (
						!empty($langResult)
						&& 'zh' != $head[$col]
						&& in_array($head[$col], $langArray)
						&& !empty($rowValue)
					) {
						if (!empty($langResult[$head[$col]])) {
							if ($isCover == 1) {
								$yieldData['data']['update'][] = [
									'key'   => $langKey ?? '',
									'lang'  => $head[$col],
									'value' => addslashes(trim($rowValue))
								];
							}
						} else {
							$yieldData['data']['insert'][$head[$col]] =
								[
									'key'         => $langKey ?? '',
									'lang'        => $head[$col],
									'value'       => addslashes(trim($rowValue)),
									'create_user' => app()->user->username,
									'create_time' => time(),
									'update_user' => app()->user->username,
									'update_time' => time()
								];
						}
					}
				}
			}
			$yieldData['row_index'] = $row;

			yield $yieldData;
		}
	}

	/**
	 * 获取excel中的某一行内容
	 *
	 * @param int $highestColumn
	 * @param int $rowIndex
	 *
	 * @return array
	 */
	public function getExcelSheetRow (int $highestColumn, int $rowIndex, int $isMatch = 0)
	{
		$data = [];
		for ($i = 0; $i < $highestColumn; $i++) {
			$value = $this->objPHPExcel->getCellByColumnAndRow($i, $rowIndex)->getValue();
			if (!empty($isMatch)) {
				preg_match("/(?:\()(.*)(?:\))/i", $value, $matches);
				array_push($data, !empty($matches[1]) ? strtolower($matches[1]) : strtolower($value));
			} else {
				array_push($data, $value);
			}
		}

		return $data;
	}

	/**
	 * 获取所有多语言的key
	 *
	 * @return array
	 */
	public function getKeysOptions ()
	{
		$data = PageLanguageDataModel::find()
			->select('id, key')
			->where(['lang' => 'zh'])
			->asArray()
			->all();

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}

	/**
	 * 获取组件绑定的UI组件模板
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function getKeyUiComponent (string $key)
	{
		$data = UiComponentLanguageRelationModel::getKeyUiComponent($key);
		if (!empty($result) && is_array($result)) {
			foreach ($result as &$value) {
				$value['platform'] = PlatformUtils::getPlatformNameByType($value['range']);
			}
		}

		return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
	}

	/**
	 * 获取对应语言的语言包
	 *
	 * @param string $lang
	 *
	 * @return array
	 */
	public function getLanguageDataForLang (string $lang)
	{
		$data = PageLanguageDataModel::getLangAllKeysData($lang, 'key, value');

		return !empty($data) ? array_column($data, 'value', 'key') : [];
	}
}
