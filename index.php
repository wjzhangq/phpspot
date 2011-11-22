<?php
require('phpspot.class.php');
$app = new phpspot();
$app->set_app_dir(dirname(__FILE__));
//虚拟目录
//$app->alias('/kk/', '/page/admin/');
$app->alias('/uv/','/page/cat.class.php');
$app->rewrite('/^kk/', 'ff')




$app->run();
?>