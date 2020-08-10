<?php
namespace ego\base;

/**
 * xhprof性能分析
 */
class PhpProfile extends \Globalegrow\PhpProfile\PhpProfile
{
    /**
     * @var string
     */
    public $site;
    /**
     * @var bool
     */
    public $isDebug;

    /**
     * 构造函数
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config['site'] ?? null, YII_DEBUG);
    }
}