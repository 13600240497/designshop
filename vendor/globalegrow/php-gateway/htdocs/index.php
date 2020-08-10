<?php
/**
 * phpunit单元测试
 */

$data = isset($_POST['content']) ? json_decode($_POST['content'], true) : null;
if (!$data) {
    $data = [
        'code' => 0,
        'message' => 'success',
        'data' => null,
    ];
}

http_response_code(isset($data['http_code']) ? $data['http_code'] : 200);
echo json_encode($data);
