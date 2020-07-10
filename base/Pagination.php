<?php
namespace app\base;

/**
 * 分页组件
 *
 * 修改了更适合后台的默认参数
 */
class Pagination extends \yii\data\Pagination
{
    /**
     * @inheritdoc
     */
    public $pageParam = 'pageNo';
    /**
     * @inheritdoc
     */
    public $pageSizeParam = 'pageSize';
    /**
     * @inheritdoc
     */
    public $pageSizeLimit = [1, 100];

    /**
     * 获取`\yii\data\Pagination`对象
     *
     * @param int $total
     * @return static
     */
    public static function new($total)
    {
        return new static(['totalCount' => $total]);
    }
}
