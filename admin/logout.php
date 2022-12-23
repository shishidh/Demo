<?php
require_once ('../base/connect.php');

session_unset();
session_destroy();

$return['code'] = 1;
$return['msg'] = '登出成功';
exit(json_encode($return));