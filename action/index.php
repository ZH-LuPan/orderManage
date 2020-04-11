<?php
$action = $_GET['action'];

/**
 * 页面初始化
 */
if($action && $action == 'indexInit'){
    if(empty($_COOKIE['userId']) || empty($_COOKIE['userName'])) die(json_encode(array('code'=> 302,'url'=>'./login.html')));
    $menuList = array(array(
        'url' => 'user',
        'name'=> '用户管理'
    ),array(
        'url' => 'order',
        'name'=> '订单管理'
    ));
    if($_COOKIE['userType'] == 2) {
        unset($menuList[0]);
    }
    $pageData = array(
        'userName' => $_COOKIE['userName'],
        'menuList' => $menuList
    );
    die(json_encode(array('code'=> 200,'data'=>$pageData)));
}

