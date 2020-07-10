<?php

namespace app\modules\base\components;

use app\base\SiteConstants;
use app\modules\base\models\SubActivityDataModel;
use app\modules\base\models\IndexActivityDataModel;

/**
 * 统计数据管理组件
 *
 * @package app\modules\activity\components
 */
class BigDataSyncComponent extends Component
{

    /**
     * 从大数据中转库同步活动页销售展现数据统计数据
     *
     * @return array
     */
    public function syncMySqlActivitySaleData()
    {
        $maxLoopNum = 1000;  //循环次数
        $pageSize   = 100;  //每次循环获取记录条数

        $tableSyncInfo = [];
        $tableSyncInfo[] = $this->syncMySQLTableData('index_activity_data', new IndexActivityDataModel(), $maxLoopNum, $pageSize);
        $tableSyncInfo[] = $this->syncMySQLTableData('sub_activity_data', new SubActivityDataModel(), $maxLoopNum, $pageSize);
        return $tableSyncInfo;
    }

    /**
     * 同步活动页销售展现数据统计数据
     *
     * @param string $tableName
     * @param \app\models\ActiveRecord $dataModel
     * @param int $maxLoopNum 循环次数
     * @param int $pageSize 每次循环获取记录条数
     * @return array
     */
    public function syncMySQLTableData($tableName, $dataModel, $maxLoopNum, $pageSize)
    {
        $columnNames = $dataModel->getTableSchema()->columnNames;
        $lastRowId = $dataModel->find()->max('id'); //最后一行ID
        if (empty($lastRowId)) $lastRowId = 0;

        $offset = 0;
        $copyRowNum = 0;
        for ($i = 0; $i < $maxLoopNum; $i++) {
            $format = 'SELECT %s FROM %s WHERE id > %d LIMIT %d, %d';
            $columns = join(SiteConstants::CHAR_COMMA, $columnNames);
            $sql = sprintf($format, $columns, $tableName, $lastRowId, $offset, $pageSize);

            $rows = static::getBigDataTransferDb()->createCommand($sql)->queryAll();
            if (empty($rows)) break;

            $rowDataList = [];
            foreach ($rows as $row) {
                $rowDataList[] = array_values($row);
            }

            $command = $dataModel->getMasterDb()->createCommand();
            $command->batchInsert($dataModel->tableName(), $columnNames, $rowDataList)->execute();

            $copyRowNum += count($rows);
            $offset += $pageSize;
        }

        return [$dataModel->tableName(), $lastRowId, $copyRowNum];
    }

    /**
     * 获取大数据数据中转数据库连接
     */
    public static function getBigDataTransferDb()
    {
        return app()->get('bd_transfer');
    }
}