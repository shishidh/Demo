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

$now_time = new DateTime();
$three_time = new DateTime('-3 month');

$click_list = $db->where('click_time', array($three_time->format('Y-m-d'), $now_time->format('Y-m-d')), 'BETWEEN')->get('click_list');

$return['code'] = 1;
$return['data'] = $click_list;
exit(json_encode($return));
