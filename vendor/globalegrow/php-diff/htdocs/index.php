<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>php-diff</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body style="font: Microsoft YaHei,Arial,sans-serif; font-size: 12px;">
<?php
require __DIR__ . '/../vendor/autoload.php';
$old = [
    'username' => 'foo',
    'email' => 'foo@example.com',
    'create_time' => time() - mt_rand(3600, 86400),
    'password' => '123456',
    'content' => file_get_contents(__DIR__ . '/../tests/a.txt'),
];
$new = [
    'username' => 'bar',
    'email' => 'bar@example.com',
    'create_time' => time(),
    'password' => 'xxx',
    'content' => file_get_contents(__DIR__ . '/../tests/b.txt'),
];
echo (new Globalegrow\PhpDiff\Diff())->changes2detail(
    (new Globalegrow\PhpDiff\Changes(['diffDetailFields' => 'content']))->get($old, $new),
    [
        'username' => '用户名',
        'email' => '邮箱',
        'create_time' => '添加时间',
        'password' => '密码'
    ]
);
?>
</body>
</html>
