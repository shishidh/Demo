<?php
require_once('../base/connect.php');

session_unset();
session_destroy();

$return['code'] = 1;
$return['msg'] = 'η»εΊζε';
exit(json_encode($return));
