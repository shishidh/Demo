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
            if (empty($post['type_id']) || empty($post['sort']) || empty($post['image_url']) || empty($post['real_name']) || empty($post['show_name']) || empty($post['link_url']) || empty($post['ad_status'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
                exit(json_encode($return));
            }

            $insert = [
                'type_id' => $post['type_id'],
                'sort' => trim($post['sort']),
                'image_url' => trim($post['image_url']),
                'real_name' => trim($post['real_name']),
                'show_name' => trim($post['show_name']),
                'link_url' => trim($post['link_url']),
                'ad_status' => $post['ad_status'],
                'remark' => trim($post['remark']),
                'create_time' => date('Y-m-d H:i:s'),
                'modify' => 1
            ];

            try {
                $addRes = $db->insert($type_text, $insert);

                if (!$addRes) {
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
                $deleteRes = $db->where('id', $post['id'])->delete($type_text);

                if (!$deleteRes) {
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
            } else if (empty($post['type_id']) || empty($post['sort']) || empty($post['image_url']) || empty($post['real_name']) || empty($post['show_name']) || empty($post['link_url']) || empty($post['ad_status'])) {
                $return['code'] = 0;
                $return['msg'] = '尚有項目未填寫';
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
                'real_name' => trim($post['real_name']),
                'show_name' => trim($post['show_name']),
                'link_url' => trim($post['link_url']),
                'ad_status' => $post['ad_status'],
                'remark' => trim($post['remark']),
                'modify' => 1
            ];

            try {
                $editRes = $db->where('id', $post['id'])->update($type_text, $update);

                if (!$editRes) {
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

    if ($post['type'] == 'edit') {
        if ($ad_list['sort'] > trim($post['sort'])) {
            $between_list = $db->orderBy('sort', 'asc')->orderBy('modify', 'desc')->get($type_text);
        } else {
            $between_list = $db->orderBy('sort', 'asc')->orderBy('modify', 'asc')->get($type_text);
        }
    } else {
        $between_list = $db->orderBy('sort', 'asc')->orderBy('modify', 'desc')->get($type_text);
    }

    $updateData = "";
    foreach ($between_list as $key => $value) {
        $this_id = $value['id'];
        $this_type_id = $value['type_id'];
        $this_sort = ($key + 1);
        $this_image_url = $value['image_url'];
        $this_real_name = $value['real_name'];
        $this_show_name = $value['show_name'];
        $this_link_url = $value['link_url'];
        $this_ad_status = $value['ad_status'];
        $this_click_number = $value['click_number'];
        $this_remark = $value['remark'];
        $this_create_time = $value['create_time'];
        $this_modify = 0;
        $updateData = $updateData . "($this_id,'$this_type_id','$this_sort','$this_image_url','$this_real_name','$this_show_name','$this_link_url','$this_ad_status','$this_click_number','$this_remark','$this_create_time','$this_modify'),";
    }

    $sqlStr = "replace into `$type_text` (id,type_id,sort,image_url,real_name,show_name,link_url,ad_status,click_number,remark,create_time,modify) values $updateData";
    $sql  = rtrim($sqlStr, ",");

    try {
        $db->startTransaction();
        $sortRes = $db->rawQuery($sql);

        if (is_array($sortRes)) {
            $db->commit();
            $return['code'] = 1;
            if ($addRes) {
                $return['msg'] = '新增成功';
            } else if ($deleteRes) {
                $return['msg'] = '刪除成功';
            } else if ($editRes) {
                $return['msg'] = '編輯完成';
            }
            exit(json_encode($return));
        } else {
            $db->rollback();
            $return['code'] = 0;
            $return['msg'] = $db->getLastError();
            exit(json_encode($return));
        }
    } catch (\Exception $e) {
        $return['code'] = 0;
        $return['msg'] = $e;
        exit(json_encode($return));
    }
}
