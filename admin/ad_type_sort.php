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

$this_class = $db->where('id', $post['id'])->get('ad_class');

if (empty($this_class)) {
    $return['code'] = 0;
    $return['msg'] = '廣告不存在';
    exit(json_encode($return));
}

try {
    $db->startTransaction();
    if ($post['new_sort'] > $post['old_sort']) {
        $res1 = $db->where('sort', $post['new_sort'])->update('ad_class', ['sort' => $post['new_sort'] - 0.1]);
    } else {
        $res1 = $db->where('sort', $post['new_sort'])->update('ad_class', ['sort' => $post['new_sort'] + 0.1]);
    }
    $res2 = $db->where('id', $post['id'])->update('ad_class', ['sort' => $post['new_sort']]);
    
    if ($res1 && $res2) {
        $db->commit();
        $between_class = $db->orderBy('sort', 'asc')->get('ad_class');

        $updateData = "";
        foreach ($between_class as $key => $value) {
            $this_id = $value['id'];
            $this_name = $value['type_name'];
            $this_sort = ($key + 1);
            $this_modify = 0;
            $updateData = $updateData . "($this_id,'$this_name','$this_sort','$this_modify'),";
        }

        $sqlStr = "replace into `ad_class` (id,type_name,sort,modify) values $updateData";
        $sql  = rtrim($sqlStr, ",");

        try {
            $db->startTransaction();
            $res3 = $db->rawQuery($sql);

            if (is_array($res3)) {
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
