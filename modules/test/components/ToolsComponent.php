<?php
namespace app\modules\test\components;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

/**
 * 工具组件组件
 */
class ToolsComponent extends Component
{
    /**
     * 导出站点的接口配置
     *
     * @param array $siteCode
     */
    public function exportSiteAsyncApi($params)
    {
        $siteCode = $params['site_code'] ?? 'zf-pc';
        $apiConfigs = $this->getInterfaceConfig($siteCode);

        $apiRows = [];
        foreach ($apiConfigs as $key => $apiInfo) {
            unset($apiInfo['example']);
            $apiRows[] = array_merge(['key' => $key], $apiInfo);
        }

        $cellTitleMapping = [
            'key'   => '键名',
            'description' => '描述',
            'url'  => '接口地址',
            'method'  => '请求方式',
            'isJsonp'   => '是否支持jsonp',
        ];

        app()->response->isSent = true;
        $this->exportExcelFile('站点异步接口', $cellTitleMapping, $apiRows, array_keys($cellTitleMapping));
    }

    private function exportExcelFile($title, $cellTitleMapping, $dataList, $orderedCellNames = null)
    {
        $cellName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS',
            'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
        ];

        //表格对象
        $objExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objExcel->setActiveSheetIndex(0);
        $activeSheet = $objExcel->getActiveSheet();
        $activeSheet->setTitle($title);

        if (!empty($dataList)) {
            $lastCellName = $cellName[count($dataList[0]) - 1];
            $activeSheet->getStyle('A1:' . $lastCellName . '1')->getFont()->setBold(true);

            //设置表头
            empty($orderedCellNames) && $orderedCellNames = array_keys($dataList[0]);
            foreach ($orderedCellNames as $key => $cellTitle) {
                $activeSheet->setCellValue($cellName[$key] . '1', ($cellTitleMapping[$cellTitle] ?? $cellTitle));
            }

            //写入数据
            $dataStartRow = 2;
            foreach ($dataList as $row => $dataInfo) {
                $curRow = $dataStartRow + $row;
                foreach ($orderedCellNames as $index => $_cellName) {
                    $value = $dataInfo[$_cellName] ?? '';
                    $activeSheet->setCellValue($cellName[$index] . $curRow, $value);
                }
            }
        }

        //输出
        header("Pragma: public");
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        header('Content-Type:application/vnd.ms-execl');
        header("Content-Disposition: attachment;filename={$title}.xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter->save('php://output');
    }


    /**
     * 获取站点的接口配置
     * @param $siteCode
     * @return array
     */
    protected function getInterfaceConfig($siteCode)
    {
        $file = app()->basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sites'
            . DIRECTORY_SEPARATOR . 'interface' . DIRECTORY_SEPARATOR . 'interface.' . YII_ENV . '.php';
        $interface = require($file);
        $allConf = $interface[$siteCode] ?? [];
        $conf = [];
        if (!empty($allConf)) {
            foreach ($allConf as $key => $val) {
                if (!empty($val['isJsonp'])) {
                    // 正式环境只需要展示url一个值就行，其他诸如method、description、example都无需展示
                    $conf[$key] = app()->env->isProduct() ? [
                        'url' => $val['url']
                    ] : $val;
                }
            }
        }

        return $conf;
    }

    /**
     * 导出组件模板里config.json文件的多语言配置
     */
    public function exportUiTplLanguages()
    {
        $uiConfigLanguage = new \app\modules\test\components\tools\UiConfigLanguage();
        $uiConfigLanguage->exportUiTplLanguages();
    }

    public function getUiLanguages()
    {
        $rootPath = \Yii::getAlias('@app/files/parts/ui');
        $languages = [];
        $this->findUiLanguages($rootPath, $languages);

        app()->response->isSent = true;
        $this->exportExcel($languages);
    }

    private function exportExcel($dataList)
    {
        $cellName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS',
            'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
        ];
        $cellTitleMapping = [
            'ui_name'   => '组件目录名称',
            'tpl_name'  => '模板目录名称',
            'lang_key'  => '键名称',
            'lang-tw'   => '繁体',
            'lang-en'   => '英文',
            'lang-ja'   => '日语'
        ];
        $title = '组件语言包';

