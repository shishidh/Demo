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

$post = $_POST;

if (empty($post['id']) || empty($post['new_sort']) || empty($post['old_sort'])) {
    $return['code'] = 0;
    $return['msg'] = '參數錯誤';
    exit(json_encode($return));
}

$this_class = $db->where('id', $post['id'])->get('ad_list');

if (empty($this_class)) {
    $return['code'] = 0;
    $return['msg'] = '廣告不存在';
    exit(json_encode($return));
}

if ($post['new_sort'] > $post['old_sort']) {
    $between_class = $db->where('sort', array($post['old_sort'] - 1, $post['new_sort']), 'BETWEEN')->get('ad_list');
} else {
    $between_class = $db->where('sort', array($post['new_sort'], $post['old_sort'] - 1), 'BETWEEN')->get('ad_list');
}

$updateData = "";
foreach ($between_class as &$value) {
    if ($value['id'] != $post['id']) {
        $this_id = $value['id'];
        $this_type_id = $value['type_id'];
        $this_image_url = $value['image_url'];
        $this_real_name = $value['real_name'];
        $this_show_name = $value['show_name'];
        $this_link_url = $value['link_url'];
        $this_ad_status = $value['ad_status'];
        $this_click_number = $value['click_number'];
        $this_remark = $value['remark'];
        $this_create_time = $value['create_time'];
        if ($post['new_sort'] > $post['old_sort']) {
            $this_sort = $value['sort'] - 1;
        } else {
            $this_sort = $value['sort'] + 1;
        }
        $updateData = $updateData . "($this_id,'$this_type_id','$this_sort','$this_image_url','$this_real_name','$this_show_name','$this_link_url','$this_ad_status','$this_click_number','$this_remark','$this_create_time'),";
    }
}

$sqlStr = "replace into `ad_list` (id,type_id,sort,image_url,real_name,show_name,link_url,ad_status,click_number,remark,create_time) values $updateData";
$sql  = rtrim($sqlStr, ",");

try {
    $db->startTransaction();
    $res1 = $db->where('id', $post['id'])->update('ad_list', ['sort' => $post['new_sort']]);
    $res2 = $db->rawQuery($sql);

    if ($res1) {
        $db->commit();
        $return['code'] = 1;
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
