<?php

namespace app\modules\admin\components;

use app\modules\admin\models\LanguageModel;
use app\modules\admin\models\LanguageTransModel;
use app\modules\component\components\ExplainComponent;
use yii\helpers\ArrayHelper;

/**
 * 多语言文件处理组件
 */
class LanguageFileComponent extends Component
{
    const FIELD_CONTENT = 'content';
    const FIELD_ALIAS = 'alias';
    const FIELD_KEY_NAME = 'key_name';
    const FIELD_IS_JS = 'is_js';
    const FIELD_ACTIVITY_ID = 'activity_id';
    const FIELD_REMARK = 'remark';

    /**
     * @var string 默认语言
     */
    public $defaultLang;

    /**
     * @var string 默认模板文件后缀
     */
    protected $defaultTplSuffix = 'twig';

    /**
     * @var string 默认提取语言包备注信息
     */
    private $defaultExtractRemark = '组件提取的待翻译文本';

    /**
     * @var array 错误数据
     */
    protected $errorRecord = [];

    /**
     * init
     */
    public function init()
    {
        $this->defaultLang = app()->params['en_lang'];
        parent::init();
    }

    /**
     * 生成缓存文件
     * @return array
     */
    public function build()
    {
        $langConf = app()->params['lang'];
        $data = $this->convertData();
        if (!empty($data)) {
            foreach ($data as $lang => $list) {
                $this->createPHPLangContent($langConf[$lang]['code'], $list);
            }
        }

        return app()->helper->arrayResult($this->codeSuccess, '文件生成成功');
    }

    /**
     * createPHPLangContent
     * @param string $lang 语言代码简称
     * @param array $list 语言包数组
     * @return bool
     */
    private function createPHPLangContent($lang, $list)
    {
        $contentFile = [];
        //php公共文件
        $phpContentCommon = '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL;
        //php按活动划分的文件
        $phpContentActivity = '<?php' . PHP_EOL . PHP_EOL . 'return array_merge(' . PHP_EOL
            . '    require(app()->basePath . "/runtime/languages/common/' . $lang . '/yii.php"),' . PHP_EOL . '    [';

        //将common内容放在第一位
        $contentFile[] = [
            static::FIELD_CONTENT => $phpContentCommon,
            'dir' => $this->getPathByLang($lang)
        ];
        foreach ((array)$list as $item) {
            //拼接PHP文件内容
            $phpString = '    "' .
                addslashes($item[static::FIELD_ALIAS] ?: $item[static::FIELD_KEY_NAME]) .
                '" => "' .
                addslashes($item[static::FIELD_CONTENT]) .
                '",' .
                PHP_EOL;
            if ($item[static::FIELD_ACTIVITY_ID]) {
                $contentFile[$item[static::FIELD_ACTIVITY_ID]]['dir'] = $this->getPathByLang(
                    $lang,
                    $item[static::FIELD_ACTIVITY_ID]
                );
                $contentFile[$item[static::FIELD_ACTIVITY_ID]][static::FIELD_CONTENT] = (
                    isset($contentFile[$item[static::FIELD_ACTIVITY_ID]][static::FIELD_CONTENT])
                        ? $contentFile[$item[static::FIELD_ACTIVITY_ID]][static::FIELD_CONTENT] : $phpContentActivity
                    ) . $phpString;
            } else {
                $contentFile[0][static::FIELD_CONTENT] .= $phpString;
            }
        }

        foreach ($contentFile as $key => $file) {
            $file[static::FIELD_CONTENT] .= !$key ? '];' : '    ]' . PHP_EOL . ');';
            file_put_contents($file['dir'] . 'yii.php', $file[static::FIELD_CONTENT]);
        }

        return true;
    }

