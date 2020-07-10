<?php

namespace app\modules\home\dl\controllers;

use app\modules\common\dl\models\PageLanguageModel;
use app\modules\common\dl\models\PageModel;
use app\modules\activity\dl\components\PageDesignComponent;
use app\base\SitePlatform;
use yii\web\Response;

/**
 * 活动页面设计管理类
 *
 * @property \app\modules\home\dl\components\PageDesignComponent $PageDesignComponent
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
     * @param int    $iframe 是否为iframe
     * @return string
     * @throws \yii\base\InvalidArgumentException
     * @throws \Exception
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\ViewNotFoundException
     * @throws \Throwable
     */
    public function actionIndex($pid, $lang = '', $iframe=0)
    {
        // 获取当前装修界面显示类型
        $designDevice = app()->request->get('device');

        /** @var \app\modules\common\dl\models\PageModel $pageModel */
        $pageModel = PageModel::getByPId($pid);
        if (!$pageModel) {
            return '页面不存在或已被删除';
        }

        $platformCode = SitePlatform::getPlatformCodeBySiteCode($pageModel->site_code);
        $responsiveDevices = ['pc', 'pad', 'm'];
        if (empty($designDevice) || !in_array($designDevice, $responsiveDevices)) {
            $designDevice = \in_array($platformCode,
                [SitePlatform::PLATFORM_CODE_APP, SitePlatform::PLATFORM_CODE_WAP],
                true
            ) ? 'm' : 'pc';
        }

        if (!headers_sent()) {
            $expire = time() + 60*60*24; // 1天
            setcookie("geshop_design_platform", $designDevice, $expire, '/', DOMAIN); // 用于DL站点
            $_COOKIE['geshop_design_platform'] = $designDevice;
        }

        $data = $this->PageDesignComponent->getIndexData($pageModel, $lang, $designDevice);
        if (!\is_array($data)) {
            app()->response->format = Response::FORMAT_HTML;
            return $data;
        }

        $viewName = (SitePlatform::PLATFORM_CODE_APP === $platformCode) ? 'mobile_index' : 'index';
        $viewName = $iframe ? $viewName.'_iframe' : $viewName;

        return $this->render($viewName, $data);
    }

    /**
     * 活动页面设计预览
     * @param string $pid 页面32位ID
     * @param $lang
     * @param $dynamic 0：实时数据 1：线上数据
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
     * 页面上线(保存页面)
     * @return array
     * @throws \Throwable
     */
    public function actionOnline()
    {
        return $this->PageDesignComponent->homeOnline(
            (int)app()->request->post('page_id', 0),
            app()->request->post('lang', ''),
            app()->request->post('langList','')
        );
    }

    /**
     * 页面上线(保存页面)/设为首页B
     * @return array
     * @throws \Throwable
     */
    public function actionOnlineB()
    {
        return $this->PageDesignComponent->homeOnlineB(
            (int)app()->request->post('page_id', 0),
            app()->request->post('lang', ''),
            app()->request->post('langList','')
        );
    }

    /**
     * 页面发布
     *
     * @return array
     */
    public function actionRelease()
    {
        $pageId = app()->request->post('page_id', 0);

        return $this->PageDesignComponent->homeRelease($pageId);
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
