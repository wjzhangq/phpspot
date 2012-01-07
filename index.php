<?php
require('phpspot.class.php');
$app = new phpspot();
$app->set_app_dir(dirname(__FILE__));

$request_path = $app->get_request_path();
/* 对$request_path 进行重写*/



$app->run($request_path);
?>