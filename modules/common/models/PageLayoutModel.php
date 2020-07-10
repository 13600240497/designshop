<?php

namespace app\modules\common\models;

use app\models\ActiveRecord;

/**
 * PageLayoutComponent模型
 *
 * @property int $id
 * @property int $page_id
 * @property int $lang
 * @property string $component_key
 * @property int $next_id
 * @property int $activity_id
 * @property array $logConfig
 */
class PageLayoutModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'page_id';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page_layout_component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id', 'next_id'], 'integer'],
            [['page_id', 'component_key', 'lang', 'next_id'], 'required'],
            ['next_id', 'validateKey'],
        ];
    }

    /**
     * 将data字段加入到attributes，方便数据库查询
     */
    public function attributes()
    {
        //其他表字段
        $otherAttributes = [
            'data',
            'custom_css',
            'background_color',
            'background_img',
            'style_data',
            'site_code',
            'activity_id'
        ];
        return array_merge(parent::attributes(), $otherAttributes);
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        if (($item = static::findOne([
                'page_id' => $this->page_id,
                'next_id' => $this->next_id,
                'lang'    => $this->lang
            ]))
            && (!$this->id || (int)$item->id !== (int)$this->id)
        ) {
            $this->addError('next_id', 'page_id&&next_id主键冲突');
            return false;
        }

        return true;
    }

    /**
     * 根据主键id获取活动ID
     * @param int $id 主键ID
     * @return int
     */
    public static function getActivityIdById($id)
    {
        /** @var \app\modules\common\models\ActivityModel $res */
        $res = static::find()->alias('l')
            ->select('a.id')
            ->leftJoin(
                PageModel::tableName() . ' as p',
                'p.id = l.page_id'
            )->leftJoin(
                ActivityModel::tableName() . ' as a',
                'a.id = p.activity_id'
            )
            ->where(['l.id' => $id])
            ->one();

        return $res ? $res->id : 0;
    }

    /**
     * 清空页面layout组件
     *
     * @param int $pageId 页面ID
     * @param string $lang 语言
     * @return bool|string
     */
    public static function deletePageLayouts($pageId, $lang = '')
    {
        $params = ['page_id' => $pageId];
        if ($lang) {
            $params['lang'] = $lang;
        }

        $layoutIds = self::find()->select('id')->where($params)->column();

        if (!empty($layoutIds)) {
            //删除layout_data
            if (true !== ($resDel = PageLayoutDataModel::deleteLayoutData($layoutIds))) {
                return $resDel;
            }
            //删除ui
            if (true !== ($resDel = PageUiModel::deleteUiByLayoutIds($layoutIds))) {
                return $resDel;
            }

            //删除layout
            if (false === self::deleteAll(['id' => $layoutIds])) {
                return self::tableName() . '表记录清理失败';
            }
        }

        return true;
    }

    /**
     * 通过pageId获取语言
     * @param $page_id
     * @return array
     * @author yuanwenguang 2019/4/26 8:59
     */
    public static function getLangListByPageId($page_id){
        $list = self::find()->where(['page_id'=>$page_id])->asArray()->all();
        $list = array_column($list,'lang');
        return empty($list) ? [] : $list;
    }
}
