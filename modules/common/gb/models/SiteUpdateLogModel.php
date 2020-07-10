<?php

namespace app\modules\common\gb\models;


/**
 * 站点更新日志模型
 *
 * @property int $id
 * @property string $site_code
 * @property int $place
 * @property string $page_ids
 * @property int $parent_id
 * @property int $status
 * @property string $result
 * @property string $detail
 * @property int $complete_time
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 */
class SiteUpdateLogModel extends BaseModel
{
    /**
     * 状态|0-未开始
     */
    const STATUS_NOT_START = 0;

    /**
     * 状态|1-进行中
     */
    const STATUS_PROCESSING = 1;

    /**
     * 状态|2-已完成
     */
    const STATUS_COMPLETED = 2;

    /**
     * 页面刷新成功
     */
    const PAGE_SUCCESS = 1;

    /**
     * 页面刷新失败
     */
    const PAGE_FAILED = 2;

    /**
     * 应用场景|1-活动页
     */
    const PLACE_ACTIVITY = 1;

    /**
     * 应用场景|2-首页
     */
    const PLACE_HOME = 2;

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
            ['site_code', 'required'],
            [['page_ids', 'result', 'detail'], 'default', 'value' => ''],
            ['parent_id', 'default', 'value' => 0]
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
        ];
        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 更新父记录的日志
     * @param int $parentId 父记录ID
     * @param array $successPageIds 成功的页面ID数组
     * @return bool
     * @throws \Throwable
     * @throws \Exception
     */
    public static function updateParentLog($parentId, $successPageIds)
    {
        if (!empty($successPageIds) && $log = static::findOne($parentId)) {
            /** @var array $detail */
            $detail = json_decode($log->detail, true);
            if (!empty($detail) && !empty($detail[0])) {
                $successTime = time();
                foreach ($detail as $key => $item) {
                    if (\in_array($item['page_id'], $successPageIds, false)) {
                        $detail[$key]['status'] = static::PAGE_SUCCESS;
                        $detail[$key]['success_time'] = $successTime;
                    }
                }
            }
            $log->detail = json_encode($detail);
            $log->update(true);

            return true;
        }

        return true;
    }

    /**
     * 获取日志记录中的发布失败的页面ID
     *
     * @param int $logId
     *
     * @return array
     */
    public static function getFailedPageIds(int $logId)
    {
        $pageIds = [];
        if (!empty($logId) && $log = static::findOne($logId)) {
            $logDetail = json_decode($log->detail, true);
            if (!empty($logDetail)) {
                /** @var array $logDetail */
                foreach ($logDetail as $item) {
                    if (!isset($item['status']) || $item['status'] !== self::PAGE_SUCCESS) {
                        $pageIds[] = $item['page_id'];
                    }
                }
            }
        }

        return $pageIds;
    }

    public static function tableName()
    {
        return 'site_update_log';
    }
}
