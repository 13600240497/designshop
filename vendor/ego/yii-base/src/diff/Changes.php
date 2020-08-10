<?php
namespace ego\diff;

/**
 * php比较两个字符串差异组件
 */
class Changes extends \Globalegrow\PhpDiff\Changes
{
    /**
     * @var \ego\models\ActiveRecord
     */
    public $model;
    /**
     * @var array|null 原值
     */
    public $old;
    /**
     * @var array 新值
     */
    public $new;

    /**
     * @inheritdoc
     */
    public function get(array $old = null, array $new = null)
    {
        return parent::get($old ?: $this->old, $new ?: $this->new);
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        if ($this->model) {
            foreach ($this->model->getTableSchema()->columns as $field => $item) {
                if (false !== strpos($item->type, 'text')) {
                    $this->diffDetailFields[] = $field;
                }
            }
        }
    }
}
