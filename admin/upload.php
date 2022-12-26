<?php
require_once('../base/connect.php');

// code = 2  一率導回登入頁
if (!isset($_SESSION['username'])) {
    $return['code'] = 2;
    $return['msg'] = '請重新登入';
    exit(json_encode($return));
} else {
    $user = $db->where('username', $_SESSION['username'])->get('user_list');

    if (empty($user)) {
        session_unset();
        session_destroy();

        $return['code'] = 2;
        $return['msg'] = '請重新登入';
        exit(json_encode($return));
    }
}

if (empty($_FILES['image'])) {
    $return['code'] = 0;
    $return['msg'] = '尚未上傳圖片';
    exit(json_encode($return));
}

$img_name = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];
$filepath = dirname(dirname(__FILE__)) . '/file/';

if (move_uploaded_file($tmp, $filepath . $img_name)) {
    $return['code'] = 1;
    $return['msg'] = '上傳成功';
    $return['src'] = $_SERVER['SERVER_NAME'] . '/file/' . $img_name;
    exit(json_encode($return));
} else {
    $return['code'] = 0;
    $return['msg'] = '上傳失敗';
    exit(json_encode($return));
}
