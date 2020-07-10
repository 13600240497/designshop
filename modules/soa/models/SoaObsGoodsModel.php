<?php
namespace app\modules\soa\models;

use app\models\ActiveRecord;
use app\base\SitePlatform;

/**
 * SoaObsGoodsModel 模型
 *
 * @property int        $id
 * @property int 	    $theme_id  			//主题ID
 * @property int 	    $page_id   			//obs页面ID
 * @property int	    $section_id 		//版块ID
 * @property string     $lang	 			//语言
 * @property string     $pid     			//geshop 页面ID
 * @property int        $component_id  		//UI组件ID
 * @property string     $component_key 		//UI组件身份唯一编码
 * @property string     $goods_sku			//产品编码
 * @property int        $last_update_time   //最后更新时间
 */
class SoaObsGoodsModel extends ActiveRecord
{

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['goods_sku', 'default', 'value' => '']
        ];
    }

    /**
     * 获取已经关联的obs页面
     * @param   array      $params
     *          int      theme_id    主题ID
     *          int      pid         页面ID
     *          string   lang        语言
     * @return  array
     */
    public static function getPageList($params)
    {
        $theme_id = !empty($params['theme_id']) ?  intval($params['theme_id']) : '';
        $pid = !empty($params['page_id']) ?  intval($params['page_id']) : '';
        $platform = !empty($params['platform']) ?  trim($params['platform']) : 'pc';
        $platform = SitePlatform::getPlatformTypeByPlatformCode($platform);
        return static::find()->where(['theme_id'=>$theme_id,'platform'=>$platform])
        ->andWhere(['<>','pid',$pid])
        ->select('page_id')->asArray()->all();
    }

    /**
     * 获取已经关联的版块
     * @param   array $params
     *          int  page_id         页面ID
     *          int  component_id    组件ID
     *          var  platform        设备端
     * @return array
     */
    public static function getSectionList($params)
    {
        $list = [];
        $section_id = '';
        $page_id = !empty($params['page_id']) ?  intval($params['page_id']) : '';
        $component_id = !empty($params['uiId']) ?  intval($params['uiId']) : '';
        $platform = !empty($params['platform']) ?  trim($params['platform']) : 'pc';
        $platform = SitePlatform::getPlatformTypeByPlatformCode($platform);
        $data = static::find()->where(['page_id'=>$page_id,'platform'=>$platform])->andWhere(['<>','section_id',''])
        ->select('section_id,component_id')->asArray()->all();
        if(!empty($data)){
            foreach ($data as $key=>$value) {
                if($value['component_id'] == $component_id){
                    $section_id = $value['section_id'];
                }
                $list[$value['section_id']] = $value['section_id'];
            }
            unset($list[$section_id]);
        }
        return $list;
    }

    /**
     * 获取主题下绑定的全部活动
     * @param    int      $theme_id
     * @return  array
     */
    public static function getActivityByThemeId($theme_id)
    {
        return static::find()->where(['theme_id'=>$theme_id])->select('activity_id')->groupBy('activity_id')->asArray()->all();

    }

    /**
     * 格式化返回数据
     * @param    array    $data
     * ---       page_id
     * ---       lang
     * ---       page_url
     * @return array
     */
    public static function formatUrlData($data)
    {
        static $domain;
        $list = [];
        if(!empty($data)){
            $pageIds = array_column($data, 'page_id');

            $page = static::find()->where(['pid'=>$pageIds])
            ->andWhere(['<>','page_id',''])
            ->select('pid,page_id,lang,platform')->groupBy('pid,lang')->asArray()->all();
            $pages = [];
            if(!empty($page)){
                foreach ($page as  $value) {
                    $pages[$value['pid']][$value['lang']] = [
                        'page_id'=>$value['page_id'],
                        'platform'=>$value['platform'],
                    ];
                }
            }
            foreach($data as $row){
                if(!empty($pages[$row['page_id']][$row['lang']])){
                    $platform = strtolower(SitePlatform::getPlatformNameByType($pages[$row['page_id']][$row['lang']]['platform']));
					$platform = $platform == 'm' ? 'wap' : $platform;
                    $site = 'gb-'.$platform;
                    if(empty($domain[$site])){
                        $domain[$site] = app()->params['sites'][$site]['secondary_domain'];
                    }
                    $page_url = '';
                    if(!empty($row['page_url'])){
                        $page_url = $domain[$site][$row['lang']].str_replace('\\', "/", $row['page_url']);
                    }
                    $list[] = [
                        'page_url' => $page_url,
                        'page_id'  => $pages[$row['page_id']][$row['lang']]['page_id'],
                        'lang'     => $row['lang'],
                    ];
                }
            }
            unset($pages,$data,$page);
        }
        return $list;
    }
}
