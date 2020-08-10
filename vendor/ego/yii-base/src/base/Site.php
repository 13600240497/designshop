<?php
namespace ego\base;

use yii\base\Component;

/**
 * 站点组件
 *
 * @property string $name
 */
class Site extends Component
{
    /**
     * @var string 简码
     */
    public $code = null;
    /**
     * @var array 简码与名称关系
     */
    public $code2names = [];

    /**
     * 获取站点名称
     *
     * @param string $code
     * @return string|null
     */
    public function getName($code = null)
    {
        $code = $code ?: $this->code;
        return $this->code2names[$code] ?? null;
    }
}
