<?php

namespace app\modules\component\components;

use app\modules\base\models\PageLanguageDataModel;
use app\modules\base\models\PageLanguagePackageModel;
use app\modules\component\models\CategoryModel;
use app\modules\component\models\ComponentSiteRelationModel;
use app\modules\component\models\ComponentTplSiteRelationModel;
use app\modules\component\models\LayoutModel;
use app\modules\component\models\UiModel;
use app\modules\component\models\UiTplModel;
use app\modules\component\models\UiTplRelationModel;
use ego\base\JsonResponseException;
use linslin\yii2\curl;
use Globalegrow\Gateway\Client;
use yii\base\Exception;

/**
 * 组件同步管理
 */
class SyncComponent extends Component
{
    /**
     * @var array 错误信息
     */
    public $errors;
    
    /**
     * 同步组件
     *
     * @throws JsonResponseException
     * @throws \yii\db\Exception
     */
    public function sync()
    {
        if (app()->env->isProduct()) {
            throw new JsonResponseException($this->codeFail, '组件同步只能在非正式环境下使用');
        }
        
        // 获取最大ID
        $idData = [
            'layoutId'   => LayoutModel::find()->max('id'),
            'uiId'       => UiModel::find()->max('id'),
            'tplId'      => UiTplModel::find()->max('id'),
            'categoryId' => CategoryModel::find()->max('id')
        ];
        
        // 参数签名
        $cilent = new Client('', app()->params['gateway']['app_key'], app()->params['gateway']['sign']);
        $sign = $cilent->getRequestSign([]);
        
        // 请求组件信息（URL固定的是线上的地址，因为这是同步线上的数据到本地）
        $curl = new curl\Curl();
        $url = 'http://geshop.gw-ec.com/api/component/need-add';
        $url .= '?' . http_build_query($idData);
        $params = ['sign' => $sign];
        
        $response = $curl->setPostParams($params)->post($url);
        if (200 !== $curl->responseCode) {
            throw new JsonResponseException(
                $this->codeFail,
                '组件信息获取失败:' . $curl->responseCode . $response
            );
        }
        
        $data = [];
        if ($response) {
            $result = json_decode($response, true);
            if ($result['code'] !== $this->codeSuccess) {
                return $result;
            }
            if (!$this->saveData($result['data'])) {
                return app()->helper->arrayResult($this->codeFail, '数据保存失败', ['errors' => $this->errors]);
            }
            
            $data = [
                'layoutCount'                   => \count($result['data']['layoutList']),
                'uiCount'                       => \count($result['data']['uiList']),
                'tplCount'                      => \count($result['data']['tplList']),
                'categoryCount'                 => \count($result['data']['categoryList']),
                'componentSiteRelationCount'    => \count($result['data']['componentSiteRelation']),
                'componentTplSiteRelationCount' => \count($result['data']['componentTplSiteRelation']),
                'tplRelationsCount'             => \count($result['data']['tplRelations'])
            ];
        }
        
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data, ['errors' => $this->errors]);
    }
    
    /**
     * 保存组件数据
     *
     * @param array[] $data
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    private function saveData(array $data)
    {
        //事物开始
        $tr = app()->db->beginTransaction();
        try {
            // 保存layout组件
            if (!empty($data['layoutList'])) {
                foreach ($data['layoutList'] as $item) {
                    $layout = new LayoutModel();
                    $layout->setAttributes($item, false);
                    if (!$layout->insert()) {
                        throw new Exception($layout->flattenErrors(', '));
                    }
                }
            }
            
            // 保存ui组件
            if (!empty($data['uiList'])) {
                foreach ($data['uiList'] as $item) {
                    $ui = new UiModel();
                    $ui->setAttributes($item, false);
                    if (!$ui->insert()) {
                        throw new Exception($ui->flattenErrors(', '));
                    }
                }
            }
            
            // 保存tpl模板
            if (!empty($data['tplList'])) {
                foreach ($data['tplList'] as $item) {
                    $tpl = new UiTplModel();
                    $tpl->setAttributes($item, false);
                    if (!$tpl->insert()) {
                        throw new Exception($tpl->flattenErrors(', '));
                    }
                }
            }
            
            // 保存组件分类category
            if (!empty($data['categoryList'])) {
                foreach ($data['categoryList'] as $item) {
                    $category = new CategoryModel();
                    $category->setAttributes($item, false);
                    if (!$category->insert()) {
                        throw new Exception($category->flattenErrors(', '));
                    }
                }
            }
            
            // 保存组件和站点关联关系
            if (!empty($data['componentSiteRelation'])) {
                foreach ($data['componentSiteRelation'] as $item) {
                    $componentSiteRelations = new ComponentSiteRelationModel();
                    unset($item['id']);
                    $componentSiteRelations->setAttributes($item, false);
                    if (!$componentSiteRelations->insert()) {
                        throw new Exception($componentSiteRelations->flattenErrors(', '));
                    }
                }
            }
            
            // 保存模板和站点关联关系
            if (!empty($data['componentTplSiteRelation'])) {
                foreach ($data['componentTplSiteRelation'] as $item) {
                    $componentTplSiteRelations = new ComponentTplSiteRelationModel();
                    unset($item['id']);
                    $componentTplSiteRelations->setAttributes($item, false);
                    if (!$componentTplSiteRelations->insert()) {
                        throw new Exception($componentTplSiteRelations->flattenErrors(', '));
                    }
                }
            }
            
            // 保存模板和模板转换关联关系
            if (!empty($data['tplRelations'])) {
                foreach ($data['tplRelations'] as $item) {
                    $tplRelations = new UiTplRelationModel();
                    unset($item['id']);
                    $tplRelations->setAttributes($item, false);
                    if (!$tplRelations->insert()) {
                        throw new Exception($tplRelations->flattenErrors(', '));
                    }
                }
            }
    
            // 保存多语言包
            if (!empty($data['packageList'])) {
                foreach ($data['packageList'] as $item) {
                    $languagePackage = new PageLanguagePackageModel();
                    unset($item['id']);
                    $languagePackage->setAttributes($item, false);
                    if (!$languagePackage->insert()) {
                        throw new Exception($languagePackage->flattenErrors(', '));
                    }
                }
            }
    
            // 保存多语言包内容
            if (!empty($data['languageDataList'])) {
                foreach ($data['languageDataList'] as $item) {
                    $languageData = new PageLanguageDataModel();
                    unset($item['id']);
                    $languageData->setAttributes($item, false);
                    if (!$languageData->insert()) {
                        throw new Exception($languageData->flattenErrors(', '));
                    }
                }
            }
            
            $tr->commit();
        } catch (\Exception $e) {
            $tr->rollBack();
            $this->errors[] = $e->getMessage();
            
            return false;
        } catch (\Throwable $e) {
            $tr->rollBack();
            $this->errors[] = $e->getMessage();
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 同步多语言包
     *
     * @return array|mixed
     * @throws JsonResponseException
     */
    public function syncLanguagePackage()
    {
        if (app()->env->isProduct()) {
            throw new JsonResponseException($this->codeFail, '多语言同步只能在非正式环境下使用');
        }
        
        // 获取最大ID
        $idData = [
            'packageId' => PageLanguagePackageModel::find()->max('id'),
            'dataId'    => PageLanguageDataModel::find()->max('id')
        ];
        empty($idData['packageId']) && $idData['packageId'] = 0;
        empty($idData['dataId']) && $idData['dataId'] = 0;

        // 参数签名
        $cilent = new Client('', app()->params['gateway']['app_key'], app()->params['gateway']['sign']);
        $sign = $cilent->getRequestSign([]);
        
        // 请求组件信息（URL固定的是线上的地址，因为这是同步线上的数据到本地）
        $curl = new curl\Curl();
        $url = 'http://geshop.gw-ec.com/api/component/language-package-add';
        $url .= '?' . http_build_query($idData);
        $params = ['sign' => $sign];
        
        $response = $curl->setPostParams($params)->post($url);
        if (200 !== $curl->responseCode) {
            throw new JsonResponseException(
                $this->codeFail,
                '多语言信息获取失败:' . $curl->responseCode . $response
            );
        }
        
        $data = [];
        if ($response) {
            $result = json_decode($response, true);
            if ($result['code'] !== $this->codeSuccess) {
                return $result;
            }
            if (!$this->saveData($result['data'])) {
                return app()->helper->arrayResult($this->codeFail, '数据保存失败', ['errors' => $this->errors]);
            }
            
            $data = [
                'packageCount'      => \count($result['data']['packageList']),
                'languageDataCount' => \count($result['data']['languageDataList'])
            ];
        }
        
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data, ['errors' => $this->errors]);
    }
}
