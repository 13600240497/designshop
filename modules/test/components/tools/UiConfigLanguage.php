<?php
namespace app\modules\test\components\tools;

use Yii;

class UiConfigLanguage
{

    public function exportUiTplLanguages()
    {
        $uiRoot = Yii::getAlias('@app/files/parts/ui');
        $languages = [];
        $this->findUiTplConfigLanguages($languages, $uiRoot);
        app()->response->isSent = true;
        if (!empty($languages)) {
            $this->exportExcel('ui-tpl-languages', $languages);
        }
    }

    private function exportExcel($title, $dataList)
    {
        $cellName = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X',
            'Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS',
            'AT','AU','AV','AW','AX','AY','AZ'
        ];
        $cellTitleMapping = [
            'ui'        => '组件',
            'tpl'       => '模板',
            'key'       => '键名称',
            'en'        => '英语(en)',
            'zh-tw'     => '繁体中文(zh-tw)',
            'ru'        => '俄语(ru)',
        ];

        //表格对象
        $objExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objExcel->setActiveSheetIndex(0);
        $activeSheet = $objExcel->getActiveSheet();
        $activeSheet->setTitle($title);

        if (!empty($dataList)) {
            $orderedCellNames = array_keys($dataList[0]);
            $lastCellName = $cellName[count($orderedCellNames) - 1];
            $activeSheet->getStyle('A1:' . $lastCellName . '1')->getFont()->setBold(true);

            //设置表头
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

    private function findUiTplConfigLanguages(&$languages, $path)
    {
        $files = scandir($path);
        if (false !== $files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if ('config.json' === $file) {
                    $configs = json_decode(file_get_contents($fullPath), true);
                    if (isset($configs['languages'])) {
                        $tplName = basename($path);
                        $uiName = basename(dirname($path));
                        foreach ($configs['languages'] as $langKey => $langInfo) {
                            $languages[] = [
                                'ui'    => $uiName,
                                'tpl'   => $tplName,
                                'key'   => $langKey,
                                'en'    => $langInfo['en'] ?? '',
                                'zh-tw'    => $langInfo['zh-tw'] ?? '',
                                'ru'    => $langInfo['ru'] ?? '',
                            ];
                        }
                    }
                } else {

                    if (is_dir($fullPath)) {
                        $this->findUiTplConfigLanguages($languages, $fullPath);
                    }
                }


            }
        }
    }
}