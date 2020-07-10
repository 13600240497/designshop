<?php
namespace app\common\dal\model\zf;

use yii\db\Exception;

/**
 * PagePublishLog模型
 *
 * @property int $id
 * @property string $version
 * @property int $page_id
 * @property string $lang
 * @property string $html
 * @property string $js
 * @property string $css
 * @property int $status
 * @property string $create_user
 * @property int $create_time
 * @property string $update_user
 * @property int $update_time
 */
class PagePublishCacheModel extends AbstractZaFulModel
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
    public static function getCurrentUsedCache(int $pageId, string $lang)
    {
        return self::find()->where([
            'page_id' => $pageId,
            'lang' => $lang,
            'status' => self::STATUS_USED
        ])->orderBy('id DESC')->one();
    }

    /**
     * 保存页面内容缓存
     * @param array $data
     * @return bool|string
     * @throws Exception
     * @throws \Throwable
     */
    public static function savePageContentCache(array $data)
    {
        $publishCacheModel = new PagePublishCacheModel();
        $data['status'] = self::STATUS_USED;
        $publishCacheModel->load($data, '');

        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            //先设置其他缓存为禁用状态
            self::updateAll([
                'status' => self::STATUS_NOT_USED
            ], [
                'page_id' => $data['page_id'],
                'lang' => $data['lang'],
                'status' => self::STATUS_USED
            ]);

            //添加新的启用缓存
            if (!$publishCacheModel->insert(true)) {
                throw new Exception('页面内容缓存记录失败：' . $publishCacheModel->flattenErrors(', '));
            }

            $tr->commit();
            return true;
        } catch (\Exception $e) {
            $tr->rollBack();
            return $e->getMessage();
        }
    }
}
