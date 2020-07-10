<?php

namespace app\modules\common\gb\models;

use app\base\SitePlatform;
use yii\base\Exception;
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
 * @property string $background_attachment
 * @property string $goods_component_style
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
 * @property string $group_id
 */
class PageLanguageModel extends BaseModel
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
                    'background_attachment',
                    'multi_time_style',
                    'statistics_code',
                    'custom_css',
                    'local_files',
                    's3_files',
                    'pipeline',
                    'group_id',
                    'share',
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
            ->select('l.page_url, p.activity_id,l.pipeline,l.lang')
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
     *  获取页面端下的全部渠道
     *
     * @param    int $group_id
     *
     * @return array
     */
    public static function getPipelineList($group_id)
    {
        $pipelineList = [];
        $list = static:: find()->alias('l')
            ->select('l.lang,p.pipeline,p.site_code,l.page_id')
            ->leftJoin(
                PageModel::tableName() . ' as p',
                'p.id = l.page_id'
            )->where(['p.group_id' => $group_id])
            ->asArray()
            ->all();
        if (!empty($list)) {
            $siteCode = current($list)['site_code'];
            
            $site = SitePlatform::getSiteBySiteCode($siteCode);
            $configAllLanguages = app()->params['lang'] ?? [];
            $configAllPipeline = app()->params['soa'][ $site ]['pipeline'] ?? [];
            $data = [];
            foreach ($list as $row) {
                $data[ $row['pipeline'] ][] = $row;
            }
            
            foreach ($data as $pipeline => $val) {
                $langList = [];
                $language = [];
                $page_id = 0;
                foreach ($val as $value) {
                    $langList[ $value['lang'] ] = [
                        'key'  => $value['lang'],
                        'name' => $configAllLanguages[ $value['lang'] ]['name'] ?? '',
                    ];
                    $page_id = $value['page_id'];
                }
                foreach ($configAllLanguages as $lang => $item) {
                    if (isset($langList[ $lang ])) {
                        $language[ $lang ] = $langList[ $lang ];
                    }
                }
                $pipelineList[ $pipeline ] = [
                    'key'      => $pipeline,
                    'name'     => $configAllPipeline[ $pipeline ] ?? '',
                    'page_id'  => $page_id,
                    'langList' => $language,
                
                ];
            }
        }
        
        return app()->helper->arrayResult(0, '获取成功', $pipelineList);
    }
    
    /**
     * 取商品类组件样式数据
     *
     * @param int    $pageId
     * @param string $lang
     *
     * @return array|mixed
     */
    public static function getGoodsComponentStyle(int $pageId, string $lang)
    {
        $result = static::find()->select('goods_component_style')
            ->where(['page_id' => $pageId, 'lang' => $lang])
            ->asArray()
            ->one();
        
        return !empty($result['goods_component_style']) ? json_decode($result['goods_component_style'], true) : [];
    }
    
    /**
     * PC同步WAP商品类组件样式
     *
     * @param array $params
     *
     * @throws Exception
     */
    public static function converWapGoodsComponentStyle(array $params)
    {
        if (empty($params['source']) || empty($params['target'])) {
            throw new Exception("参数错误，同步商品类组件样式失败");
        }
        
        foreach ($params['source'] as $sourcePipe => $sourceItem) {
            $goodsComponentStyle = static::find()
                ->where(['page_id' => $sourceItem['id'], 'pipeline' => $sourcePipe, 'lang' => $sourceItem['lang']])
                ->one()
                ->getAttribute('goods_component_style');
            $goodsComponentStyle = !empty($goodsComponentStyle) ? json_decode($goodsComponentStyle, true) : [];
            $goodsComponentStyle['private'] = [];
            foreach ($params['target'] as $targetPipe => $targetItem) {
                if (!empty($targetItem['lang']) && is_array($targetItem['lang'])) {
                    foreach ($targetItem['lang'] as $lang) {
                        static::updateAll(
                            ['goods_component_style' => json_encode($goodsComponentStyle)],
                            ['page_id' => $targetItem['id'], 'pipeline' => $targetPipe, 'lang' => $lang]
                        );
                    }
                }
            }
        }
    }
    
    /**
     * WAP同步APP商品类组件样式
     *
     * @param array $params
     *
     * @throws Exception
     */
    public static function converAppGoodsComponentStyle(array $params)
    {
        if (empty($params['source']) || empty($params['target'])) {
            throw new Exception("参数错误，同步商品类组件样式失败");
        }
        
        foreach ($params['source'] as $sourcePipe => $sourceItem) {
            $goodsComponentStyle = static::find()
                ->where(['page_id' => $sourceItem['id'], 'pipeline' => $sourcePipe, 'lang' => $sourceItem['lang']])
                ->one()
                ->getAttribute('goods_component_style');
            $goodsComponentStyle = !empty($goodsComponentStyle) ? json_decode($goodsComponentStyle, true) : [];
            $goodsComponentStyle['private'] = [];
            foreach ($params['target'] as $targetPages) {
                foreach ($targetPages as $targetPipe => $targetItem) {
                    if (!empty($targetItem['lang']) && is_array($targetItem['lang'])) {
                        foreach ($targetItem['lang'] as $lang) {
                            static::updateAll(
                                ['goods_component_style' => json_encode($goodsComponentStyle)],
                                ['page_id' => $targetItem['id'], 'pipeline' => $targetPipe, 'lang' => $lang]
                            );
                        }
                    }
                }
            }
        }
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
        return self::find()->select('lang, pipeline')
            ->where(['page_id' => $pageIds])
            ->asArray()->all();
    }
}
