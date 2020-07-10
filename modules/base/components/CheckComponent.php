<?php

namespace app\modules\base\components;

/**
 * 数据检测组件
 * Class CheckComponent
 * @package app\modules\base\components
 */
class CheckComponent extends Component
{
    /**
     * 默认入口
     * @throws \yii\db\Exception
     */
    public function index()
    {
        $result = '';

        /* 检测page_language表的lang和activity表的lang不一致的 */
        $sql1 = /** @lang sql */
            '
            select * from (
                select GROUP_CONCAT(l.lang) as pLang, a.lang as aLang, a.id as activity_id, p.id as page_id
                from page_language as l
                left join page as p on l.page_id = p.id
                left join activity as a on p.activity_id = a.id
                where p.activity_id > 0 and p.is_delete = 0 and a.is_delete = 0
                group by l.page_id
            ) as t
            where t.aLang <> t.pLang and LENGTH(t.aLang) <> LENGTH(t.pLang);
        ';
        $result1 = app()->db->createCommand($sql1)->queryAll();
        $result .= $this->echoString('检测page_language表的lang和activity表的lang不一致的', $result1, 1);

        /* 检测page表site_code和activity表site_code不一致的 */
        $sql2 = /** @lang sql */
            '
            select p.id, p.activity_id, p.site_code as p_site_code, a.site_code as a_site_code 
            from page as p
            left join activity as a on p.activity_id = a.id
            where p.activity_id > 0 and p.site_code <> a.site_code;
        ';
        $result2 = app()->db->createCommand($sql2)->queryAll();
        $result .= $this->echoString('检测page表site_code和activity表site_code不一致的', $result2, 2);

        /* 检测未设置默认模板的组件（包含默认模板被删除的） */
        $sql3 = /** @lang sql */
            '
            select u.*, t.is_delete as tpl_is_delete
            from ui_component as u
            left join ui_component_tpl as t on u.tpl_id = t.id
            where u.is_delete = 0 and u.`status` = 3 and (u.tpl_id = 0 or t.is_delete = 1 or t.`status` = 0);
        ';
        $result3 = app()->db->createCommand($sql3)->queryAll();
        $result .= $this->echoString('检测启用的组件中未设置默认模板的（包含默认模板被删除的）', $result3, 3);

        return $result;
    }

    /**
     * 输出
     * @param string $title
     * @param array $data
     * @param int $index
     * @return string
     */
    private function echoString(string $title, array $data, int $index)
    {
        $string = '<h3>' . $index . '.' . $title . '</h3>';
        $string .= '异常数据总数：' . \count($data) . '<br />';
        $string .= '异常数据明细：<pre>' . print_r($data, true) . '</pre><br />';
        return $string;
    }
}
