<?php
define('APP_ROOT', dirname(__FILE__));
require('phpspot.class.php');
$app = new phpspot();
//虚拟目录
$app->alias('/kk/', '/page/admin/');
$app->alias('/uv/','/page/a.txt');

$app->run();
?>