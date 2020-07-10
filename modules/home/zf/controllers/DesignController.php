<?php

namespace app\modules\home\zf\controllers;

use app\modules\common\zf\models\PageLanguageModel;
use app\modules\common\zf\models\PageModel;
use app\modules\home\zf\components\PageDesignComponent;
use yii\web\Response;

/**
 * 活动页面设计管理类
 *
 * @property \app\modules\home\zf\components\PageDesignComponent $PageDesignComponent
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
        if (true !== ($auth = $this->PageDesignComponent->checkAuth($pageId, $this->getRoute()))) {
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
     * @param string $group_id 页面分组ID
     * @param string $lang     语言代码简称
     * @param string $pipeline 国家编码
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

        //按站点加载不同模板文件夹
        $viewName = SITE_GROUP_CODE . '/';
        $viewName .= \in_array(
            (int) $data['type'],
            [PageDesignComponent::TYPE_MOBILE, PageDesignComponent::TYPE_APP],
            true
        ) ? 'mobile_index' : 'index';

        $iframe = app()->getRequest()->get('iframe', 0);

        $viewName = $iframe ? $viewName.'_iframe' : $viewName;

        return $this->render($viewName, $data);
    }

    /**
     * 活动页面设计预览
     *
     * @param string $pid     页面32位ID
     * @param        $lang
     * @param        $dynamic 0：实时数据 1：线上数据
     *
     * @return string
     * @throws \ego\base\JsonResponseException
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionPreview($pid, $lang, $dynamic = 1)
    {
        //活动预览页不需要layout，会根据所属平台去取平台的头尾，所以要用renderPartial
        app()->response->format = Response::FORMAT_HTML;
        $useCache = !empty($dynamic) ? false : true;
        $pageHtml = $this->PageDesignComponent->preview($pid, $lang, $useCache);

        return $this->renderPartial('preview', ['pageHtml' => $pageHtml]);
    }

    /**
     * 页面上线
     *
     * @return array
     * @throws \Throwable
     */
    public function actionOnline()
    {
        $pageId = app()->request->post('page_id', 0);
        $pipeline = app()->request->post('pipeline', '');

        return $this->PageDesignComponent->setHomePage($pageId, $pipeline);
    }

    /**
     * 页面发布
     *
     * @return array
     * @throws \Throwable
     */
    public function actionRelease()
    {
        $homeType = app()->request->post('home_type', 0);
        if (empty($homeType)) {
            return $this->PageDesignComponent->batchHomeOnline(
                (int) app()->request->post('page_id', 0),
                app()->request->post('pipeline', '')
            );
        } else {
            return $this->PageDesignComponent->batchHomeOnlineB(
                (int) app()->request->post('page_id', 0),
                app()->request->post('pipeline', '')
            );
        }
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
     * 获取跨渠道复制页面的渠道配置
     *
     * @param $page_id
     * @param $lang
     *
     * @return array
     * @throws \Throwable
     * @throws \ego\base\JsonResponseException
     */
    public function actionGetCopyPipeline($page_id, $lang)
    {
        return $this->PageDesignComponent->getCopyPipeline($page_id, $lang);
    }

    /**
     * 跨渠道复制页面
     *
     * @return array
     * @throws \Throwable
     * @throws \ego\base\JsonResponseException
     */
    public function actionCopyPipeline()
    {
        return $this->PageDesignComponent->copyPipeline(app()->request->post());
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
        return $this->PageDesignComponent->copyPage(app()->request->get());
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
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * 活动页面快照
     *
     * @param int $page_id language id
     *
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
        $language = PageLanguageModel::find()->alias('l')->leftJoin(PageModel::tableName() . ' p', 'l.page_id = p.id')
            ->select('l.lang,p.pid,p.site_code')->where(['l.id' => $page_id])->asArray()->one();
        $pageHtml = $this->PageDesignComponent->preview($language['pid'], $language['lang']);
        $lazyImg = explode('/', app()->params['sites'][ $language['site_code'] ]['lazyImg']);
        $pageHtml = preg_replace('/src\=\"(.*?)' . end($lazyImg) . '\" data-original\=/', 'src=', $pageHtml);
        $pageHtml = str_replace('</footer>', '<style>body,input,select,textarea { font: 12px/150% Verdana,Arial,Helvetica,sans-serif;color: #333}</style></footer>', $pageHtml);

        return $this->renderPartial('preview', ['pageHtml' => $pageHtml]);
    }
}
