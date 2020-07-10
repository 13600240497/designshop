<?php

namespace app\modules\activity\controllers;

use app\modules\common\models\PageLanguageModel;
use app\modules\common\models\PageModel;
use yii\web\Response;
use app\modules\activity\components\PageDesignComponent;

/**
 * 活动页面设计管理类
 *
 * @property \app\modules\activity\components\PageDesignComponent $PageDesignComponent
 * @author wangmeng
 *
 */
class DesignController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * beforeAction
     * @param \yii\base\Action $action
     * @return bool
     * @throws \ego\base\JsonResponseException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $pageId = isset($_REQUEST['page_id']) ? (int)$_REQUEST['page_id'] : 0;
        $lang = isset($_REQUEST['lang']) ? (string)$_REQUEST['lang'] : '';
        if (true !== ($auth = $this->PageDesignComponent->checkAuth($pageId, $this->getRoute(), $lang))) {
            return $auth;
        }

        return true;
    }

    /**
     * beforeAction
     * @param \yii\base\Action $action
     * @param mixed $result the action return result.
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        $pageId = isset($_REQUEST['page_id']) ? (int)$_REQUEST['page_id'] : 0;
        if ($pageId) {
            $this->PageDesignComponent->changePageAutoRefresh($pageId, $this->getRoute());
        }

        return parent::afterAction($action, $result);
    }

    /**
     * 活动页面设计
     * @param string $pid 页面32位ID
     * @param string $lang 语言代码简称
     * @return string
     * @throws \yii\base\InvalidArgumentException
     * @throws \Exception
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     */
    public function actionIndex($pid, $lang = '')
    {
        $data = $this->PageDesignComponent->getIndexData($pid, $lang);

        if (!\is_array($data)) {
            app()->response->format = Response::FORMAT_HTML;
            return $data;
        }

        $render = \in_array(
            (int)$data['activityInfo']['type'],
            [PageDesignComponent::TYPE_MOBILE, PageDesignComponent::TYPE_APP],
            true
        ) ? 'mobile_index' : 'index';

        return $this->render($render, $data);
    }


    /**
     * 获取页面关联关系
     *
     * @param int $page_id
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
     * @param string $pid 页面32位ID
     * @param $lang
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
     * 页面发布(保存页面)
     * @return array
     * @throws \Throwable
     */
    public function actionRelease()
    {
        return $this->PageDesignComponent->activityRelease(
            (int)app()->request->post('page_id', 0),
            app()->request->post('lang', ''),
            '',
            app()->request->post('langList',[])
        );
    }

    /**
     * 页面设置（设置自定义样式等）
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionSetting()
    {
        return $this->PageDesignComponent->setting(
            (int)app()->request->post('page_id', 0),
            app()->request->post()
        );
    }

    /**
     * 获取页面设置（设置自定义样式等）
     * @param int $page_id 页面ID
     * @param string $lang 语言代码简称
     * @return array
     * @throws \Throwable
     */
    public function actionGetSetting($page_id, $lang)
    {
        return $this->PageDesignComponent->getSetting((int)$page_id, $lang);
    }

    /**
     * 英文页面数据复制
     * @throws \yii\db\Exception
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     * @throws \Exception
     */
    public function actionCopyPage()
    {
        return $this->PageDesignComponent->copyPage(app()->request->get());
    }

    /**
     * 英文页面SKU复制
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
        $pageHtml = str_replace('</footer></body>','<style>body,input,select,textarea { font: 12px/150% Verdana,Arial,Helvetica,sans-serif;color: #333}</style></footer></body>',$pageHtml);
        return $this->renderPartial('preview', ['pageHtml' => $pageHtml]);
    }
}
