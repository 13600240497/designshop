<?php

namespace Clothing\Tools\ServiceTracing;

/**
 * 追踪行为特征处理
 *
 */
trait ActionTrait
{
    /**
     * 服务追踪开始时间
     * @var float
     */
    protected $startTimestamp = 0.0;

    /**
     * 追踪结束时间
     * @var float
     */
    protected $finishTimestamp = 0.0;

    /**
     * 异常信息
     * @var null|\Throwable
     */
    protected $exception;

    /**
     * 开始调用链追踪，什么时候开始，将影响最终的时间消耗计算
     * @return static
     */
    public function start($startTime = 0)
    {
        if ($startTime){
            $this->startTimestamp = $startTime;
        }else{
            $this->startTimestamp = microtime(true) * 1000;
        }


        return $this;
    }

    /**
     * 获取开始时间，单位为毫秒，例如：1565147281485
     * @throws \Clothing\Tools\ServiceTracing\Exception
     *
     * @return float
     */
    public function getStartTimestamp()
    {
        if (! $this->isStarted()) {
            throw new Exception('Tracing has not started!');
        }

        return $this->startTimestamp;
    }

    /**
     * 判断是否已开始追踪
     * @return bool
     */
    public function isStarted()
    {
        return $this->startTimestamp > 0;
    }

    /**
     * 结束调用链追踪
     * @throws \Clothing\Tools\ServiceTracing\Exception
     *
     * @return static
     */
    public function finish()
    {
        if (! $this->isStarted()) {
            throw new Exception('Tracing has not started!');
        }

        if (! $this->isFinished()) {
            $this->finishTimestamp = microtime(true) * 1000;
        }

        return $this;
    }

    /**
     * 获取结束时间，单位为毫秒，例如：1565147281485
     * @throws \Clothing\Tools\ServiceTracing\Exception
     *
     * @return float
     */
    public function getFinishTimestamp()
    {
        if (! $this->isFinished()) {
            throw new Exception('Tracing has not finished!');
        }

        return $this->finishTimestamp;
    }

    /**
     * 判断是否已停止追踪
     * @return bool
     */
    public function isFinished()
    {
        return $this->isStarted() && $this->finishTimestamp > 0;
    }

    /**
     * 获取从开始->结束，中间消耗的时间(毫秒)
     * @return float
     */
    public function getDuration()
    {
        if (! $this->isStarted() || ! $this->isFinished()) {
            return 0;
        }

        return round($this->getFinishTimestamp() - $this->getStartTimestamp());
    }

    /**
     * 获取当前|指定时间，距离开始时间消耗的毫秒数
     * @param null|float $timestamp
     * @return float
     */
    public function getElapsedMilliseconds($timestamp=null)
    {
        if ($timestamp === null) {
            $timestamp = $this->getFinishTimestamp();
        }

        return round($timestamp - $this->getStartTimestamp());
    }

    /**
     * 设置异常信息
     *
     * @param \Throwable $e
     * @throws \Clothing\Tools\ServiceTracing\Exception
     * @return static
     */
    public function setException($e)
    {
        if ($this->isFinished()) {
            throw new Exception('Tracing has been finished!');
        }

        if ($e instanceof \Exception) { // for php5.6
            $this->exception = $e;
        }

        if ($e instanceof \Throwable) { // for php7+
            $this->exception = $e;
        }

        return $this->finish();
    }

    /**
     * 判断是否发生异常
     * @return bool
     */
    public function hasException()
    {
        return $this->exception !== null;
    }

    /**
     * 获取异常
     * @return null|\Throwable
     */
    public function getException()
    {
        return $this->exception;
    }
}
