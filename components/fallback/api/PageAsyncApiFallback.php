<?php
namespace app\components\fallback\api;

use Yii;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use app\base\SitePlatform;
use app\base\SiteConstants;

/**
 * 活动页面组件异步API接口兜底
 *
 * 注意： 这里是公共类同时支持RG、ZF、DL站点，变量注解以ZF站点类为例
 *
 * @author TianHaisen
 */
class PageAsyncApiFallback
{
  /** @var \app\modules\common\zf\models\PageModel 活动页面Model对象 */
  private $pageModel;

  /** @var string 语言简码 */
  private $lang;

  /** @var array 兜底组件配置 */
  private static $fallbackConfig = null;

  /** @var array 站点支持异步接口配置 */
  private static $asyncApiConfig = null;

  /**
   * 构造函数
   *
   * @param \app\modules\common\zf\models\PageModel $pageModel 活动页面Model对象
   * @param string $lang 语言简码
   * @throws AsyncApiFallbackException
   */
  public function __construct($pageModel, $lang)
  {
    if (empty($pageModel)) {
      throw new AsyncApiFallbackException('无效的参数!');
    }

    $this->websiteCode = SitePlatform::splitSiteCode($pageModel->site_code)[0];
    $this->pageModel = $pageModel;
    $this->lang = $lang;

    $this->loadFallbackConfig();
    $this->loadAsyncApiConfig();
  }

  /**
   * 检查是否有需求兜底的API
   *
   * @return bool
   */
  public function hasFallbackAsyncApi()
  {
      // 跳过原生页面
      if ($this->pageModel->hasAttribute('is_native')) {
          $isNative = (int)$this->pageModel->is_native;
          if ($isNative === 1) {
              return false;
          }
      }

    // 获取组件数据
    $allUiList = $this->getAllUiWithData();
    if (empty($allUiList)) {
      return false;
    }

    $tplIds = array_unique(array_column($allUiList, 'tpl_id'));
    if (empty($tplIds)) {
      return false;
    }

    $uiTplInfoList = $this->getUiTplInfoByIds($tplIds);
    foreach ($uiTplInfoList as $uiTplInfo) {
      // 跳过同步组件
      $isAsyncUi = isset($uiTplInfo['is_async']) ? (int)$uiTplInfo['is_async'] : 0;
      if (0 === $isAsyncUi) {
        continue;
      }

      if (!empty($this->getUiAsyncApiConfig($uiTplInfo['component_key'], $uiTplInfo['name_en']))) {
        return true;
      }
    }

    return false;
  }

