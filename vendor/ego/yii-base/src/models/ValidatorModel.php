<?php
namespace ego\models;

/**
 * 验证器组件
 *
 * 可实现脱离数据库进行数据验证
 *
 * ```php
 *      $val = app()->validatorModel->new($rules)->load($data);
 *      if ($val->validate()) {
 *          // 像操作ActiveRecord一样操作
 *     }
 * ```
 */
class ValidatorModel extends ActiveRecord
{
    /**
     * @var array 字段属性
     */
    private $attributes = [];
    /**
     * @var array 字段显示名称
     */
    private $attributeLabels = [];
    /**
     * @var array 验证规则
     */
    private $rules = [];

    /**
     * 构造方法
     *
     * @param array $rules 验证规则
     */
    public function __construct(array $rules = [])
    {
        parent::__construct([]);
        $this->rules = $rules;
        foreach ($rules as $rule) {
            foreach ((array) $rule[0] as $key => $attribute) {
                $this->attributes[$attribute] = $attribute;
            }
        }
    }

    /**
     * 新建实例
     *
     * @param array $rules
     * @return $this
     */
    public static function new(array $rules)
    {
        return new static($rules);
    }

    /**
     * 设置或者获取字段显示名称
     *
     * @param array $labels 字段显示名称
     * @return $this
     */
    public function setAttributeLabels(array $labels = null)
    {
        $this->attributeLabels = array_merge($this->attributeLabels, $labels);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->attributeLabels;
    }

    /**
     * @inheritdoc
     * @return $this
     */
    public function load($data, $formName = null, $withDefaults = true)
    {
        parent::load($data, $formName ?? '');
        if ($withDefaults) {
            $this->loadDefaults();
        }
        return $this;
    }

    /**
     * 加载默认值
     */
    private function loadDefaults()
    {
        foreach ($this->rules as $rule) {
            // ['user', 'default', 'value' => '']
            if ('default' === $rule[1]) {
                foreach ((array) $rule[0] as $attribute) {
                    $this->{$attribute} = $this->{$attribute} ?? $rule['value'];
                }
            }
        }
    }
}
