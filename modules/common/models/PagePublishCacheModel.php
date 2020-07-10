<?php

namespace app\modules\common\models;

use app\models\ActiveRecord;
use yii\db\Exception;

/**
 * PagePublishLog模型
 *
 * @property int $id
 * @property string $version
 * @property int $page_id
 * @property string $lang
 * @property string $html
 * @property string $customJs
 * @property string $js
 * @property string $css
 * @property int $status
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 */
class PagePublishCacheModel extends ActiveRecord
{
    /**
     * 状态|0-未启用
     */
    const STATUS_NOT_USED = 0;

    /**
     * 状态|1-启用
     */
    const STATUS_USED = 1;

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
                'page_id',
                'lang',
                'status'
            ], 'required'],
            [['html', 'js', 'css','layout','uilist','customJs'], 'default', 'value' => ''],
            [['page_id', 'status'], 'integer']
        ];
    }

    /**
     * 获取页面正在使用的缓存
     * @param int $pageId
     * @param string $lang
     * @return array|null|PagePublishCacheModel
     */
    public static function getCurrentUsedCache(int $pageId, string $lang, string $fields = '*')
    {
        $pageModel = PageModel::getById($pageId);
        if (!$pageModel) {
            return [];
        }

        $cacheKey = app()->redisKey->getPagePublishCacheKey($pageModel->site_code);
        $field = "{$pageId}:{$lang}";
        if (empty(app()->redis->hexists($cacheKey, $field))) {
            $result = self::find()->select($fields)
                ->where([
                    'page_id' => $pageId,
                    'lang'    => $lang,
                    'status'  => self::STATUS_USED
                ])
                ->orderBy('id DESC')
                ->asArray()
                ->one();
            
            app()->redis->hset($cacheKey, $field, gzcompress(json_encode($result), 9));
        }
        
        $data = app()->redis->hget($cacheKey, $field);
        return !empty($data) ? json_decode(gzuncompress($data), true) : [];
    }
    
    /**
     * 保存页面内容缓存
     *
     * @param array $data
     *
     * @return bool|string
     * @throws Exception
     * @throws \Throwable
     */
    public static function savePageContentCache(array $data)
    {
        $pageModel = PageModel::getById($data['page_id']);
        $cacheKey = app()->redisKey->getPagePublishCacheKey($pageModel->site_code);
        $field = "{$pageModel->id}:{$data['lang']}";
        $return = app()->redis->hset($cacheKey, $field, gzcompress(json_encode($data)));
        
        return (1 == $return || 0 == $return) ? true : false;
    }
}