        //表格对象
        $objExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objExcel->setActiveSheetIndex(0);
        $activeSheet = $objExcel->getActiveSheet();
        $activeSheet->setTitle($title);

        if (!empty($dataList)) {
            $lastCellName = $cellName[count($dataList[0]) - 1];
            $activeSheet->getStyle('A1:' . $lastCellName . '1')->getFont()->setBold(true);

            //设置表头
            $orderedCellNames = array_keys($dataList[0]);
            foreach ($orderedCellNames as $key => $cellTitle) {
                $activeSheet->setCellValue($cellName[$key] . '1', ($cellTitleMapping[$cellTitle] ?? $cellTitle));
            }

            //写入数据
            $dataStartRow = 2;
            foreach ($dataList as $row => $dataInfo) {
                $curRow = $dataStartRow + $row;
                foreach ($orderedCellNames as $index => $_cellName) {
                    $value = $dataInfo[$_cellName] ?? '';
                    $activeSheet->setCellValue($cellName[$index] . $curRow, $value);
                }
            }
        }

        //输出
        header("Pragma: public");
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        header('Content-Type:application/vnd.ms-execl');
        header("Content-Disposition: attachment;filename={$title}.xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter->save('php://output');
    }

    protected function findUiLanguages($dir, &$languages)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $files = scandir($dir);
        if (false === $files) {
            throw new \Exception('Unable to open directory: ' . $dir);
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if ('config.json' === $file) {
                $tplName = basename($dir);
                $uiName = basename(dirname($dir));
                $jsonBody = file_get_contents($path);
                if (!empty($jsonBody)) {
                    $jsonData = json_decode($jsonBody, true);
                    if (isset($jsonData['languages'])) {
                        foreach ($jsonData['languages'] as $langKey => $langInfo) {
                            $languages[] = [
                                'ui_name'  => $uiName,
                                'tpl_name' => $tplName,
                                'lang_key' => $langKey,
                                'lang-tw'  => $langInfo['zh-tw'] ?? '',
                                'lang-en'  => $langInfo['en'] ?? '',
                                'lang-ja'  => $langInfo['ja'] ?? ''
                            ];
                        }
                    }
                }
            }

            if (is_dir($path)) {
                $this->findUiLanguages($path, $languages);
            }
        }
    }

    public function langRo()
    {
        $langFile = Yii::getAlias('@runtime/ro.txt');
        $langBody = file_get_contents($langFile);
        $langLines = explode("\n", $langBody);
        $roLanguages = [];
        foreach($langLines as $line) {
            $line = trim($line);
            list($key, $lang) = explode(',', $line, 2);
            $roLanguages[$key] = rtrim($lang, ',');
        }

        app()->response->isSent = true;
        $phpRoLanguages = [];
        $enLanguages = require(Yii::getAlias('@app/files/languages/common/en.php'));
        foreach($enLanguages['tpl'] as $key => $enText) {
            $phpRoLanguages['tpl'][$key] = $roLanguages[$key] ?? '';
        }

        foreach($enLanguages['js'] as $key => $enText) {
            $phpRoLanguages['js'][$key] = $roLanguages[$key] ?? '';
        }

        $phpCode = VarDumper::export($phpRoLanguages);
        $phpCode = sprintf("<?php\nreturn %s;", $phpCode);
        file_put_contents(Yii::getAlias('@app/files/languages/common/ro.php'), $phpCode);
    }

    public function site404()
    {
        $body = '';
        $config = json_decode(file_get_contents(\Yii::getAlias('@runtime/config_pro.php')), true);
        foreach ($config['params']['sites']['dl-']['headFooterMonitorDomain']['activity'] as $item) {
            $body .= str_replace('geshop-activity.html', '404.html', $item) ."\n";
        }
        echo $body;
    }


    public function goodsOrder()
    {
        $jsonFile = \Yii::getAlias('@runtime/goods.json');
        $json = file_get_contents($jsonFile);
        $result = json_decode($json, true);

        $skuList = "401801801,401809401,445499302,443788901,440460602,439724801,446689304,440908502,440910007,342979001,277915001,438140301,440723202,440464002,436750306,443791711,417785301,440915301,418981202,440466001,288786601,401809302,436702201,440465302";
        $goodsInfo = $result['data']['goodsInfo'];
        $skuList2 = join(',', array_column($goodsInfo, 'goods_sn'));


        if (!empty($goodsInfo)) {
            usort($goodsInfo, function($goodsInfo1, $goodsInfo2){
                $stock1 = isset($goodsInfo1['goods_number']) ? (int)$goodsInfo1['goods_number'] : 0;
                $stock2 = isset($goodsInfo2['goods_number']) ? (int)$goodsInfo2['goods_number'] : 0;
                if (($stock1 > 0 && $stock2 > 0) || ($stock1 === $stock2)) {
                    return 0;
                }
                return ($stock1 > 0 ) ? -1 : 1;
            });
        }

        echo  "{$skuList} <br/> $skuList2";
    }

    public function cleanRequestLog()
    {
        $c = new \app\modules\base\components\AccessLogComponent();
        $c->cleanExpiredLog();
        return app()->helper->arrayResult(0, '清除完成');
    }

    public function delTwigCache()
    {
        $cachePath = \yii::getAlias('@app/runtime/Twig/cache');
        FileHelper::removeDirectory($cachePath);
        return app()->helper->arrayResult(0, '清除完成');
    }

    public function gbTrans()
    {
        app()->response->isSent = true;

        $langIndex = [
            'buy_now', 'sold_out', 'starts_in', 'ends_in', 'coming_soon', 'total', 'view_more', 'view_less',
            'view_all', 'add_to_cart', 'you_save', 'final_price', 'deals_ended', 'more_stock_soon',
            'coupon', 'copy', 'limited_to_units', 'off_text', 'after_coupon', 'text_left', 'number_of_pcs_left',
            'discount', 'price', 'flash', 'oper_add', 'all_taken', 'have_expired'
        ];

        $phpBodyFormat = "<?php\r\n\r\nreturn %s";
        $rootPath = \yii::getAlias('@app/runtime/lang');
        $outPath = \yii::getAlias('@app/files/languages/gb');
        if ($dh = opendir($rootPath)) {
            while (($lang = readdir($dh)) !== false) {
                if ($lang == '.' || $lang == '..') continue;

                $srcLangPath = $rootPath .'/'. $lang;
                if ($srcObj = opendir($srcLangPath)) {
                    $allLangMessage = $srcLangMessage = [];
                    while (($srcFile = readdir($srcObj)) !== false) {
                        if ($srcFile == '.' || $srcFile == '..') continue;

                        $srcLangFile = $srcLangPath .'/'. $srcFile;
                        $_message = require($srcLangFile);
                        $allLangMessage = ArrayHelper::merge($allLangMessage, $_message);
                    }
                    closedir($srcObj);

                    foreach ($langIndex as $_index) {
                        if (!isset($allLangMessage[$_index]))
                            continue;

                        $_targetIndex = $_index;
                        if ('off_text' == $_index) {
                            $_targetIndex = 'off';
                        } elseif ('text_left' == $_index) {
                            $_targetIndex = 'left';
                        } elseif ('flash' == $_index) {
                            $_targetIndex = 'flash_sale';
                        } elseif ('oper_add' == $_index) {
                            $_targetIndex = 'add_to_cart';
                        }


                        $targetMsg = $allLangMessage[$_index];
                        $targetMsg = str_replace(':#$1#', '%s', $targetMsg);
                        $targetMsg = str_replace('>', '', $targetMsg);
                        $targetMsg = str_replace(':', '', $targetMsg);


                        $srcLangMessage[$_targetIndex] = $targetMsg;
                    }

                    $varBody = var_export($srcLangMessage, true);
                    $varBody = str_replace('array (', '[', $varBody);
                    $varBody = str_replace(')', '];', $varBody);
                    $phpBody = sprintf($phpBodyFormat, $varBody);

                    $outPhpFile = $outPath .'/'. $lang .'/component.php';
                    file_put_contents($outPhpFile, $phpBody);
                }
            }

            closedir($dh);
        }
    }
}
