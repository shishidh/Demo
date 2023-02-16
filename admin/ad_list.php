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
    $get = $_GET;
    $type_text = '';
    switch ($get['type_id']) {
        case '1':
            $type_text = 'carousel_list';
            break;
        case '2':
            $type_text = 'popup_list';
            break;
        case '3':
            $type_text = 'banner_list';
            break;
        case '4':
            $type_text = 'bottom_list';
            break;

        default:
            $type_text = 'ad_list';
            break;
    }

    if ($get['type_id']) {
        $ad_type = $db->orderBy('sort', 'asc')->get('ad_class');
        $ad_list = $db->where('type_id', $get['type_id'])->orderBy('sort', 'asc')->get($type_text);

        $return['code'] = 1;
        $return['data']['ad_type'] = $ad_type;
        $return['data']['ad_list'] = $ad_list;
        exit(json_encode($return));
    } else {
        $ad_type = $db->orderBy('sort', 'asc')->get('ad_class');
        $ad_list = $db->orderBy('sort', 'asc')->get($type_text);

        $return['code'] = 1;
        $return['data']['ad_type'] = $ad_type;
        $return['data']['ad_list'] = $ad_list;
        exit(json_encode($return));
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;
    $type_text = '';
    switch ($post['type_id']) {
        case '1':
            $type_text = 'carousel_list';
            break;
        case '2':
            $type_text = 'popup_list';
            break;
        case '3':
            $type_text = 'banner_list';
            break;
        case '4':
            $type_text = 'bottom_list';
            break;

        default:
            $type_text = 'ad_list';
            break;
    }

    switch ($post['type']) {
        case 'add':
            if (empty($post['type_id']) || empty($post['sort']) || empty($post['image_url']) || empty($post['image_code']) || empty($post['real_name']) || empty($post['show_name']) || empty($post['link_url']) || empty($post['ad_status'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $has_sort = $db->where('sort', trim($post['sort']))->get($type_text);

            if ($has_sort) {
                $return['code'] = 0;
                $return['msg'] = '已有相同排序';
                exit(json_encode($return));
            }

            $insert = [
                'type_id' => $post['type_id'],
                'sort' => trim($post['sort']),
                'image_url' => trim($post['image_url']),
                'image_code' => trim($post['image_code']),
                'real_name' => trim($post['real_name']),
                'show_name' => trim($post['show_name']),
                'link_url' => trim($post['link_url']),
                'ad_status' => $post['ad_status'],
                'remark' => trim($post['remark']),
                'create_time' => date('Y-m-d H:i:s')
            ];

            try {
                $res = $db->insert($type_text, $insert);

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

            $ad_list = $db->where('id', $post['id'])->get($type_text);

            if (empty($ad_list)) {
                $return['code'] = 0;
                $return['msg'] = '廣告不存在';
                exit(json_encode($return));
            }

            try {
                $res = $db->where('id', $post['id'])->delete($type_text);

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
            } else if (empty($post['type_id']) || empty($post['sort']) || empty($post['image_url']) || empty($post['image_code']) || empty($post['real_name']) || empty($post['show_name']) || empty($post['link_url']) || empty($post['ad_status'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $this_id = $post['id'];
            $has_sort = $db->where("id != $this_id")->where('sort', trim($post['sort']))->get($type_text);

            if ($has_sort) {
                $return['code'] = 0;
                $return['msg'] = '已有相同排序';
                exit(json_encode($return));
            }

            $ad_list = $db->where('id', $post['id'])->get($type_text);

            if (empty($ad_list)) {
                $return['code'] = 0;
                $return['msg'] = '廣告不存在';
                exit(json_encode($return));
            }

            $update = [
                'type_id' => $post['type_id'],
                'sort' => trim($post['sort']),
                'image_url' => trim($post['image_url']),
                'image_code' => trim($post['image_code']),
                'real_name' => trim($post['real_name']),
                'show_name' => trim($post['show_name']),
                'link_url' => trim($post['link_url']),
                'ad_status' => $post['ad_status'],
                'remark' => trim($post['remark']),
            ];

            try {
                $res = $db->where('id', $post['id'])->update($type_text, $update);

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
