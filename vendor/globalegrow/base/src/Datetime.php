<?php
namespace Globalegrow\Base;

/**
 * 时间组件
 *
 * @property \DateTimeImmutable $cn
 * @property \DateTimeImmutable $us
 * @property \DateTimeImmutable $utc
 */
class Datetime extends Component
{
    /**
     * 时区
     *
     * 键为别名，值为时区
     * 默认：
     * ```php
     *  [
     *      'cn' => 'asia/shanghai'
     *      'us' => 'America/Chicago',
     *      'utc' => 'UTC',
     *  ]
     * ```
     * @var array
     */
    public $timezones = [];
    /**
     * @var \DateTimeImmutable[] 时间对象
     */
    protected $datetimes = [];

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return isset($this->datetimes[$name]) ? $this->get($name) : parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->timezones = array_merge(
            [
                'cn' => 'asia/shanghai',
                'us' => 'America/Chicago',
                'utc' => 'UTC',
            ],
            $this->timezones
        );
        $this->datetimes = $this->timezones;
    }

    /**
     * 获取时间
     *
     * @param string $timezone
     * @return \DateTimeImmutable|string
     */
    public function get($timezone)
    {
        if (!isset($this->datetimes[$timezone])
            || is_string($this->datetimes[$timezone])
        ) {
            $timezone = isset($this->datetimes[$timezone]) ? $this->datetimes[$timezone] : $timezone;
            $this->datetimes[$timezone] = new \DateTimeImmutable(
                'now',
                new \DateTimeZone($timezone)
            );
        }
        return $this->datetimes[$timezone];
    }
}
