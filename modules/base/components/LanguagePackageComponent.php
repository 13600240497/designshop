<?php

namespace app\modules\base\components;


use app\modules\base\models\AdminModel;
use app\modules\base\models\PageLanguagePackageModel;
use yii\db\Exception;

class LanguagePackageComponent extends Component
{
    
    /**
     * 语言包列表
     *
     * @param string $siteCode
     *
     * @return array
     */
    public function lists(string $siteCode)
    {
        $data = PageLanguagePackageModel::getLangPackageList($siteCode);
        if (!empty($data['list']) && is_array($data['list'])) {
            foreach ($data['list'] as &$value) {
                $value['update_user'] = AdminModel::getRealNameByUserName($value['update_user']);
                $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            }
        }
    
        return app()->helper->arrayResult($this->codeSuccess, $this->msgSuccess, $data);
    }
    
    /**
     * 新增语言
     *
     * @param string $siteCode
     * @param string $names
     * @param string $codes
     *
     * @return array
     */
    public function addPackageLang(string $siteCode, string $names, string $codes)
    {
        $nameArray = explode(',', $names);
        $codeArray = explode(',', $codes);
        $hasLang = PageLanguagePackageModel::checkExistsLangPackage($siteCode, $codeArray);
        if (!empty($hasLang)) {
            return app()->helper->arrayResult($this->codeFail, implode(',', $hasLang) . '已存在');
        }
        
        try {
            $combine = array_combine($nameArray, $codeArray);
            $data = [];
            foreach ($combine as $name => $code) {
                $data[] = [
                    $siteCode,
                    $name,
                    $code,
                    app()->user->username,
                    time(),
                    app()->user->username,
                    time()
                ];
            }
            $columns = ['site_code', 'lang_name', 'lang', 'create_user', 'create_time', 'update_user', 'update_time'];
            if (PageLanguagePackageModel::insertAll($columns, $data)) {
                return app()->helper->arrayResult($this->codeSuccess, '保存成功');
            }
        } catch (\Exception $exception) {
            //两个数组的单元数不同
            return app()->helper->arrayResult($this->codeFail, '语言和简码数量不一致');
        } catch (Exception $exception) {
            return app()->helper->arrayResult($this->codeFail, '保存失败');
        }
    }
}