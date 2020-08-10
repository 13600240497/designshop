<?php
/**
 * phpunit单元测试
 */
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'code' => 0,
    'message' => 'success',
    'data' => $_SERVER,
]);