  /**
   * 生成兜底json数据推送到S3
   */
  public function fallback()
  {
    // 获取组件数据
    $allUiList = $this->getAllUiWithData();
    if (empty($allUiList)) {
      return;
    }

    $tplIds = array_unique(array_column($allUiList, 'tpl_id'));
    if (empty($tplIds)) {
      return;
    }

    $uiTplInfoList = $this->getUiTplInfoByIds($tplIds);
    $allRequestApiInfo = [];
    $uiResult = [];
    foreach ($allUiList as $uiInfo) {
      try {
        // 跳过没有找到组件模板的
        $uiId = $uiInfo['id'];
        $tplId = $uiInfo['tpl_id'] ?? 0;
        if (!isset($uiTplInfoList[$tplId])) {
          continue;
        }

        $uiTplInfo = $uiTplInfoList[$tplId];
        $uiKey = $uiTplInfo['component_key'];
        $tplKey = $uiTplInfo['name_en'];

        // 跳过同步组件
        $isAsyncUi = isset($uiTplInfo['is_async']) ? (int)$uiTplInfo['is_async'] : 0;
        if (0 === $isAsyncUi) {
          continue;
        }

        // 跳过没有配置
        $apiInfo = $this->getUiAsyncApiConfig($uiKey, $tplKey);
        if (empty($apiInfo)) {
          continue;
        }

        // 生成完整接口地址
        $allRequestApiInfo[] = [
          'uiId' => $uiId,
          'url' => $this->getApiAndParams($apiInfo, $uiInfo)
        ];
      } catch (\Exception $e) {
        $uiResult[$uiId] = sprintf('生成接口异常, 在 %d 行,错误: %s', $e->getLine() , $e->getMessage());
      }
    }

    if (!empty($allRequestApiInfo)) {
      // 并发请求站点接口
      $apiRequest = new PageAsyncApiRequest($allRequestApiInfo);
      $apiRequest->concurrencyRequest();
      $resultInfoList = $apiRequest->getRequestResult();
      foreach ($resultInfoList as $_uiId => $resultInfo) {
        if ($resultInfo['status']) {
          try {
            // 推送json文件到s3
            $jsonBody = trim($resultInfo['content'], '()');
            $redisData = $this->getPublishRedisData($this->pageModel, $this->lang, $_uiId, $jsonBody);
            app()->swoole->init()->send(['data' => [$redisData], 'action' => 'asyncPushPage', 'mode' => 'multi']);
            $uiResult[$_uiId] = sprintf('请求接口成功 [%s], 生成json文件成功,S3路径: %s', $resultInfo['url'], $redisData['s3_key']);
          } catch (\Exception $e) {
            $uiResult[$_uiId] = sprintf('生成推送异常, 在 %d 行,错误: %s', $e->getLine() , $e->getMessage());
          }
        } else {
          $uiResult[$_uiId] = sprintf('请求接口失败 [%s], 错误: %s', $resultInfo['url'], $resultInfo['message']);
        }
      }
    }

    // 写入日志
    if (!empty($uiResult)) {
      $message = sprintf(
        '站点 %s 页面ID %d 语言 %s 结果:' . PHP_EOL,
        $this->pageModel->site_code, $this->pageModel->id, $this->lang
      );

      foreach ($uiResult as $_uiId => $_message) {
        $message .= $_uiId .' => '. $_message . PHP_EOL;
      }
      Yii::info($message, __CLASS__);
    }
  }

  /**
   * 获取推送信息
   *
   * @param \app\modules\common\zf\models\PageModel $pageModel 页面Model对象
   * @param string $languageCode 语言简码
   * @param int $uiId 组件ID
   * @param string $apiBody 接口返回结果
   * @return array
   * @throws AsyncApiFallbackException
   */
  private function getPublishRedisData($pageModel, $languageCode, $uiId, $apiBody)
  {
    $localFilePath = $this->getPublishFileLocalPath($pageModel, $languageCode, $uiId);
    $absFilePath = $this->getRelativePathByLocalPath($localFilePath);

    if (false === file_put_contents($localFilePath, $apiBody)) {
      throw new AsyncApiFallbackException('文件内容写入失败：' . $localFilePath);
    }

    $item = [
      'local_path'          => $localFilePath,
      's3_key'              => $absFilePath,
      'file_type'           => \app\components\auto\AutoRefreshUi::PUBLISH_ASYNC_DATA_JSON_TYPE,
    ];
    return $item;
  }

  /**
   * 获取推送文件本地路径
   *
   * @param \app\modules\common\zf\models\PageModel $pageModel 页面Model对象
   * @param string $languageCode 语言简码
   * @param int $uiId 组件ID
   * @return string 文件本地绝对路径
   */
  private function getPublishFileLocalPath($pageModel, $languageCode, $uiId)
  {
    $publishConfig = $this->getSiteConfig($pageModel, 's3PublishPath');
    $publishUri = $publishConfig[ $languageCode ] .'/fallback/' . $pageModel->id;
    $publishUri = str_replace('\\', '/', trim($publishUri, '/'));
    $localPath = Yii::getAlias('@runtime/'. $publishUri);
    if (!is_dir($localPath) && !mkdir($localPath, 0777, true) && !is_dir($localPath)) {
      return '';
    }
    return $localPath .DIRECTORY_SEPARATOR. $uiId .'.json';
  }

  /**
   * 根据文件在本地绝对路径返回相对路径
   *
   * @param string $localPath 文件在本地绝对路径
   * @return string 文件相对路径,如: /publish/www.zaful.com/en/test-zf-blog-42424.html
   */
  private function getRelativePathByLocalPath($localPath)
  {
    $explode = !empty($localPath) ? explode('runtime', $localPath) : [];
    return $explode && isset($explode[1]) ? $explode[1] : '';
  }

