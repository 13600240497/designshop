<?php

$m = new Memcached();
$m->addServer('127.0.0.1', 11211);
$stats = $m->getStats()['127.0.0.1:11211'];
var_export([
    'max_connections'   => $stats['max_connections'],
    'threads'           => $stats['threads'],
    'curr_connections'  => $stats['curr_connections'],
    'total_connections' => $stats['total_connections'],
    'curr_items'        => $stats['curr_items'],
    'total_items'       => $stats['total_items'],
    'incr_misses'       => $stats['incr_misses'],
    'incr_hits'         => $stats['incr_hits'],
]);