    /**
     * 获取所有语言包数据，并转换结构
     * @param string $langParam 语言代码简称
     * @param bool $contentFilledByKey content字段为空时是否需要用key_name填充
     * @param int $activityId 活动ID
     * @return array
     */
    public function convertData($langParam = null, $contentFilledByKey = true, $activityId = 0)
    {
        $langArr = ($langParam === null) ? array_keys(app()->params['lang']) : [$langParam];
        $total = [];
        foreach ($langArr as $lang) {
            $query = LanguageModel::find()->alias('l');
            if ($lang !== $this->defaultLang) {
                $query = $query->joinWith(['contents lt' => function ($q) use ($lang) {
                    $q->onCondition(['lt.lang' => $lang]);
                }]);
            }

            if ($activityId) {
                //$activityId传值时查询公共的和活动ID下的
                $query = $query->andWhere(['l.activity_id' => [0, $activityId]]);
            } else {
                //$activityId不传值时则只查公共的
                $query = $query->andWhere(['l.activity_id' => 0]);
            }

            $data = $query->asArray()->all();
            $total[$lang] = $this->convertToTransData($data, $lang, $contentFilledByKey);
        }

        return $total;
    }

    /**
     * convertToTransData
     * @param array $data 语言包列表数组
     * @param string $lang 语言代码简称
     * @param bool $contentFilledByKey content字段为空时是否需要用key_name填充
     * @return array
     */
    private function convertToTransData($data, $lang, $contentFilledByKey)
    {
        $transData = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                $tempContent = ($lang !== $this->defaultLang && !empty($item['contents']))
                    ? $item['contents'][0][static::FIELD_CONTENT] : $item[static::FIELD_KEY_NAME];
                $tempContent =
                    !$contentFilledByKey &&
                    empty($item['contents']) &&
                    $lang !== $this->defaultLang ? '' : $tempContent;
                $transData[] = [
                    static::FIELD_ACTIVITY_ID => $item[static::FIELD_ACTIVITY_ID],
                    static::FIELD_ALIAS => $item[static::FIELD_ALIAS],
                    static::FIELD_KEY_NAME => $item[static::FIELD_KEY_NAME],
                    static::FIELD_CONTENT => trim($tempContent),
                    static::FIELD_IS_JS => $item[static::FIELD_IS_JS],
                    static::FIELD_REMARK => $item[static::FIELD_REMARK],
                ];
            }
        }
        return $transData;
    }

    /**
     * 获取语言包缓存文件保存路径（公共文件夹和按活动划分的文件夹）
     * @param string $langCode 语言编码
     * @param int $activityId 活动ID
     * @return string
     */
    private function getPathByLang($langCode, $activityId = 0)
    {
        $path = app()->basePath . DIRECTORY_SEPARATOR
            . 'runtime' . DIRECTORY_SEPARATOR
            . 'languages' . DIRECTORY_SEPARATOR
            . (!$activityId ? 'common' : $activityId) . DIRECTORY_SEPARATOR
            . $langCode . DIRECTORY_SEPARATOR;
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            return '';
        }

        return $path;
    }

    /**
     * 语言包导入
     * @param array $file
     * @return array
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function import($file)
    {
        set_time_limit(0);
        if (empty($file['tmp_name'])) {
            return app()->helper->arrayResult(1, '获取文件失败');
        }
        $response = $this->validFile($file);
        if ($response['code']) {
            return $response;
        }

        $data = $this->getFileContent($file['tmp_name']);
        $processedData = $this->processImportData($data);
        if (!empty($this->errorRecord)) {
            return app()->helper->arrayResult(1, '导入失败', $this->errorRecord);
        }

        if (!$this->saveImportData($processedData)) {
            return app()->helper->arrayResult(1, '导入失败', $this->errorRecord);
        }

        return app()->helper->arrayResult(0, '导入成功');
    }

    /**
     * 文件导出
     * @param int $activityId 活动ID
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export($activityId = 0)
    {
        ini_set('display_errors', 1);            //错误信息
        $langList = app()->params['lang'];
        //设置文件基本信息
        require_once app()->basePath . '/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator('globalegrow')
            ->setLastModifiedBy('globalegrow')
            ->setTitle('language_export')
            ->setSubject('language_export')
            ->setDescription('language_export')
            ->setKeywords('excel')
            ->setCategory('language_file');
        $is_js = [
            0 => 'N',
            1 => 'Y',
        ];
        $totalContent = $this->convertData(null, false, $activityId);
        if (!empty($totalContent)) {
            $index = 0;
            foreach ($totalContent as $lang => $list) {
                //设置每个sheet页的头
                $tempSheet = $objPHPExcel->createSheet($index);
                $tempSheet->setCellValue('A1', '编号')
                    ->setCellValue('B1', '键别名')
                    ->setCellValue('C1', '英语')
                    ->setCellValue('D1', '前端显示语言')
                    ->setCellValue('E1', '是否为js语言')
                    ->setCellValue('F1', '活动ID')
                    ->setCellValue('G1', '备注');
                $tempSheet->setTitle($langList[$lang]['name']);
                foreach ((array)$list as $number => $item) {
                    $tempRowNumber = $number + 2;
                    $tempSheet->setCellValue('A' . $tempRowNumber, $tempRowNumber - 1)
                        ->setCellValue('B' . $tempRowNumber, $item[static::FIELD_ALIAS])
                        ->setCellValue('C' . $tempRowNumber, $item[static::FIELD_KEY_NAME])
                        ->setCellValue('D' . $tempRowNumber, $item[static::FIELD_CONTENT])
                        ->setCellValue('E' . $tempRowNumber, $is_js[$item[static::FIELD_IS_JS]] ?: 'N')
                        ->setCellValue('F' . $tempRowNumber, $item[static::FIELD_ACTIVITY_ID])
                        ->setCellValue('G' . $tempRowNumber, $item[static::FIELD_REMARK]);
                }
                $index++;
            }
        }
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="多语言包' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * 验证文件
     * @param array $file
     * @return array
     */
    private function validFile($file)
    {
        if (!is_file($file['tmp_name'])) {
            return app()->helper->arrayResult(1, '无效的文件');
        }
        $fileExtensions = explode('.', $file['name']);
        $len = count($fileExtensions);
        if (!in_array($fileExtensions[$len - 1], ['xlsx', 'xls'])) {
            return app()->helper->arrayResult(2, '只支持【.xls】和【xlsx】格式');
        }

        return app()->helper->arrayResult(0, 'ok');
    }

    /**
     * 获取文件信息
     * @param string $tempFile excel文件
     * @return array|bool
     * @throws \PHPExcel_Reader_Exception
     */
    private function getFileContent($tempFile)
    {
        require_once app()->basePath . '/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
        try {
            ob_end_clean();
            $reader = new \PHPExcel_Reader_Excel2007();
            $phpExcel = $reader->load($tempFile);
        } catch (\Exception $e) {
            $reader = new \PHPExcel_Reader_Excel5();
            $phpExcel = $reader->load($tempFile);
        }
        // 取出所有数据
        $allSheet = $phpExcel->getAllSheets();

        // var $total 所有语言下 所有信息
        $total = [];

        // 所有key集合
        foreach ($allSheet as $sheet) {
            $langItemArr = $this->getDataBySheet($sheet);
            // 每个子分页
            if ($langItemArr) {
                list($lang, $langItem) = $langItemArr;
                $total[$lang] = $langItem;
            }
        }
        return $total;
    }

    /**
     * 获取单个sheet数据
     * @param \PHPExcel_Worksheet $sheet
     * @return array|null
     */
    private function getDataBySheet(\PHPExcel_Worksheet $sheet)
    {
        // 每页标题 作为语言
        $lang = trim($sheet->getTitle());
        $langList = app()->params['lang'];
        $allLang = array_combine(array_keys($langList), array_column($langList, 'name'));
        // 除去网站没有开启的语言
        if (!$lang || !in_array($lang, $allLang, true)) {
            return null;
        }
        $langCode = array_search($lang, $allLang, true);
        $total = [];
        $rows = $sheet->getHighestRow();
        for ($row = 2; $row <= $rows; $row++) {
            $rowData = [
                static::FIELD_ALIAS => trim($sheet->getCellByColumnAndRow(1, $row)->getValue()),
                static::FIELD_KEY_NAME => trim($sheet->getCellByColumnAndRow(2, $row)->getValue()),
                static::FIELD_CONTENT => $sheet->getCellByColumnAndRow(3, $row)->getValue(),
                static::FIELD_IS_JS => $sheet->getCellByColumnAndRow(4, $row)->getValue(),
                static::FIELD_ACTIVITY_ID => (int)$sheet->getCellByColumnAndRow(5, $row)->getValue(),
                static::FIELD_REMARK => trim($sheet->getCellByColumnAndRow(6, $row)->getValue())
            ];
            if (empty($rowData[static::FIELD_KEY_NAME])
                || empty($rowData[static::FIELD_ALIAS])
                || ($langCode !== $this->defaultLang && empty($rowData[static::FIELD_CONTENT]))
            ) {
                continue;
            }

            if ($rowData) {
                $total[] = $rowData;
            }
        }
        if (count($total)) {
            return [$langCode, $total];
        }

        return null;
    }

    /**
     * 处理导入数据
     * @param array $dataList
     * @return array
     */
    private function processImportData($dataList)
    {
        $tempAliasName = [];
        foreach ($dataList as $lang => $data) {
            foreach ((array)$data as $value) {
                $key = $value[static::FIELD_ACTIVITY_ID] . '_' . $value[static::FIELD_ALIAS];
                if (!isset($tempAliasName[$key])) {
                    $value[static::FIELD_CONTENT] = ($lang !== $this->defaultLang)
                        ? [$lang => $value[static::FIELD_CONTENT]] : [];
                    $tempAliasName[$key] = $value;
                } elseif ($lang !== $this->defaultLang) {
                    $tempAliasName[$key][static::FIELD_CONTENT][$lang] = $value[static::FIELD_CONTENT];
                }
            }
        }

        return $tempAliasName;
    }

    /**
     * 保存导入数据
     * @param  array $data
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    private function saveImportData($data)
    {
        $errorCount = 0;
        foreach ($data as $value) {
            //保存原文数据
            $languageId = $this->saveImportLanguage($value);
            if (!$languageId) {
                $errorCount++;
                continue;
            }

            //保存多语言数据
            if ($value[static::FIELD_CONTENT]
                && !$this->saveImportLanguageTrans($value[static::FIELD_CONTENT], $languageId)) {
                $errorCount++;
            }
        }

        return !$errorCount;
    }

    /**
     * 将导入数据保存到原始语言表
     * @param array $data
     * @param bool $runValidation
     * @return int
     */
    private function saveImportLanguage($data, $runValidation = true)
    {
        //保存原文数据
        $isJs = $data[static::FIELD_IS_JS] === 'Y' ? 1 : 0;
        $activityId = !empty($data[static::FIELD_ACTIVITY_ID]) ? $data[static::FIELD_ACTIVITY_ID] : 0;
        $langModel = LanguageModel::findOne([
            static::FIELD_ALIAS => $data[static::FIELD_ALIAS],
            static::FIELD_ACTIVITY_ID => $activityId
        ]);
        if (!$langModel) {
            $langModel = new LanguageModel();
        }
        $langModel->is_js = $isJs;
        $langModel->alias = $data[static::FIELD_ALIAS];
        $langModel->key_name = $data[static::FIELD_KEY_NAME];
        $langModel->activity_id = $activityId;
        $langModel->remark = !empty($data[static::FIELD_REMARK]) ? $data[static::FIELD_REMARK] : '';

        if (false === $langModel->save($runValidation)) {
            $this->errorRecord[] = ArrayHelper::toArray($langModel);
            \Yii::error('原语言保存失败', __METHOD__);
            return 0;
        }

        return $langModel->id;
    }

    /**
     * 将导入数据保存到多语言表
     * @param array $data
     * @param int $languageId
     * @param bool $runValidation
     * @return bool
     */
    private function saveImportLanguageTrans($data, $languageId, $runValidation = true)
    {
        $errorCount = 0;
        foreach ($data as $lang => $content) {
            $langTransModel = LanguageTransModel::findOne(['language_id' => $languageId, 'lang' => $lang]);
            if (!$langTransModel) {
                $langTransModel = new LanguageTransModel();
            }
            $langTransModel->language_id = $languageId;
            $langTransModel->lang = $lang;
            $langTransModel->content = $content;

            if (false === $langTransModel->save($runValidation)) {
                $this->errorRecord[] = ArrayHelper::toArray($langTransModel);
                \Yii::error('翻译内容保存失败', __METHOD__);
                $errorCount++;
            }
        }

        return !$errorCount;
    }

    /**
     * 从组件中提取待翻译的语言包
     * @param string $componentKey 组件key
     * @return array
     * @throws \Exception
     */
    public function extractComponent($componentKey)
    {
        $explainComponent = new ExplainComponent();
        if (false === ($componentConfig = $explainComponent->getStaticConfig($componentKey))) {
            return app()->helper->arrayResult(1, '未查找到component配置');
        }

        $file = $componentConfig['css'] ?: $componentConfig['js'];
        if (!is_file($file)) {
            return app()->helper->arrayResult(1, '组件文件缺失');
        }

        $dir = \dirname($file);
        $errorKey = [];

        //先获取组件下的所有twig文件
        if (!empty($fileList = $this->getFileList($dir))) {
            foreach ($fileList as $filePath) {
                if (!empty($errorOne = $this->matchComponentFile($filePath))) {
                    $errorKey[] = $errorOne;
                }
            }
        }

        if (!empty($errorKey)) {
            \Yii::error('组件语言包提取失败：' . json_encode($errorKey), __METHOD__);
            return app()->helper->arrayResult(1, 'fail', ['error_key' => $errorKey]);
        }

        return app()->helper->arrayResult(0, 'success');
    }

    /**
     * 获取所有文件列表(返回的文件地址都是绝对路径)
     * @param string $dir 文件夹路径
     * @return array
     */
    private function getFileList($dir)
    {
        $files = array();
        if (is_dir($dir) && ($handle = opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..'
                    && pathinfo($file, PATHINFO_EXTENSION) === $this->defaultTplSuffix) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                        $list = $this->getFileList($dir . DIRECTORY_SEPARATOR . $file);
                        array_push($files, ...$list);
                    } else {
                        $files[] = $dir . DIRECTORY_SEPARATOR . $file;
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * 正则提取文件待翻译文本
     * @param string $file 文件路径
     * @return string
     */
    private function matchComponentFile($file)
    {
        $errorKey = '';
        $content = file_get_contents($file);
        $matches = [];
        if (preg_match_all(
            "/{{[\s]*t[\s]*\([\s]*[\'|\"]yii[\'|\"],[\s]*[\'|\"](.*)[\'|\"][\s]*\)[\s]*}}/",
            $content,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $val) {
                $data = [
                    static::FIELD_IS_JS => 0,
                    static::FIELD_ALIAS => $val[1],
                    static::FIELD_KEY_NAME => $val[1],
                    static::FIELD_REMARK => $this->defaultExtractRemark
                ];
                if (!$this->saveImportLanguage($data)) {
                    //将保存失败的key记录下来
                    $errorKey = $val[1];
                }
            }
        }

        return $errorKey;
    }
}
