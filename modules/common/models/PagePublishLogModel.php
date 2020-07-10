<?php

namespace app\modules\common\models;

use app\models\ActiveRecord;

/**
 * PagePublishLog模型
 *
 * @property int $id
 * @property string $version
 * @property int $log_type
 * @property int $page_id
 * @property string $lang
 * @property int $action_type
 * @property string $file_name
 * @property string $file_type
 * @property string $file_size
 * @property string $file_hash
 * @property string $local_path
 * @property string $s3_url
 * @property string $diff
 * @property int $ip
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 */
class PagePublishLogModel extends ActiveRecord
{
    /**
     * 日志类型|1-缓存文件生成日志
     */
    const LOG_TYPE_CREATE = 1;

    /**
     * 日志类型|2-发布S3日志
     */
    const LOG_TYPE_PUBLISH = 2;

    /**
     * 操作类型|1-上线
     */
    const ACTION_TYPE_ONLINE = 1;

    /**
     * 操作类型|2-下线
     */
    const ACTION_TYPE_OFFLINE = 2;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig = false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'version',
                'log_type',
                'page_id',
                'lang',
                'action_type',
                'file_name',
                'file_type',
                'file_size',
                'file_hash',
                'local_path'
            ], 'required'],
            [['page_id', 'ip'], 'integer'],
        ];
    }

    /**
     * 将新字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'create_name',
            'page_url',
            'langList',
            'title'
        ];

        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 根据文件hash和类型获取文件本地存储路径
     * @param string $hash 文件hash
     * @param string $type 文件后缀
     * @return string
     */
    public static function getRelativePath($hash, $type)
    {
        $item = static::findOne([
            'file_hash' => $hash,
            'file_type' => $type
        ]);

        return $item ? $item->local_path : '';
    }

    /**
     * 批量插入
     * @param array $list 数据
     * @return int
     * @throws \yii\db\Exception
     */
    public static function insertAllData(array $list)
    {
        if (empty($list)) {
            return 0;
        }

        $columns = array_keys($list[0]);
        $data = [];
        foreach ($list as $item) {
            $data[] = array_values($item);
        }

        return parent::insertAll($columns, $data);
    }

    /**
     * 根据记录ID查询下个记录
     * @param int $id 本表记录ID
     * @param array $params 记录参数
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getNextDiffLog($id, $params)
    {
        return static::find()->where([
                'log_type' => $params['log_type'],
                'page_id' => $params['page_id'],
                'lang' => $params['lang'],
                'file_type' => $params['file_type']
            ])
            ->andFilterWhere(['>', 'id', $id])
            ->orderBy('id ASC')
            ->one();
    }

    /**
     * 查询页面最新文件生成记录
     *
     * @param int $pageId
     * @param int $status
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageNewestPublishLog(int $pageId, int $status)
    {
        $actionType = ($status === PageModel::PAGE_STATUS_HAS_ONLINE || $status === PageModel::PAGE_STATUS_HAS_ONLINE_UPDATE)
            ? self::ACTION_TYPE_ONLINE : self::ACTION_TYPE_OFFLINE;
    
        $query = static::find()->alias('l')->where([
            'l.log_type' => self::LOG_TYPE_CREATE,
            'l.page_id' => $pageId,
            'l.action_type' => $actionType
        ]);

        $versions = [];
        // 兼容GB广告落地页面
        if (isGearbestSite()) {
            $version = $query->select('max(l.version) as version')
                ->groupBy('l.version')
                ->orderBy('l.version desc')
                ->asArray()
                ->one();
            $versions[] = $version['version'];
        } else {
            // 按照语言发布获取多个版本
            $rows = $query->select('max(l.version) as version, lang')
                ->groupBy('l.lang')
                ->orderBy('l.version asc')
                ->asArray()
                ->indexBy('lang')
                ->all();
            $versions = array_column($rows, 'version');
        }

        $data = [];
        if (!empty($versions)) {
            $data = $query->select('l.*, pl.page_url')
                ->leftJoin(PageLanguageModel::tableName() . ' as pl', 'l.page_id = pl.page_id AND l.lang = pl.lang')
                ->andWhere(['l.version' => $versions])
                ->groupBy('l.page_id, l.lang, l.file_type')
                ->asArray()
                ->all();
        }
    
        return $data;
    }

    /**
     * 查询页面最新文件生成记录
     *
     * @param int $pageId
     * @param int $status
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getHomebNewestPublishLog(int $pageId, int $status)
    {
        $actionType = $status === PageModel::PAGE_STATUS_HAS_ONLINE_B
            ? self::ACTION_TYPE_ONLINE : self::ACTION_TYPE_OFFLINE;

        $query = static::find()->alias('l')->where([
            'l.log_type' => self::LOG_TYPE_CREATE,
            'l.page_id' => $pageId,
            'l.action_type' => $actionType
        ]);
        $rows = $query->select('max(l.version) as version, lang')
            ->groupBy('l.lang')
            ->orderBy('l.version asc')
            ->asArray()
            ->indexBy('lang')
            ->all();
        $versions = array_column($rows, 'version');

        $data = [];
        if (!empty($versions)) {
            $data = $query->select('l.*, pl.page_url')
                ->leftJoin(PageLanguageModel::tableName() . ' as pl', 'l.page_id = pl.page_id AND l.lang = pl.lang')
                ->andWhere(['l.version' => $versions])
                ->groupBy('l.page_id, l.lang, l.file_type')
                ->asArray()
                ->all();
        }

        return $data;
    }

    /**
     * 查询页面最新推送记录
     *
     * @param int $pageId
     * @param array|string $version
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageNewestPushLog(int $pageId, $version)
    {
        return static::find()->where([
                'log_type' => self::LOG_TYPE_PUBLISH,
                'page_id' => $pageId,
                'version' => $version
            ])->asArray()->all();
    }
}
