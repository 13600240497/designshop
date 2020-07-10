<?php

namespace app\modules\activity\gb\controllers;

use app\modules\common\gb\models\PageLanguageModel;
use app\modules\common\gb\models\PageModel;
use yii\web\Response;

/**
 * 活动页面设计管理类
 *
 * @property \app\modules\activity\gb\components\PageDesignComponent $PageDesignComponent
 * @property \app\modules\activity\gb\components\ServiceTagComponent $ServiceTagComponent
 * @author wangmeng
 *
 */
class DesignController extends Controller
{
    /**
     * beforeAction
     *
     * @param \yii\base\Action $action
     *
     * @return bool
     * @throws \ego\base\JsonResponseException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $pageId = isset($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : 0;
        $lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : '';
        if (true !== ($auth = $this->PageDesignComponent->checkAuth($pageId, $this->getRoute(), $lang))) {
            return $auth;
        }

        return true;
    }

    /**
     * beforeAction
     *
     * @param \yii\base\Action $action
     * @param mixed            $result the action return result.
     *
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        $pageId = isset($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : 0;
        if ($pageId) {
            $this->PageDesignComponent->changePageAutoRefresh($pageId, $this->getRoute());
        }

        return parent::afterAction($action, $result);
    }

    /**
     * 活动页面设计
     *
     * @param string $group_id 页面端分组32位ID
     * @param string $lang     语言代码简称
     *
     * @return string
     * @throws \yii\base\InvalidArgumentException
     * @throws \Exception
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     */
    public function actionIndex($group_id, $lang = '', $pipeline)
    {
        $data = $this->PageDesignComponent->getIndexData($group_id, $lang, $pipeline);

        if (!\is_array($data)) {
            app()->response->format = Response::FORMAT_HTML;

            return $data;
        }

        $render = \in_array(
            (int) $data['activityInfo']['type'],
            [
                $this->PageDesignComponent::TYPE_MOBILE,
                $this->PageDesignComponent::TYPE_APP,
                $this->PageDesignComponent::TYPE_IOS,
                $this->PageDesignComponent::TYPE_IPAD,
                $this->PageDesignComponent::TYPE_ANDROID
            ],
            true
        ) ? 'mobile_index' : 'index';

        return $this->render($render, $data);
    }

    /**
     * 获取页面关联关系
     *
     * @param int    $page_id
     * @param string $lang
     *
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionRelationList(int $page_id, string $lang)
    {
        return $this->PageDesignComponent->getRelationList($page_id, $lang);
    }

    /**
     * 活动页面设计预览
     *
     * @param string $pid 页面32位ID
     * @param        $lang
     *
     * @return string
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionPreview($pid, $lang)
    {
        //活动预览页不需要layout，会根据所属平台去取平台的头尾，所以要用renderPartial
        app()->response->format = Response::FORMAT_HTML;
        //观察者使用
        if (filter_has_var(INPUT_GET, 'observer')) {
            define('OBSERVER', 0);
        }

        $pageHtml = $this->PageDesignComponent->preview($pid, $lang);

        return $this->renderPartial('preview', ['pageHtml' => $pageHtml]);
    }

    /**
     * 获取页面下所有渠道和语言的预览链接
     *
     * @param int $page_id
     */
    public function actionPreviewList(int $page_id)
    {
        return $this->PageDesignComponent->previewList($page_id);
    }
    
    /**
     * 页面发布(保存页面)
     *
     * @return array
     * @throws \Throwable
     */
    public function actionRelease()
    {
        return $this->PageDesignComponent->activityRelease(
            (int) app()->request->post('page_id', 0),
            app()->request->post('lang', '')
        );
    }
    
    /**
     * 批量发布页面
     */
    public function actionBatchRelease()
    {
        return $this->PageDesignComponent->batchActivityRelease(app()->request->post());
    }
    
    /**
     * 页面设置（设置自定义样式等）
     *
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionSetting()
    {
        return $this->PageDesignComponent->setting(
            (int) app()->request->post('page_id', 0),
            app()->request->post()
        );
    }

    /**
     * 获取页面设置（设置自定义样式等）
     *
     * @param int    $page_id 页面ID
     * @param string $lang    语言代码简称
     *
     * @return array
     * @throws \Throwable
     */
    public function actionGetSetting($page_id, $lang)
    {
        return $this->PageDesignComponent->getSetting((int) $page_id, $lang);
    }

    /**
     * 商品类组件样式设置
     *
     * @return array
     */
    public function actionGoodsComponentStyleSetting()
    {
        return $this->PageDesignComponent->goodsComponentStyleSetting(
            (int) app()->request->post('page_id', 0),
            app()->request->post()
        );
    }
    
    /**
     * 取商品类组件样式数据
     *
     * @param $page_id
     * @param $lang
     *
     * @return array
     */
    public function actionGetGoodsComponentStyleSetting($page_id, $lang)
    {
        return $this->PageDesignComponent->getGoodsComponentStyleSetting((int) $page_id, $lang);
    }
    
    /**
     * 英文页面数据复制
     *
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionCopyPage()
    {
        return $this->PageDesignComponent->copyPage(app()->request->post());
    }

    /**
     * 英文页面SKU复制
     *
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionCopySku()
    {
        return $this->PageDesignComponent->copySku(app()->request->post());
    }

    /**
     * 服务标配置数据保存
     *
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionSaveServiceTag()
    {
        return $this->ServiceTagComponent->saveConfig(app()->request->post());
    }

    /**
     * 服务标配置数据获取
     *
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionGetServiceTag()
    {
        return $this->ServiceTagComponent->getConfig(app()->request->get());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * 活动页面快照
     * @param int $page_id language id
     * @return string
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionSnapshot($page_id)
    {
        app()->response->format = Response::FORMAT_HTML;
        $language = PageLanguageModel::find()->alias('l')->leftJoin(PageModel::tableName().' p','l.page_id = p.id')
            ->select('l.lang,p.pid,p.site_code')->where(['l.id'=>$page_id])->asArray()->one();
        $pageHtml = $this->PageDesignComponent->preview($language['pid'], $language['lang']);
        $lazyImg = explode('/',app()->params['sites'][$language['site_code']]['lazyImg']);
        $pageHtml = preg_replace('/src\=\"(.*?)'.end($lazyImg).'\" data-original\=/','src=',$pageHtml);
        $pageHtml = str_replace('</footer>','<style>body,input,select,textarea { font: 12px/150% Verdana,Arial,Helvetica,sans-serif;color: #333}</style></footer>',$pageHtml);
        return $this->renderPartial('preview', ['pageHtml' => $pageHtml]);
    }
}
