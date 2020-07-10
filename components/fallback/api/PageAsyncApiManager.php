<?php
namespace app\components\fallback\api;

use yii\helpers\Json;
use app\base\SitePlatform;
use app\base\SiteConstants;

/**
 * 活动页面组件异步API接口兜底管理
 *
 * 注意： 这里是公共类同时支持RG、ZF、DL站点，变量注解以ZF站点类为例
 *
 * @author TianHaisen
 */
class PageAsyncApiManager
{
  /** @var \app\modules\common\zf\models\PageModel 活动页面Model */
  private $pageModel;

  /** @var string 语言简码 */
  private $lang;

  /** @var PageAsyncApiFallback */
  private $pageAsyncApiFallback;

  /**
   * 构造函数
   *
   * @param \app\modules\common\zf\models\PageModel|array $pageModel
   * @param string $lang
   * @throws AsyncApiFallbackException
   */
  public function __construct($pageModel, $lang)
  {
    if (is_array($pageModel)) {
      $websiteCode = SitePlatform::splitSiteCode($pageModel['site_code'])[0];
      $pageModel = $this->newSitePageModel($websiteCode, $pageModel);
    }

    $this->pageModel = $pageModel;
    $this->lang = $lang;
    $this->pageAsyncApiFallback = new PageAsyncApiFallback($this->pageModel, $this->lang);
  }

  /**
   * new 一个新的页面UI组件对象
   *
   * @param string $websiteCode
   * @param array $data 组件数据
   * @return \app\modules\common\zf\models\PageModel
   */
  private function newSitePageModel($websiteCode, $data)
  {
    if (SiteConstants::SITE_GROUP_CODE_ZF === $websiteCode) {
      $pageModel = new \app\modules\common\zf\models\PageModel();
    } elseif (SiteConstants::SITE_GROUP_CODE_DL === $websiteCode) {
      $pageModel = new \app\modules\common\dl\models\PageModel();;
    } else {
      $pageModel = new \app\modules\common\models\PageModel();
    }

    $pageModel->setAttributes($data, false);
    return $pageModel;
  }

  /**
   * 检查否有需要兜底的API，如果有添加到兜底队列
   */
  public function checkFallbackAsyncApi()
  {
    if ($this->pageAsyncApiFallback->hasFallbackAsyncApi()) {
      $websiteCode = SitePlatform::splitSiteCode($this->pageModel->site_code)[0];
      $taskInfo = Json::encode([
        'pageId' => $this->pageModel->id,
        'lang' => $this->lang
      ]);
      app()->redis->rpush(app()->redisKey->getUiAsyncApiFallbackListKey($websiteCode), $taskInfo);
    }
  }

  /**
   * 从队列中获取一个任务
   *
   * @param string $websiteCode 网站简码，如: zf/rg
   * @return array|null
   */
  public static function getUiAsyncApiFallbackTask($websiteCode)
  {
    $taskInfo = app()->redis->lpop(app()->redisKey->getUiAsyncApiFallbackListKey($websiteCode));
    if (!empty($taskInfo)) {
      return Json::decode($taskInfo, true);
    }
    return null;
  }

  /**
   * 清理队列
   *
   * @param string $websiteCode 网站简码，如: zf/rg
   * @return array
   */
  public static function cleanTaskQueue($websiteCode)
  {
    $key = app()->redisKey->getUiAsyncApiFallbackListKey($websiteCode);
    $count = app()->redis->llen($key);
    app()->redis->del($key);
    return ['count' => $count];
  }

  /**
   * 查看队列详情
   *
   * @param string $websiteCode 网站简码，如: zf/rg
   * @return array
   */
  public static function getTaskQueueInfo($websiteCode)
  {
    $key = app()->redisKey->getUiAsyncApiFallbackListKey($websiteCode);
    $count = app()->redis->llen($key);
    $pagination = \app\base\Pagination::new($count);
    $start = ($pagination->page - 1) * $pagination->pageSize;
    $end = $start + $pagination->pageSize - 1;

    $list = app()->redis->lrange($key, $start, $end);
    $list = array_map(function ($item) {
      return Json::decode($item, true);
    }, $list);

    return [
      'key'         => $key,
      'currentTime' => date('Y-m-d H:i:s'),
      'list'        => $list,
      'pagination'  => [
        $pagination->pageParam     => (int) $pagination->page + 1,
        $pagination->pageSizeParam => (int) $pagination->pageSize,
        'totalCount'               => (int) $pagination->totalCount
      ]
    ];
  }

}