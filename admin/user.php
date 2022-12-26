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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_list = $db->get('user_list');

    $return['code'] = 1;
    $return['data'] = $user_list;
    exit(json_encode($return));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;

    switch ($post['type']) {
        case 'add':
            if (empty($post['username']) || empty($post['password'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $insert = [
                'username' => trim($post['username']),
                'password' => trim($post['password']),
                'remark' => trim($post['remark']),
                'create_time' => date('Y-m-d H:i:s')
            ];

            try {
                $res = $db->insert('user_list', $insert);

                if ($res) {
                    $return['code'] = 1;
                    $return['msg'] = '新增成功';
                    exit(json_encode($return));
                } else {
                    $return['code'] = 0;
                    $return['msg'] = $db->getLastError();
                    exit(json_encode($return));
                }
            } catch (\Exception $e) {
                $return['code'] = 0;
                $return['msg'] = $e;
                exit(json_encode($return));
            }
            break;
        case 'delete':
            if (empty($post['id'])) {
                $return['code'] = 0;
                $return['msg'] = '參數錯誤';
                exit(json_encode($return));
            }

            $user = $db->where('id', $post['id'])->get('user_list');

            if (empty($user)) {
                $return['code'] = 0;
                $return['msg'] = '帳號不存在';
                exit(json_encode($return));
            }

            try {
                $res = $db->where('id', $post['id'])->delete('user_list');

                if ($res) {
                    $return['code'] = 1;
                    $return['msg'] = '刪除成功';
                    exit(json_encode($return));
                } else {
                    $return['code'] = 0;
                    $return['msg'] = $db->getLastError();
                    exit(json_encode($return));
                }
            } catch (\Exception $e) {
                $return['code'] = 0;
                $return['msg'] = $e;
                exit(json_encode($return));
            }
            break;
        case 'edit':
            if (empty($post['id'])) {
                $return['code'] = 0;
                $return['msg'] = '參數錯誤';
                exit(json_encode($return));
            } else if (empty($post['username']) || empty($post['password'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $user = $db->where('id', $post['id'])->get('user_list');

            if (empty($user)) {
                $return['code'] = 0;
                $return['msg'] = '帳號不存在';
                exit(json_encode($return));
            }

            $update = [
                'username' => trim($post['username']),
                'password' => trim($post['password']),
                'remark' => trim($post['remark']),
                'update_time' => date('Y-m-d H:i:s')
            ];

            try {
                $res = $db->where('id', $post['id'])->update('user_list', $update);

                if ($res) {
                    $return['code'] = 1;
                    $return['msg'] = '編輯完成';
                    exit(json_encode($return));
                } else {
                    $return['code'] = 0;
                    $return['msg'] = $db->getLastError();
                    exit(json_encode($return));
                }
            } catch (\Exception $e) {
                $return['code'] = 0;
                $return['msg'] = $e;
                exit(json_encode($return));
            }
            break;

        default:
            $return['code'] = 0;
            $return['msg'] = '參數錯誤';
            exit(json_encode($return));
            break;
    }
}