  /**
   * 获取站点配置
   *
   * @param \app\modules\common\zf\models\PageModel $pageModel 页面Model对象
   * @param string $key 配置项
   * @return array
   */
  private function getSiteConfig($pageModel, $key)
  {
    $siteCode = $pageModel->site_code;
    if (SiteConstants::SITE_GROUP_CODE_ZF === $this->websiteCode) {
      return app()->params['sites'][ $siteCode ][ $key ][ $pageModel->pipeline ] ?? [];
    }
    return app()->params['sites'][ $siteCode ][ $key ] ?? [];
  }

  /**
   * 获取异步请求API完整URL
   *
   * @param array $apiInfo 组件兜底API配置信息
   * @param array $uiInfo 组件信息
   * @return string
   * @throws AsyncApiFallbackException
   */
  private function getApiAndParams($apiInfo, &$uiInfo)
  {
    $siteCode = $this->pageModel->site_code;
    $apiUrl = $apiInfo['api'];
    if (!StringHelper::startsWith($apiUrl, 'http')) {
      if (StringHelper::startsWith($apiUrl, '/')) {
        $apiUrl = app()->params['sites'][$siteCode]['domain'] . $apiUrl;
      } else {
        $apiUrl = self::$asyncApiConfig[$siteCode][$apiUrl]['url'] ?? '';
      }
    }

    if (empty($apiUrl)) {
      $message = sprintf('没有找接口[%s]!', $apiInfo['api']);
      throw new AsyncApiFallbackException($message);
    }

    $params = [];
    if (isset($apiInfo['params']) && !empty($apiInfo['params'])) {
      $uiData = Json::decode($uiInfo['data'], true);
      foreach ($apiInfo['params'] as $name => $valueKey) {
        if (is_array($valueKey)) {
          $_params = [];
          foreach ($valueKey as $_name => $_valKey) {
            $_params[$_name] = $this->getParamValue($apiInfo, $uiData, $_valKey);
          }
          $params[$name] = Json::encode($_params);
        } else {
          $params[$name] = $this->getParamValue($apiInfo, $uiData, $valueKey);
        }
      }
    }

    if (!empty($params)) {
      $apiUrl .= (strpos($apiUrl, '?') !== false) ? '&' : '?';
      $apiUrl .= http_build_query($params);
    }

    return $apiUrl;
  }

  /**
   * @param array $apiInfo 组件兜底API配置信息
   * @param array $uiData 组件配置数据
   * @param mixed $valueKey
   * @return mixed
   * @throws AsyncApiFallbackException
   */
  private function getParamValue($apiInfo, &$uiData, $valueKey)
  {
    if (is_int($valueKey)) {
      return $valueKey;
    } elseif ('callback' === $valueKey) {
      return 'jQuery19109946044125825673_'. time();
    } elseif ('lang' === $valueKey) {
      return $this->lang;
    } elseif ('pipeline' === $valueKey) {
     return (SiteConstants::SITE_GROUP_CODE_ZF === $this->websiteCode) ? $this->pageModel->pipeline : '';
    } else {
      if ('goodsSKU' === $valueKey) {
        return $this->getUiGoodsSku($apiInfo, $uiData);
      } else {
        // 是否有默认值
        if (strpos($valueKey, '|') === false) {
          return $uiData[$valueKey] ?? '';
        } else {
          list($_key, $_defaultValue) = explode('|', $valueKey, 2);
          return $uiData[$_key] ?? $_defaultValue;
        }
      }
    }
  }


  /**
   * 获取组件SKU列表
   *
   * @param array $apiInfo 组件兜底API配置信息
   * @param array $uiData 组件配置数据
   * @return string
   * @throws AsyncApiFallbackException
   */
  private function getUiGoodsSku($apiInfo, &$uiData)
  {
    if (!isset($uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU])) {
      $message = sprintf('组件配置没有[%s]配置项!', SiteConstants::UI_COMPONENT_KEY_GOODS_SKU);
      throw new AsyncApiFallbackException($message);
    }

