<?php

namespace app\modules\admin\components;

use app\base\Pagination;
use app\base\SiteConstants;
use app\base\SitePlatform;
use app\base\PipelineUtils;
use app\base\SiteUtils;
use app\modules\admin\models\LanguageModel;
use app\modules\admin\models\LanguageTransModel;
use yii\helpers\ArrayHelper;
use yii\base\Exception;

/**
 * 多语言组件
 */
class LanguageComponent extends LanguageFileComponent
{
    /**
     * 多语言内容列表
     *
     * @return array
     */
    public function lists()
    {
        $lang = app()->request->get('lang', $this->defaultLang);

        $query = LanguageModel::find()->alias('l')->joinWith('activity a');
        if ($this->defaultLang !== $lang) {
            $query = $query->joinWith([
                'contents lt' => function ($q) use ($lang) {
                    $q->onCondition(['lt.lang' => $lang]);
                },
            ]);
        }

        if ($activityId = app()->request->get('activity_id')) {
            $query->andWhere(['l.activity_id' => $activityId]);
        }
        if ($keyName = app()->request->get('key_name')) {
            $query->andWhere(['like', 'l.key_name', $keyName]);
        }
        if ($content = app()->request->get('content')) {
            $query->andWhere(['like', 'lt.content', $content]);
        }

        $total = $query->count();
        $pagination = Pagination::new($total);
        $data = $query->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()
            ->all();

        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            [
                'list'       => $data,
                'pagination' => [
                    'pageNo'     => (int) $pagination->page + 1,
                    'pageSize'   => (int) $pagination->pageSize,
                    'totalCount' => (int) $total
                ],
            ]
        );
    }

    /**
     * 添加
     *
     * @param array $data
     * @param bool  $runValidation
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \Throwable
     */
    public function add(array $data, $runValidation = true)
    {
        unset($data['id']);
        $data = array_map('trim', $data);
        $model = new LanguageModel();
        $model->load($data, '');

        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            if (!$model->insert($runValidation)) {
                throw new Exception($model->flattenErrors(', '));
            }
            if (!empty($data['lang']) && !empty($data['content'])) {
                $transData = [
                    'language_id' => $model->id,
                    'lang'        => $data['lang'],
                    'content'     => $data['content'],
                ];
                $langTransModel = new LanguageTransModel();
                $langTransModel->load($transData, '');
                if (!$langTransModel->insert($runValidation)) {
                    throw new Exception($model->flattenErrors(', '));
                }
            }

            $tr->commit();

            return app()->helper->arrayResult(
                $this->codeSuccess,
                '添加成功',
                ['id' => $model->id]
            );
        } catch (\Exception $e) {
            $tr->rollBack();

            return app()->helper->arrayResult(
                $this->codeFail,
                $e->getMessage() ?: '添加失败'
            );
        }
    }

    /**
     * 编辑
     *
     * @param int   $id
     * @param array $data
     * @param bool  $runValidation
     *
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function edit($id, array $data, $runValidation = true)
    {
        unset($data['id']);
        $data = array_map('trim', $data);
        if (!$data) {
            return app()->helper->arrayResult($this->codeFail, '更新数据不能为空');
        }

        if (!$model = LanguageModel::getById($id)) {
            return app()->helper->arrayResult($this->codeFail, '记录不存在');
        }

        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            //更新译文记录
            $response = $this->saveContent($id, $data, $runValidation);
            if ($response['code']) {
                throw new Exception($response['message']);
            }
            //更新原文记录
            if (isset($data['is_js']) || isset($data['key_name']) || isset($data['alias'])
                || isset($data['remark']) || isset($data['activity_id'])
            ) {
                $model->load($data, '');
                if (false === $model->update($runValidation)) {
                    throw new Exception($model->flattenErrors(', '));
                }
            }

            $tr->commit();

            return app()->helper->arrayResult(
                $this->codeSuccess,
                '编辑成功'
            );

        } catch (\Exception $e) {
            $tr->rollBack();

            return app()->helper->arrayResult(
                $this->codeFail,
                $e->getMessage() ?: '编辑失败'
            );
        }
    }

    /**
     * 翻译文案编辑
     *
     * @param int   $id 原文记录ID
     * @param array $data
     *                  [
     *                  'content_id' => 译文记录ID,
     *                  'lang' => 语言代码简称,
     *                  'content' => 译文内容
     *                  ]
     * @param bool  $runValidation
     *
     * @return array
     */
    private function saveContent($id, $data, $runValidation = true)
    {
        if (empty($data['content_id'])) {//新增
            $model = new LanguageTransModel();
            $model->language_id = $id;
        } elseif (!$model = LanguageTransModel::findOne(['id' => $data['content_id'], 'language_id' => $id])) {
            return app()->helper->arrayResult($this->codeFail, '原译文ID记录不匹配');
        }
        $model->lang = $data['lang'];
        $model->content = $data['content'];
        if (false === $model->save($runValidation)) {
            return app()->helper->arrayResult(
                $this->codeFail,
                $this->msgFail
            );
        }

        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess
        );
    }

    /**
     * 删除
     *
     * @param int $id        原文记录ID
     * @param int $contentId 译文记录ID
     *
     * @return array
     * @throws \Throwable
     */
    public function delete($id, $contentId)
    {
        if (empty($id) && empty($contentId)) {
            return app()->helper->arrayResult($this->codeFail, 'id和content_id不能同时为空');
        }

        if (!empty($id)) {
            return $this->deleteLang($id);
        }

        return $this->deleteTrans($contentId);
    }

    /**
     * 删除原文
     *
     * @param int $id 原文记录ID
     *
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function deleteLang($id)
    {
        $langModel = LanguageModel::getById($id);
        if (!$langModel) {
            return app()->helper->arrayResult($this->codeSuccess, '删除成功');
        }

        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            //删除原文
            if (false === $langModel->delete()) {
                throw new Exception($langModel->flattenErrors(', '));
            }
            //删除译文
            if (false === LanguageTransModel::deleteAll(['language_id' => $id])) {
                throw new Exception('译文删除失败');
            }

            $tr->commit();

            return app()->helper->arrayResult(
                $this->codeSuccess,
                '删除成功'
            );
        } catch (\Exception $e) {
            $tr->rollBack();

            return app()->helper->arrayResult(
                $this->codeFail,
                $e->getMessage() ?: '删除失败'
            );
        }
    }

    /**
     * 删除译文
     *
     * @param int $contentId 译文记录ID
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteTrans($contentId)
    {
        $transModel = LanguageTransModel::getById($contentId);
        if (!$transModel && false === $transModel->delete()) {
            return app()->helper->arrayResult(
                $this->codeFail,
                $transModel->flattenErrors(', ') ?: '译文删除失败'
            );
        }

        return app()->helper->arrayResult(
            $this->codeSuccess,
            '译文删除成功'
        );
    }

    /**
     * 详情
     *
     * @param int $id
     * @param int $contentId
     *
     * @return array
     */
    public function info($id, $contentId)
    {
        if (empty($id) && empty($contentId)) {
            return app()->helper->arrayResult($this->codeFail, 'id和content_id不能同时为空');
        }

        $langModel = $transModel = null;
        if (!empty($contentId) && !$transModel = LanguageTransModel::getById($contentId)) {
            return app()->helper->arrayResult($this->codeFail, '译文ID查不到对应记录');
        }

        if (empty($contentId) && !empty($id) && !$langModel = LanguageModel::getById($id)) {
            return app()->helper->arrayResult($this->codeFail, '原文ID查不到对应记录');
        }

        if ($transModel) {
            $langModel = LanguageModel::getById($transModel->language_id);
            $data = ArrayHelper::toArray($langModel);
            $data['content_id'] = $transModel->id;
            $data['lang'] = $transModel->lang;
            $data['content'] = $transModel->content;
        } else {
            $data = ArrayHelper::toArray($langModel);
            $data['content_id'] = 0;
            $data['lang'] = $this->defaultLang;
            $data['content'] = $langModel->key_name;
        }

        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            $data
        );
    }

    /**
     * 获取js语言包内容
     *
     * @param array $lang       语言代码简称
     * @param int   $activityId 活动ID
     *
     * @return string
     */
    public function getJsLang($lang, $activityId = 0)
    {
        if (!$lang) {
            return app()->helper->arrayResult(1, '请传入指定的语言');
        }
        $data = $this->convertData($lang, true, $activityId);
        $return = [];
        foreach ((array) $data[ $lang ] as $item) {
            $return[ addslashes($item['alias'] ?: $item['key_name']) ] = addslashes($item['content']);
        }

        return app()->helper->arrayResult(
            0,
            'success',
            $return
        );
    }

    /**
     * 获取语言key列表(不同站点支持不同语言)
     *
     * @param array $params
     * @return array
     */
    public function getLangList($params)
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }
        $activityType = $params['activity_type'] ?? SiteConstants::ACTIVITY_TYPE_SPECIAL;

        $platformLangs = [];

        $supportPlatforms = NULL;
        if (isDresslilySite($siteGroupCode)) {
            $supportPlatforms = SitePlatform::getDLSupportPlatforms();
        } else {
            $supportPlatforms = SitePlatform::getAllSupportPlatforms();
        }

        foreach ($supportPlatforms as $platformCode) {
            $platformType = SitePlatform::getPlatformTypeByPlatformCode($platformCode);
            if (SitePlatform::PLATFORM_TYPE_UNKNOWN == $platformType) {
                continue;
            }

            $siteCode = SitePlatform::getSiteCodeByPlatformType($platformType);
            if (empty($siteCode)) {
                continue;
            }

            if ($activityType == SiteConstants::ACTIVITY_TYPE_GB_ADVERTISEMENT) {
                $langList = SitePlatform::getSiteAdvertisingPageSupportLanguages($siteCode);
            } else {
                $langList = SitePlatform::getSiteSpecialPageSupportLanguages($siteCode);
            }
            $platformLangs[$platformCode] = $langList;

        }

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $platformLangs);
    }


    /**
     * 获取国家(渠道)站点列表
     *
     * @param array $params Http GET参数
     * @return array
     */
    public function getCountrySiteList($params)
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }

        $pipelineLangList = [];
        $pageType = $params['activity_type'] ?? SiteConstants::ACTIVITY_TYPE_SPECIAL;
        if (SiteConstants::ACTIVITY_PAGE_TYPE_SPECIAL == $pageType) {
            $pipelineLangList = PipelineUtils::getSiteSpecialAllPlatformPipelineLangList($siteGroupCode, true);
        } else if (SiteConstants::ACTIVITY_PAGE_TYPE_HOME == $pageType) {
            $pipelineLangList = PipelineUtils::getSiteHomeAllPlatformPipelineLangList($siteGroupCode, true);
        }

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $pipelineLangList);
    }

    /**
     * 获取渠道下支持的语言
     *
     * @return array
     */
    public function getPipelineList()
    {
        $siteGroupCode = SitePlatform::getCurrentSiteGroupCode();
        if (empty($siteGroupCode)) {
            return app()->helper->arrayResult($this->codeFail, '找不到站点');
        }
        $list = SitePlatform::getSiteSpecialPageSupportPipeline();

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $list);
    }
}
