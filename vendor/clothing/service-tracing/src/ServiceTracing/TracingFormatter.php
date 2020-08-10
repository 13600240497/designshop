<?php

namespace Clothing\Tools\ServiceTracing;

use Monolog\Formatter\FormatterInterface;

/**
 * 用于在 monolog 中，对日志数据进行格式化处理，以便服务治理平台收集数据
 */
class TracingFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        return $record['message'] . "\n";
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }
}