    $ipsGoodsSkuKey = \app\modules\soa\components\ips\SingleLevelIps::UI_COMPONENT_FIELD_KEY_IPS_GOODS_SKU;
    if (is_array($uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU])) {
      // tab 选项
      return '';
    } else {
      $dataFromKey = \app\modules\soa\components\ips\SingleLevelIps::UI_COMPONENT_FIELD_KEY_GOODS_DATA_FROM;
      if (isset($uiData[$dataFromKey]) && (SiteConstants::GOODS_SKU_FROM_IPS === (int)$uiData[$dataFromKey])) {
        $goodsSkuString = $uiData[$ipsGoodsSkuKey];
      } else {
        $goodsSkuString = $uiData[SiteConstants::UI_COMPONENT_KEY_GOODS_SKU];
      }

      if (!empty($goodsSkuString) && isset($apiInfo['pageSize'])) {
        $_pageSize = (int)$apiInfo['pageSize'];
        $_skuList = explode(SiteConstants::CHAR_COMMA, $goodsSkuString);
        if ($_pageSize <= count($_skuList)) {
          $goodsSkuString = array_slice($_skuList, 0, $_pageSize);
        }
      }

      return $goodsSkuString;
    }
  }

  /**
   * 获取页面组件及配置数据列表
   *
   * @return array
   */
  private function getAllUiWithData()
  {
    // 获取发布缓存页面数据
    $publishedCacheData = $this->getPagePublishedCacheData($this->pageModel->id, $this->lang);
    if (empty($publishedCacheData)) {
      return null;
    }

    // 获取组件数据
    $allUiList = isset($publishedCacheData['uilist']) ? Json::decode($publishedCacheData['uilist'], true) : [];
    if (empty($allUiList)) {
      return null;
    }

    $pageUiList = [];
    foreach ($allUiList as $layoutUiList) {
      foreach ($layoutUiList as $positionUiList) {
        $pageUiList = array_merge($pageUiList, $positionUiList);
      }
    }

    return $pageUiList;
  }

  /**
   * 获取页面发布的缓存数据
   *
   * @param int $pageId 页面ID
   * @param string $lang 语言简码
   * @return array
   */
  private function getPagePublishedCacheData($pageId, $lang)
  {
    if (SiteConstants::SITE_GROUP_CODE_ZF === $this->websiteCode) {
      return \app\modules\common\zf\models\PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
    } elseif (SiteConstants::SITE_GROUP_CODE_DL === $this->websiteCode) {
      return \app\modules\common\dl\models\PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
    } else {
      return \app\modules\common\models\PagePublishCacheModel::getCurrentUsedCache($pageId, $lang);
    }
  }

  /**
   * @param array $ids
   * @return array
   */
  private function getUiTplInfoByIds($ids)
  {
    return \app\modules\component\models\UiTplModel::find()
      ->select('id, name_en, component_key, is_async')
      ->where(['id' => $ids])
      ->indexBy('id')
      ->asArray()
      ->all();
  }

  /**
   * 获取组件异步API配置信息
   *
   * @param string $uiKey
   * @param string $tplKey
   * @return array|null
   */
  private function getUiAsyncApiConfig($uiKey, $tplKey)
  {
    if (isset(self::$fallbackConfig[$uiKey][$tplKey][$this->websiteCode])) {
      return self::$fallbackConfig[$uiKey][$tplKey][$this->websiteCode];
    }
    return null;
  }

  /**
   * 加载兜底配置文件
   */
  protected function loadFallbackConfig()
  {
    if (null === self::$fallbackConfig) {
      $configFile = Yii::getAlias('@app/config/fallback.php');
      self::$fallbackConfig = require($configFile);
    }
  }

  /**
   * 加载站点的接口配置
   */
  private function loadAsyncApiConfig()
  {
    if (self::$asyncApiConfig === null) {
      $configFile = Yii::getAlias('@app/config/sites/interface/interface.' . YII_ENV . '.php');
      self::$asyncApiConfig = require($configFile);
    }
  }

}