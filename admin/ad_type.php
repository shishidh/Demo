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
    $ad_type = $db->orderBy('sort', 'asc')->get('ad_class');
    foreach ($ad_type as &$value) {
        $value['ad_number'] = $db->where('type_id', $value['id'])->getValue('ad_list', 'count(*)');
    }
    $return['code'] = 1;
    $return['data'] = $ad_type;
    exit(json_encode($return));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;

    switch ($post['type']) {
        case 'add':
            if (empty($post['type_name']) || empty($post['sort'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $has_sort = $db->where('sort', trim($post['sort']))->get('ad_class');

            if ($has_sort) {
                $return['code'] = 0;
                $return['msg'] = '已有相同排序';
                exit(json_encode($return));
            }

            $insert = [
                'type_name' => trim($post['type_name']),
                'sort' => trim($post['sort'])
            ];

            try {
                $res = $db->insert('ad_class', $insert);

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

            $ad_class = $db->where('id', $post['id'])->get('ad_class');
            $ad_list = $db->where('type_id', $post['id'])->get('ad_list');

            if (empty($ad_class)) {
                $return['code'] = 0;
                $return['msg'] = '廣告不存在';
                exit(json_encode($return));
            }

            if ($ad_list) {
                $return['code'] = 0;
                $return['msg'] = '分類底下尚有項目';
                exit(json_encode($return));
            }

            try {
                $res = $db->where('id', $post['id'])->delete('ad_class');

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
            } else if (empty($post['type_name']) || empty($post['sort'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $has_sort = $db->where('sort', trim($post['sort']))->get('ad_class');

            if ($has_sort) {
                $return['code'] = 0;
                $return['msg'] = '已有相同排序';
                exit(json_encode($return));
            }

            $ad_class = $db->where('id', $post['id'])->get('ad_class');

            if (empty($ad_class)) {
                $return['code'] = 0;
                $return['msg'] = '廣告不存在';
                exit(json_encode($return));
            }

            $update = [
                'type_name' => trim($post['type_name']),
                'sort' => trim($post['sort'])
            ];

            try {
                $res = $db->where('id', $post['id'])->update('ad_class', $update);

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
