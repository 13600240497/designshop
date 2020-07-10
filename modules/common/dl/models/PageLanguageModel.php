<?php

namespace app\modules\common\dl\models;

use yii\db\Expression;

/**
 * PageLanguage模型
 *
 * @property int    $id
 * @property int    $page_id
 * @property string $lang
 * @property string $title
 * @property string $seo_title
 * @property string $keywords
 * @property string $description
 * @property int    $tpl_id
 * @property string $background_color
 * @property string $background_image
 * @property string $background_position
 * @property string $background_repeat
 * @property int    $style_type
 * @property string $multi_time_style
 * @property string $url_name
 * @property string $page_url
 * @property string $end_url
 * @property string $redirect_url
 * @property string $statistics_code
 * @property string $custom_css
 * @property string $local_files
 * @property string $s3_files
 */
class PageLanguageModel extends AbstractBaseModel
{
    /** @var int 页面样式 - 系统设置 */
    const STYLE_TYPE_SYSTEM = 1;
    /** @var int 页面样式 - 自定义 */
    const STYLE_TYPE_CUSTOM = 2;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'title';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id', 'lang', 'title'], 'required'],
            ['style_type', 'default', 'value' => static::STYLE_TYPE_SYSTEM],
            [
                [
                    'page_url',
                    'end_url',
                    'url_name',
                    'redirect_url',
                    'seo_title',
                    'keywords',
                    'description',
                    'tpl_id',
                    'background_color',
                    'background_image',
                    'background_position',
                    'background_repeat',
                    'multi_time_style',
                    'statistics_code',
                    'custom_css',
                    'local_files',
                    's3_files',
                    'goods_sn',
                    'share_title',
                    'share_image',
                    'share_desc',
                    'share_link',
                    'share_place'
                ], 'default', 'value' => ''
            ],
            ['seo_title', 'string', 'length' => [1, 200]],
            ['page_id', 'validateKey'],
            ['title', 'string', 'length' => [1, 100]],
            ['keywords', 'string', 'max' => 200],
        ];
    }

    /**
     * 验证主键
     *
     * @return bool
     */
    public function validateKey()
    {
        if (empty($this->page_id) || empty($this->lang)) {
            $this->addError('page_id', 'page_id&&lang不能为空');

            return false;
        }

        if (($item = static::findOne([
                'page_id' => $this->page_id,
                'lang'    => $this->lang
            ]))
            && (!$this->id || (int) $item->id !== (int) $this->id)
        ) {
            $this->addError('page_id', 'page_id&&lang主键冲突');

            return false;
        }

        return true;
    }

    /**
     * 获取活动下有page_url的页面列表
     *
     * @param array $activityIds 活动ID数组
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageUrlList($activityIds)
    {
        return static::find()->alias('l')
            ->select('l.page_url, p.activity_id')
            ->leftJoin(
                PageModel::tableName() . ' as p',
                'p.id = l.page_id'
            )->where([
                'p.is_delete'   => PageModel::NOT_DELETE,
                'p.activity_id' => $activityIds,
                'p.status'      => PageModel::PAGE_STATUS_HAS_ONLINE
            ])->andWhere(['<>', 'l.page_url', ''])
            ->groupBy('p.activity_id')
            ->orderBy('l.id ASC')
            ->asArray()
            ->all();
    }

    /**
     * 取上线页面的多语言地址列表
     *
     * @param $pageId
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageUrlListForId(array $params)
    {
        $data = [];
        if (!empty($params['pageId']) && is_array($params['pageId'])) {
            $pageId = implode(',', $params['pageId']);
            $query = static::find()->alias('l')
                ->select('l.page_url as url_title, p.site_code, l.lang, p.id as page_id')
                ->leftJoin(
                    PageModel::tableName() . ' as p',
                    'p.id = l.page_id'
                )
                ->where([
                    'p.is_delete' => PageModel::NOT_DELETE,
                    'p.status'    => PageModel::PAGE_STATUS_HAS_ONLINE
                ])
                ->andWhere("page_id IN({$pageId})")
                ->orderBy(new Expression("FIND_IN_SET(page_id, '{$pageId}')"))
                ->asArray()
                ->all();
            if (!empty($query) && is_array($query)) {
                foreach ($query as &$value) {
                    $site = mb_substr($value['site_code'], 0, strpos($value['site_code'], '-'));
                    if ($site == $params['siteCode']) {
                        $platform = mb_substr($value['site_code'], strpos($value['site_code'], '-') + 1, strlen($value['site_code']));
                        $lang = $value['lang'];
                        $value['url_title'] = trim($value['url_title'], '/');
                        $value['url_title'] = mb_substr($value['url_title'], 0, strpos($value['url_title'], '.'));
                        unset($value['site_code'], $value['lang']);
                        $data[ $platform ][ $lang ][] = $value;
                    }
                }
                unset($query);
            }
        }

        return $data;
    }

    /**
     * 获取当前页面配置所属页面
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(PageModel::class, ['id' => 'page_id']);
    }

    /**
     * 获取活动下有page_url的页面列表
     *
     * @param array $activityIds 活动ID数组
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getThemePageUrlList($activityIds)
    {
        return static::find()->alias('l')
            ->select('l.page_url, l.page_id,l.lang')
            ->leftJoin(
                PageModel::tableName() . ' as p',
                'p.id = l.page_id'
            )->where([
                'p.is_delete'   => PageModel::NOT_DELETE,
                'p.activity_id' => $activityIds,
            ])
            ->groupBy('l.page_id,l.lang')
            ->orderBy('l.id ASC')
            ->asArray()
            ->all();
    }

    /**
     * 获取分享信息
     * @param int $pageId
     * @param stirng $lang
     * @return array
     */
    public static function getShareData($pageId, $lang)
    {
        $pageLangInfo = static::find()
            ->select('share_image, share_title, share_desc, share_link, share_place')
            ->where(['page_id' => $pageId, 'lang' => $lang])
            ->asArray()
            ->one();

        $place = [];
        if (!empty($pageLangInfo['share_place'])) {
            $place = json_decode($pageLangInfo['share_place'], true);
            $place = array_filter($place);
        }

        $shareInfo = [
            'place' => $place,
            'image'  => $pageLangInfo['share_image'] ?? '',
            'title'  => $pageLangInfo['share_title'] ?? '',
            'desc'  => $pageLangInfo['share_desc'] ?? '',
            'link'  => $pageLangInfo['share_link'] ?? '',
        ];

        return $shareInfo;
    }
    
    public static function getAllLangPageForPageId(int $pageId)
    {
        return self::find()->where(['page_id' => $pageId])->count();
    }
}
