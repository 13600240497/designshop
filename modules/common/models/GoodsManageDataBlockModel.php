<?php
namespace app\modules\common\models;

use app\modules\common\models\PageUiComponentDataModel;
use app\modules\common\models\PageUiModel;
use app\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * GoodsManageUiDataModel 模型
 *
 * @property int    $id
 * @property int    $gmp_id
 * @property string $lang
 * @property string $category_title
 * @property string $more_url
 * @property int    $sku_from
 * @property string $goods_sku
 * @property int    $sort_num
 * @property string $data_md5
 * @property int    $is_same
 * @property int    $component_id
 * @property string $create_user
 * @property int    $create_time
 * @property string $update_user
 * @property int    $update_time
 */
class GoodsManageDataBlockModel extends ActiveRecord
{

    /** @var string 商品标题组合组件pc版本key */
    const GOODS_TITLE_COMPOSITE_COMPONENT_KEY_PC = 'U000055';
    /** @var string 商品标题组合组件m和app版本key */
    const GOODS_TITLE_COMPOSITE_COMPONENT_KEY_MOBILE = 'U000056';

    /** @var string 商品标题组合组件字段*/
    const GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_TITLE_TEXT = 'titleText';
    const GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_VIEW_HREF = 'viewHref';
    const GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_GOODS_SKU = 'goodsSKU';

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
            [['gmp_id', 'lang', 'category_title', 'sku_from', 'goods_sku', 'sort_num', 'data_md5', 'is_same'], 'required'],
        ];
    }

    /**
     * 获取商品标题组合组件商品管理配置数据
     * @param int $componentId
     * @param string $lang
     * @return array
     */
    public static function getGoodsTitleCompositeComponentConfigData($componentId, $lang)
    {
        $goodsTitleCompositeComponentKeys = [
            static::GOODS_TITLE_COMPOSITE_COMPONENT_KEY_PC,
            static::GOODS_TITLE_COMPOSITE_COMPONENT_KEY_MOBILE
        ];

        $configData = [];
        /** @var \app\modules\common\models\PageUiModel $pageUiModel */
        $pageUiModel = PageUiModel::find()->where(['id' => $componentId, 'lang' => $lang])->one();
        if ($pageUiModel && in_array($pageUiModel->component_key, $goodsTitleCompositeComponentKeys)) {

            $fieldKeys = [
                static::GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_TITLE_TEXT,
                static::GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_VIEW_HREF,
                static::GOODS_TITLE_COMPOSITE_COMPONENT_FIELD_GOODS_SKU
            ];

            $uiDataList = PageUiComponentDataModel::find()->where([
                'component_id' => $componentId,
                'lang' => $lang,
                'is_public' => PageUiComponentDataModel::IS_PUBLIC,
                'key' => $fieldKeys
            ])->asArray()->all();

            if (!empty($uiDataList)) {
                foreach ($uiDataList as $uiData) {
                    $configData[$uiData['key']] = json_decode($uiData['value']);
                }
            }
        }

        return $configData;
    }
}