<?php

namespace app\modules\gbad\components;

use app\base\SiteConstants;
use app\base\SitePlatform;
use app\modules\common\components\CommonActivityComponent;
use app\modules\common\models\ActivityModel;
use ego\base\JsonResponseException;
use yii\helpers\Url;

/**
 * 自定义活动组件
 */
class ActivityComponent extends CommonActivityComponent
{
    /**
     * @inheritdoc
     * 默认活动类型为GB站广告推广页
     */
    public function lists($params, $activityType=SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT)
    {
        return parent::lists($params, $activityType);
    }

    /**
     * @inheritdoc
     */
    protected function buildListData($params, &$data)
    {
        list($pages, $siteConf, $activityGroupMap) = $params;

        foreach ($data as $i => $item) {
            //获取语言
            $data[ $i ]['langList'] = ActivityModel::getLangListByLangString($item['lang']);
            $lang = current($data[ $i ]['langList'])['key'];

            //分组信息
            $activityGroupInfo = $activityGroupMap[$item['group_id']] ?? [
                    'platform_list' => SitePlatform::getPlatformCodeBySiteCode($item['site_code']),
                    'lang_list' => $item['lang']
                ];

            $platformList = explode(SiteConstants::CHAR_COMMA, $activityGroupInfo['platform_list']);
            foreach ($platformList as $platformCode) {
                $data[ $i ]['group_info']['platform_list'][] = [
                    'code' => $platformCode,
                    'name' => SitePlatform::getPlatformNameByCode($platformCode)
                ];
            }

            $data[ $i ]['group_info']['lang_list'] = ActivityModel::getLangListByLangString($activityGroupInfo['lang_list']);

            if (!empty($pages[ $item['id'] ]['page_url'])) {
                $domain = $siteConf['ad_secondary_domain'][ $lang ];
                $data[ $i ]['preview'] = $domain . $pages[ $item['id'] ]['page_url'];
                $data[ $i ]['qrcode'] = Url::to([
                    sprintf('/%s/qr-code/create', SiteConstants::MODULE_NAME_GB_ADVERTISEMENT),
                    'url' => $data[ $i ]['preview']
                ], true);
            }
        }
    }

    /**
     * @inheritdoc
     * 默认活动类型为GB站广告推广页
     */
    public function groupAdd($params, $activityType=SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT)
    {
        return parent::groupAdd($params, $activityType);
    }

    /**
     * @inheritdoc
     */
    protected function getPageValidLanguageKeys($siteCode, $postLangKeys)
    {
        if (!$siteCode || !is_array($postLangKeys) || empty($postLangKeys)) {
            return [];
        }

        $supportLanguageKeys = $this->getPageSupportLanguages($siteCode);
        return SitePlatform::getValidLanguageKeys($supportLanguageKeys, $postLangKeys);
    }

    /**
     * @inheritdoc
     */
    protected function getPageSupportLanguages($siteCode) {
        return SitePlatform::getSiteAdvertisingPageSupportLanguageKeys($siteCode);
    }

    /**
     * 活动权限加/解锁
     *
     * @param $id
     *
     * @return array
     * @throws JsonResponseException
     */
    public function lock($id)
    {
        $model = ActivityModel::getById($id);
        //检查活动是否加锁，并判断权限
        if (false === ActivityModel::checkAuth($model)) {
            throw new JsonResponseException($this->codeFail, '只有活动创建者才具有此权限');
        }

        if (!$model) {
            throw new JsonResponseException($this->codeFail, '自定义活动不存在');
        }

        return $this->doLock($model);
    }


}