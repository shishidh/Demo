<?php
require_once('../base/connect.php');

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

if (empty($post['ad_id'])) {
    $return['code'] = 0;
    $return['msg'] = '參數錯誤';
    exit(json_encode($return));
}

$now_time = new DateTime();
$tw_time = new DateTime('+1 day');
$ad_click = $db->where('ad_id', $post['ad_id'])->where('click_time', array($now_time->format('Y-m-d'), $tw_time->format('Y-m-d')), 'BETWEEN')->getOne("click_list",  "*");
$ad_list = $db->where('id', $post['ad_id'])->getOne($type_text,  "*");

if ($ad_click) {
    $update1 = [
        'click_number' => $ad_click['click_number'] + 1,
        'real_name' => $post['real_name'],
        'show_name' => $post['show_name'],
    ];
    $update2['click_number'] = $ad_list['click_number'] + 1;

    $db->startTransaction();
    $res1 = $db->where('ad_id', $post['ad_id'])->where('click_time', $now_time->format('Y-m-d'))->update('click_list', $update1);
    $res2 = $db->where('id', $post['ad_id'])->update($type_text, $update2);

    if (!$res1 && !$res2) {
        $db->rollback();
    } else {
        $db->commit();
    }
} else {
    $insert = [
        'ad_id' => $post['ad_id'],
        'click_time' => date('Y-m-d'),
        'real_name' => $post['real_name'],
        'show_name' => $post['show_name'],
        'click_number' => 1,
    ];
    $update['click_number'] = $ad_list['click_number'] + 1;

    $db->startTransaction();
    $res1 = $db->insert('click_list', $insert);
    $res2 = $db->where('id', $post['ad_id'])->update($type_text, $update);

    if (!$res1 && !$res2) {
        $db->rollback();
    } else {
        $db->commit();
    }
}
