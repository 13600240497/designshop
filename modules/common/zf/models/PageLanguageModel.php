<?php

namespace app\modules\common\zf\models;

use app\base\SiteConstants;
use app\base\SitePlatform;
use yii\db\Expression;

/**
 * PageLanguage模型
 *
 * @property int    $id
 * @property string $group_id
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
            [['group_id', 'page_id', 'lang', 'title'], 'required'],
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
            ->select('l.page_url, p.activity_id, p.pipeline, l.lang')
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
     * @param $params
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageUrlListForId(array $params)
    {
        $data = [];
        if (empty($params['pageId']) || !is_array($params['pageId'])) {
            return $data;
        }

        $postSiteGroupCode = strtolower($params['siteCode']);
        $pageInfoList = PageModel::find()->select('group_id')->where([
            'id' => $params['pageId'],
            'is_delete' => PageModel::NOT_DELETE,
            'status'    => PageModel::PAGE_STATUS_HAS_ONLINE
        ])->asArray()->all();
        if (empty($pageInfoList)) {
            return $data;
        }

        //查询所有上线的页面
        $pageGroupIds = array_column($pageInfoList, 'group_id');
        $pageQuery = static::find()->alias('l')
            ->select('l.page_url as url_title, p.site_code, p.pipeline, l.lang, p.id as page_id, is_native')
            ->leftJoin(
                PageModel::tableName() . ' as p',
                'p.id = l.page_id'
            )
            ->where([
                'p.group_id' => $pageGroupIds,
                'p.is_delete' => PageModel::NOT_DELETE,
                'p.status'    => PageModel::PAGE_STATUS_HAS_ONLINE
            ]);
        if (!empty($params['pipelineCode'])) $pageQuery->andWhere(['p.pipeline' => $params['pipelineCode']]);

        if (!empty($params['platformCode'])) {
            $siteCodes = [];
            $platformCodes = is_array($params['platformCode']) ? $params['platformCode'] : [$params['platformCode']];
            foreach ($platformCodes as $_platformCode) {
                if (!in_array($_platformCode, SitePlatform::getAllSupportPlatforms(), true)) {
                    continue;
                }

                $siteCode = SitePlatform::getSiteCodeByPlatformCode($_platformCode, $postSiteGroupCode);
                $siteCodes[] = $siteCode;
            }

            if (!empty($siteCodes)) {
                $pageQuery->andWhere(['p.site_code' => $siteCodes]);
            }
        }

        $rows = $pageQuery->asArray()->all();
        if (empty($rows)) {
            return $data;
        }

        foreach ($rows as &$row) {
            list($siteGroupCode, $platformCode) = SitePlatform::splitSiteCode($row['site_code']);
            if (strcasecmp($siteGroupCode, $postSiteGroupCode) == 0) {
                $langCode = $row['lang'];
                $pipelineCode = $row['pipeline'];

                $row['url_title'] = trim($row['url_title'], '/');
                $row['url_title'] = mb_substr($row['url_title'], 0, strpos($row['url_title'], '.'));
                unset($row['site_code'], $row['pipeline'], $row['lang']);

                //兼容RG  D网暂无渠道的格式
                if (!empty($params['pipelineCode']))
                {
                    $data[ $platformCode ][ $pipelineCode ][ $langCode ][] = $row;
                }else {
                    $data[ $platformCode ][ $langCode ][] = $row;
                }
            }
        }

        return $data;
    }

    /**
     * 取上线页面的多语言地址列表
     *
     * @param $params
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageUrlForId(array $params)
    {
        $data = [];
        if (!empty($params['pageId']) && is_array($params['pageId'])) {
            $pageModel = new PageModel();
            $platformList = ['pc','wap','app'];
            foreach ($platformList as $key => $item) {
                if (!$params['pageId'][$key]){
                    unset($params['pageId'][$key]);//过滤ID为0参数
                    continue;
                }
                if (!$pageModel->getActiveByPageId($params['pageId'][$key], $params['siteCode'] . '-' . $item)) unset($params['pageId'][$key]);//过滤ID与平台不符的参数
            }
            if (empty($params['pageId'])) return $data;
            $pageId = implode(',', $params['pageId']);
            $pageInfoList = PageModel::find()
                ->where([
                'is_delete' => PageModel::NOT_DELETE,
                'status'    => PageModel::PAGE_STATUS_HAS_ONLINE
            ])->andWhere("id IN({$pageId})")->asArray()->all();
            if (empty($pageInfoList)) {
                return $data;
            }
            $pageGroupIds = '"' . implode('","', array_column($pageInfoList, 'group_id')) . '"';

            $query = static::find()->alias('l')
                ->select('l.page_url as url_title, p.site_code, l.lang, p.id as page_id, is_native')
                ->leftJoin(
                    PageModel::tableName() . ' as p',
                    'p.id = l.page_id'
                )
                ->where([
                    'p.is_delete' => PageModel::NOT_DELETE,
                    'p.status'    => PageModel::PAGE_STATUS_HAS_ONLINE
                ])
                ->andWhere("p.group_id IN ({$pageGroupIds})")
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

    public static function getPageLanguages(array $pageIds)
    {
        $result = self::find()->select('lang')->where(['in', 'page_id', $pageIds])->asArray()->all();

        return !empty($result) ? array_unique(array_column($result, 'lang')) : [];
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

    /**
     * 根据页面ID批量获取渠道和语言
     *
     * @param array $pageIds
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllPageLangList(array $pageIds)
    {
        return self::find()->alias('l')
            ->select('l.id, p.site_code, l.lang, p.pipeline')
            ->leftJoin(PageModel::tableName().' as p', 'p.id = l.page_id')
            ->where(['l.page_id' => $pageIds, 'p.is_delete' => 0])
            ->asArray()->all();
    }

    public static function getPageTitle(int $pageId)
    {
	    $result = PageLanguageModel::find()->select('title')->where(['page_id' => $pageId])->asArray()->one();

	    return !empty($result['title']) ? $result['title'] : '';
    }
}
