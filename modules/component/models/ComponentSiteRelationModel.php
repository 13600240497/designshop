<?php

namespace app\modules\component\models;

use app\base\SitePlatform;

/**
 * 组件站点关联关系模型
 * @property int $id
 * @property int $type
 * @property int $component_id
 * @property string $site_code
 */
class ComponentSiteRelationModel extends ComponentModel
{
    /**
     * 组件类型|1-layout
     */
    const TYPE_LAYOUT = 1;

    /**
     * 组件类型|2-UI
     */
    const TYPE_UI = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'component_site_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'component_id', 'site_code'], 'required'],
            [['type', 'component_id'], 'integer'],
        ];
    }

    /**
     * 保存站点关系配置
     * @param int $componentId
     * @param int $type
     * @param int $range
     * @param string $siteGroups 只包含站点信息（eg:test、rw、rg、zf），不包含端的信息（pc、wap、app）
     * @return bool|string
     * @throws \yii\db\Exception
     */
    public static function saveSiteRelation($componentId, $type, $range, $siteGroups)
    {
        $siteCodes = self::getFullSiteCode(explode(',', $siteGroups), $range);
        if (empty($siteGroups) || empty($siteCodes)) {
            return '站点关系配置不能为空';
        }

        $tr = app()->db->beginTransaction();
        try {
            // 先删除旧的
            static::deleteAll([
                'component_id' => $componentId,
                'type' => $type
            ]);

            // 再添加新的
            $column = ['type', 'component_id', 'site_code'];
            $data = [];
            foreach ($siteCodes as $siteCode) {
                if (empty($siteCode) || !SitePlatform::siteExists($siteCode)) {
                    continue;
                }

                $data[] = [
                    (int)$type,
                    $componentId,
                    trim($siteCode)
                ];
            }
            if (!empty($data)) {
                static::insertAll($column, $data);
            }

            $tr->commit();
        } catch (\Exception $e) {
            $tr->rollBack();
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 获取完整的siteCode
     * @param array $siteArr
     * @param int $range
     * @return array
     */
    private static function getFullSiteCode(array $siteArr, int $range)
    {
        $result = [];
        if (!empty($siteArr)) {
            foreach ($siteArr as $site) {
                if ($range === UiModel::RANGE_PC) {
                    $result[] = SitePlatform::getWebPlatformSiteCode($site);
                    $result[] = SitePlatform::getPcPlatformSiteCode($site);
                } elseif (in_array($range, [UiModel::RANGE_WAP, UiModel::RANGE_NATIVE], true)) { // wap和app公用
                    $result[] = SitePlatform::getWapPlatformSiteCode($site);
                    $result[] = SitePlatform::getAppPlatformSiteCode($site);
                    $result[] = SitePlatform::getIosPlatformSiteCode($site);
                    $result[] = SitePlatform::getIpadPlatformSiteCode($site);
                    $result[] = SitePlatform::getAndroidPlatformSiteCode($site);
                } elseif ($range === UiModel::RANGE_RESPONSIVE) { // 响应式则添加所有端
                    $result[] = SitePlatform::getWebPlatformSiteCode($site);
                    $result[] = SitePlatform::getPcPlatformSiteCode($site);
                    $result[] = SitePlatform::getWapPlatformSiteCode($site);
                    $result[] = SitePlatform::getAppPlatformSiteCode($site);
                    $result[] = SitePlatform::getIosPlatformSiteCode($site);
                    $result[] = SitePlatform::getIpadPlatformSiteCode($site);
                    $result[] = SitePlatform::getAndroidPlatformSiteCode($site);
                }
            }
        }

        return $result;
    }
}
