<?php
require_once ('../base/connect.php');

// code = 2  一率導回登入頁
if (isset($_SESSION['username'])) {
    $user = $db->where('username', $_SESSION['username'])->get('user_list');

    if (empty($user)) {
        session_unset();
        session_destroy();

        $return['code'] = 2;
        $return['msg'] = '請重新登入';
        exit(json_encode($return));
    } else {
        // 更新登入資訊
        $update['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $update['login_time'] = date('Y-m-d H:i:s');
        $res = $db->where('username', $_SESSION['username'])->update('user_list', $update);

        $return['code'] = 1;
        $return['msg'] = '歡迎回來，再次自動登錄';
        $return['user'] = $_SESSION['username'];
        exit(json_encode($return));
    }
} else {
    $post = $_POST;

    if (empty($post['username']) || empty($post['password'])) {
        $return['code'] = 0;
        $return['msg'] = '尚有項目未填寫';
        exit(json_encode($return));
    }

    $user = $db->where('username', $post['username'])->where('password', md5($post['password']))->get('user_list');

    if (empty($user)) {
        $return['code'] = 0;
        $return['msg'] = '帳戶或密碼錯誤';
        exit(json_encode($return));
    }

    // 更新登入資訊
    $update['login_ip'] = $_SERVER['REMOTE_ADDR'];
    $update['login_time'] = date('Y-m-d H:i:s');
    $res = $db->where('username', $post['username'])->where('password', md5($post['password']))->update('user_list', $update);

    $_SESSION['username'] = $post['username'];
    $_SESSION['password'] = md5($post['password']);

    $return['code'] = 1;
    $return['msg'] = '登入成功';
    $return['user'] = $_SESSION['username'];
    exit(json_encode($return));
}
