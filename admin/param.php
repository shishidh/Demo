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
    $param_list = $db->get('param_list');

    $return['code'] = 1;
    $return['data'] = $param_list;
    exit(json_encode($return));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;

    if (empty($post['id'])) {
        $return['code'] = 0;
        $return['msg'] = '參數錯誤';
        exit(json_encode($return));
    }

    $param_list = $db->where('id', $post['id'])->get('param_list');

    if (empty($param_list)) {
        $return['code'] = 0;
        $return['msg'] = '參數不存在';
        exit(json_encode($return));
    }

    switch ($post['type']) {
        case 'delete':
            try {
                $res = $db->where('id', $post['id'])->delete('param_list');

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
            $update = [
                'remark' => trim($post['remark']),
                'param_text' => trim($post['param_text'])
            ];

            try {
                $res = $db->where('id', $post['id'])->update('param_list', $update);

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
