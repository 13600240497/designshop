<?php

namespace app\modules\api\controllers;

use app\modules\base\models\PageLanguageDataModel;
use app\modules\base\models\PageLanguagePackageModel;
use app\modules\component\models\CategoryModel;
use app\modules\component\models\ComponentSiteRelationModel;
use app\modules\component\models\ComponentTplSiteRelationModel;
use app\modules\component\models\LayoutModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\component\models\UiTplRelationModel;

/**
 * 组件相关对外API接口
 *
 * Class ComponentController
 *
 * @package app\modules\api\controllers
 */
class ComponentController extends Controller
{
    
    /**
     * 获取需要同步的组件的相关属性
     *
     * @param int $layoutId
     * @param int $uiId
     * @param int $tplId
     * @param int $categoryId
     *
     * @return array
     */
    public function actionNeedAdd(int $layoutId, int $uiId, int $tplId, int $categoryId)
    {
        $layoutList = LayoutModel::find()->andWhere(['>', 'id', $layoutId])->asArray()->all();// layout组件
        $uiList = UiModel::find()->andWhere(['>', 'id', $uiId])->asArray()->all();// ui组件
        $tplList = UiTplModel::find()->andWhere(['>', 'id', $tplId])->asArray()->all();// tpl模板
        $categoryList = CategoryModel::find()->andWhere(['>', 'id', $categoryId])->asArray()->all();// 组件分类
        
        $layoutIds = $layoutList ? array_column($layoutList, 'id') : [];
        $uiIds = $uiList ? array_column($uiList, 'id') : [];
        $tplIds = $tplList ? array_column($tplList, 'id') : [];
        $layoutSiteRelations = !$layoutIds ? [] : ComponentSiteRelationModel::find()->where([
            'type'         => ComponentSiteRelationModel::TYPE_LAYOUT,
            'component_id' => $layoutIds
        ])->asArray()->all();// layout和site关联关系
        $uiSiteRelations = !$uiIds ? [] : ComponentSiteRelationModel::find()->where([
            'type'         => ComponentSiteRelationModel::TYPE_UI,
            'component_id' => $uiIds
        ])->asArray()->all();// ui和site关联关系
        $tplSiteRelations = !$tplIds ? [] : ComponentTplSiteRelationModel::find()->where([
            'type'   => ComponentTplSiteRelationModel::TYPE_UI,
            'tpl_id' => $tplIds
        ])->asArray()->all();// tpl和site关联关系
        $tplRelations = !$tplIds ? [] : UiTplRelationModel::find()->where([
            'OR',
            ['tpl_id' => $tplIds],
            ['relation_tpl_id' => $tplIds]
        ])->asArray()->all();// tpl和tpl关联关系
        
        return app()->helper->arrayResult(0, 'success', [
            'layoutList'               => $layoutList,
            'uiList'                   => $uiList,
            'tplList'                  => $tplList,
            'categoryList'             => $categoryList,
            'componentSiteRelation'    => array_merge($layoutSiteRelations, $uiSiteRelations),
            'componentTplSiteRelation' => $tplSiteRelations,
            'tplRelations'             => $tplRelations
        ]);
    }
    
    /**
     * 获取需要同步的多语言包
     *
     * @param int $packageId
     * @param int $dataId
     *
     * @return array
     */
    public function actionLanguagePackageAdd(int $packageId, int $dataId)
    {
        $packageList = PageLanguagePackageModel::find()->where(['>', 'id', $packageId])->asArray()->all();
        $languageDataList = PageLanguageDataModel::find()->where(['>', 'id', $dataId])->asArray()->all();
        
        return app()->helper->arrayResult(
            0,
            'success',
            [
                'packageList'      => $packageList,
                'languageDataList' => $languageDataList
            ]
        );
    }
}
