<?php
require_once('../base/connect.php');

$res = [];

$ad_class = $db->orderBy('sort', 'asc')->get('ad_class');
$ad_list = $db->where('ad_status', '1')->orderBy('sort', 'asc')->get('ad_list');
$carousel_list = $db->where('ad_status', '1')->orderBy('sort', 'asc')->get('carousel_list');
$popup_list = $db->where('ad_status', '1')->orderBy('sort', 'asc')->get('popup_list');
$banner_list = $db->where('ad_status', '1')->orderBy('sort', 'asc')->get('banner_list');
$bottom_list = $db->where('ad_status', '1')->orderBy('sort', 'asc')->get('bottom_list');

foreach ($ad_class as &$class_value) {
    $class_value['ad_list'] = [];

    foreach ($ad_list as &$list_value) {
        if ($list_value['type_id'] == $class_value['id']) {
            array_push($class_value['ad_list'], $list_value);
        }
    }
}

$res['ad_class'] = $ad_class;
$res['ad_list'] = $ad_list;
$res['carousel_list'] = $carousel_list;
$res['popup_list'] = $popup_list;
$res['banner_list'] = $banner_list;
$res['bottom_list'] = $bottom_list;

$param_list = $db->get('param_list');

foreach ($param_list as &$value) {
    $res[$value['param_name']] = $value['param_text'];
}

exit(json_encode($res));
