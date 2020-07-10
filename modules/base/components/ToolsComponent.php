<?php
namespace app\modules\base\components;

use app\modules\base\models\PageLanguageDataModel;
use app\modules\common\zf\components\CommonPageComponent;
use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\models\PageModel;
use ego\base\JsonResponseException;
use app\modules\base\components\tools\EdmDeepLinkComponent;

/**
 * 系统工具组件
 */
class ToolsComponent extends Component
{
    /**
     * EDM链接生成器UI数据
     *
     * @return array
     */
    public function getEdmDeepLinkUiData()
    {
        $uiData = EdmDeepLinkComponent::getUiData();
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $uiData);
    }

    /**
     * 生成 EDM链接
     * @param array $params Post参数
     * @return string
     * @throws JsonResponseException
     */
    public function generateEdmUrl($params)
    {
        if (!isset($params['website_code'], $params['activity_url'], $params['action_type'], $params['action_params'])) {
            throw new JsonResponseException($this->codeFail, '缺失参数');
        }

        $websiteCode = $params['website_code'];
        $activityUrl = $params['activity_url'];
        $actionType = $params['action_type'];
        $actionParams = json_decode($params['action_params'], true);
        try {
            $fullUrl = EdmDeepLinkComponent::generateUrl($websiteCode, $activityUrl, $actionType, $actionParams);
        } catch (\Exception $e) {
            throw new JsonResponseException($this->codeFail, $e->getMessage());
        }

        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, ['url' => $fullUrl]);
    }


    /**
     * 查询页面数量
     * @param array $param
     * @return array
     * @throws
     */
    public function getOnlinePageNumber(array $param)
    {
        $websiteCode = (defined('SITE_GROUP_CODE_FIXED') && !empty(SITE_GROUP_CODE_FIXED))
            ? SITE_GROUP_CODE_FIXED
            : SITE_GROUP_CODE;
        if(!in_array($websiteCode, ['zf','dl', 'rg', 'suk'])){
            $model = \app\commands\models\PageModel::find();
        }else{
            $model = PageModel::find();
        }
        $model->where(['status' => [PageModel::PAGE_STATUS_HAS_ONLINE, PageModel::PAGE_STATUS_HAS_RELEASE]]);
        if(!empty($param['page_create_time'])){
            $time = '';
            switch ($param['page_create_time']){
                case 1://一年
                    $time = strtotime('-1 year');
                    break;
                case 2://半年
                    $time = strtotime('-6 month');
                    break;
                case 3://三个月
                    $time = strtotime('-3 month');
                    break;
            }
            $model->andWhere(['<', 'update_time', $time]);
        }
        if(!empty($param['platform'])){//平台
            $platform = explode('-', $param['platform']);
            $platform = array_map(function ($item){
                return SITE_GROUP_CODE . '-' . $item;
            }, $platform);
            $model->andWhere(['site_code' => $platform]);
        }
        if(!empty($param['pipeline'])){//渠道
            $pipeline = explode('-', $param['pipeline']);
            $model->andWhere(['pipeline' => $pipeline]);
        }
        $total = $model->count();
        if(!empty($param['offline']) && (!empty($param['page_create_time']) || !empty($param['platform']) || !empty($param['pipeline']))){//处理下线
            $data = $model->select('id,pipeline,site_code')->asArray()->all();
            if(!empty($data)){
                \Yii::info('页面下线' . var_export($data, true), __METHOD__);
                PageModel::updateAll(['status' => PageModel::PAGE_STATUS_HAS_OFFLINE], ['id' => array_column($data, 'id')]);
            }
        }
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, ['page-num' => $total]);
    }
}
