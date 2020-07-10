<?php

namespace app\modules\test\controllers;

use app\base\SitePlatform;
use app\modules\component\models\ComponentSiteRelationModel;
use app\modules\component\models\LayoutModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use yii\db\Exception;

/**
 * mysql数据修复控制器
 * @property \app\modules\test\components\RepairToolsComponent $RepairToolsComponent
 */
class MysqlController extends Controller
{

    public function actionSql()
    {
        $num = $this->RepairToolsComponent->pageSiteCode();
        return app()->helper->arrayResult(0, '清理成功', ['num' => $num]);
    }

    /**
     * 清理mysql数据表schema缓存
     * @return array
     */
    public function actionClearSchema()
    {
        app()->db->schema->refresh();
        return app()->helper->arrayResult(0, '清理成功');
    }

    /**
     * 修复组件和模板的站点对应关系数据
     */
    public function actionComponentSite()
    {
        $errors = [];
        $success = 0;
        $sites = ['test', 'rw', 'rg', 'zf'];
        $this->initLayout($sites, $errors, $success);
        $this->initUi($sites, $errors, $success);
        $this->initUiTpl($sites, $errors, $success);

        return app()->helper->arrayResult(0, '操作成功', [
            'success' => $success,
            'errors' => $errors,
            'tips' => '方法可重复调用，记录存在会更新，不存在则插入'
        ]);
    }

    /**
     * 初始化layout
     * @param array $sites
     * @param array $errors
     * @param int $success
     */
    private function initLayout(array $sites, array &$errors, int &$success)
    {
        $list = LayoutModel::find()->select('id, range')->asArray()->all();
        if (!empty($list)) {
            $result = [];
            foreach ($list as $item) {
                foreach ($sites as $site) {
                    $item['range'] = (int)$item['range'];
                    if ($item['range'] === LayoutModel::RANGE_PC || $item['range'] === LayoutModel::RANGE_RESPONSIVE) {
                        $result[] = [
                            'component_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_PC
                        ];
                    }
                    if ($item['range'] === LayoutModel::RANGE_WAP || $item['range'] === LayoutModel::RANGE_RESPONSIVE) {
                        $result[] = [
                            'component_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_WAP
                        ];
                        $result[] = [
                            'component_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_APP
                        ];
                    }
                }
            }
            if (!empty($result)) {
                foreach ($result as $res) {
                    $sql = /** @lang mysql */
                        'replace into component_site_relation(type, component_id, site_code) '
                        . 'values(' . ComponentSiteRelationModel::TYPE_LAYOUT . ', ' . $res['component_id']
                        . ', "' . $res['site_code'] . '");';
                    try {
                        app()->db->createCommand($sql)->execute();
                        $success++;
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }
    }

    /**
     * 初始化UI
     * @param array $sites
     * @param array $errors
     * @param int $success
     */
    private function initUi(array $sites, array &$errors, int &$success)
    {
        $list = UiModel::find()->select('id, range')->asArray()->all();
        if (!empty($list)) {
            $result = [];
            foreach ($list as $item) {
                foreach ($sites as $site) {
                    $item['range'] = (int)$item['range'];
                    if ($item['range'] === UiModel::RANGE_PC || $item['range'] === UiModel::RANGE_RESPONSIVE) {
                        $result[] = [
                            'component_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_PC
                        ];
                    }
                    if ($item['range'] === UiModel::RANGE_WAP || $item['range'] === UiModel::RANGE_RESPONSIVE) {
                        $result[] = [
                            'component_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_WAP
                        ];
                        $result[] = [
                            'component_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_APP
                        ];
                    }
                }
            }
            if (!empty($result)) {
                foreach ($result as $res) {
                    $sql = /** @lang mysql */
                        'replace into component_site_relation(type, component_id, site_code) '
                        . 'values(' . ComponentSiteRelationModel::TYPE_UI . ', ' . $res['component_id']
                        . ', "' . $res['site_code'] . '");';
                    try {
                        app()->db->createCommand($sql)->execute();
                        $success++;
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }
    }

    /**
     * 初始化UI模板
     * @param array $sites
     * @param array $errors
     * @param int $success
     */
    private function initUiTpl(array $sites, array &$errors, int &$success)
    {
        $list = UiTplModel::find()->alias('ut')
            ->select('ut.id, u.range')
            ->leftJoin(UiModel::tableName() . ' as u', 'ut.component_key = u.component_key')
            ->asArray()->all();
        if (!empty($list)) {
            $result = [];
            foreach ($list as $item) {
                foreach ($sites as $site) {
                    $item['range'] = (int)$item['range'];
                    if ($item['range'] === UiModel::RANGE_PC || $item['range'] === UiModel::RANGE_RESPONSIVE) {
                        $result[] = [
                            'tpl_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_PC
                        ];
                    }
                    if ($item['range'] === UiModel::RANGE_WAP || $item['range'] === UiModel::RANGE_RESPONSIVE) {
                        $result[] = [
                            'tpl_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_WAP
                        ];
                        $result[] = [
                            'tpl_id' => $item['id'],
                            'site_code' => $site . SitePlatform::SITE_CODE_SEPARATOR . SitePlatform::PLATFORM_CODE_APP
                        ];
                    }
                }
            }
            if (!empty($result)) {
                foreach ($result as $res) {
                    $sql = /** @lang mysql */
                        'replace into component_tpl_site_relation(type, tpl_id, site_code) '
                        . 'values(' . ComponentSiteRelationModel::TYPE_UI . ', ' . $res['tpl_id']
                        . ', "' . $res['site_code'] . '");';
                    try {
                        app()->db->createCommand($sql)->execute();
                        $success++;
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }
    }
}
