<?php

namespace app\modules\activity\zf\components;

use app\modules\common\zf\components\CommonConvertWapComponent;
use app\modules\common\zf\models\PageModel;
use app\modules\common\zf\models\PageLayoutModel;
use app\base\PipelineUtils;
use app\base\SitePlatform;
use ego\base\JsonResponseException;

/**
 * 一键PC转Wap
 * @package app\modules\activity\components
 */
class ConvertWapComponent extends CommonConvertWapComponent
{
    /**
     * 检查渠道下页面是否装修,size大于0
     * @param array $params
     * @return array
     * @throws JsonResponseException
     */
    public function checkPipelinePagesDesign($params) {
        if (empty($params['activity_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的活动ID');
        }

        if (empty($params['group_id'])) {
            throw new JsonResponseException($this->codeFail, '无效的渠道组ID');
        }

        $pageRowList = PageModel::find()->where([
            'is_delete'     => PageModel::NOT_DELETE,
            'activity_id'   => $params['activity_id'],
            'group_id'      => $params['group_id'],
        ])->asArray()->indexBy('id')->all();
        if (empty($pageRowList)) {
            throw new JsonResponseException($this->codeFail, '没有有效的活动页面');
        }

        $pageIds = array_keys($pageRowList);
        $pageLayoutRowList = PageLayoutModel::find()
            ->select('page_id, lang, count(lang) as size')
            ->where(['page_id' => $pageIds])
            ->groupBy('page_id, lang')
            ->asArray()
            ->all();

        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        $configAllLangList = app()->params['lang'];
        $configAllPipelineList = PipelineUtils::getConfigAllPipelineListByGroupCode($siteGroupCode);

        $langList = [];
        foreach ($pageLayoutRowList as $pageLayoutRow) {
            if ($pageLayoutRow['size'] < 1)
                continue;

            $pageId = $pageLayoutRow['page_id'];
            $langCode = $pageLayoutRow['lang'];
            $langList[$pageId][$langCode] = [
                'code' => $langCode,
                'name' => $configAllLangList[ $langCode ]['name'] ?? '',
                'size' => $pageLayoutRow['size']
            ];
        }

        $pipelineList = [];
        foreach ($pageRowList as $pageRow) {
            $pageId = $pageRow['id'];
            $pipelineCode = $pageRow['pipeline'];

            if (!isset($langList[$pageId]))
                continue;

            $pipelineList[$pipelineCode] = [
                'code' => $pipelineCode,
                'name' => $configAllPipelineList[$pipelineCode] ?? '',
                'lang_list' => $langList[$pageId]
            ];
        }

        return app()->helper->arrayResult($this->codeSuccess, 'success', $pipelineList);
    }
}
