<?php
$action = $_GET['action'];

date_default_timezone_set("PRC");
require_once '../db/Database.php';


/**
 * 登录操作
 */
if($action && $action == 'login'){
    $db = new Database('order_user');

    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    $result = $db->wheres("login_name = "."'".$username."'" ."and password = "."'".$password."'"." and status = 1")->find();
    if($result['id']){
        setcookie('userName',$result['user_name']);
        setcookie('userType',$result['user_type']);
        setcookie('userId',$result['id']);
        die(json_encode(array('code' =>200,'msg' => '登录成功','url' => './index.html?module=index')));
    }else{
        die(json_encode(array('code' =>400,'msg' => '登录失败,用户名或密码错误')));
    }
}

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
    ),array(
        'url' => 'express',
        'name'=> '快递管理'
    ));
    if($_COOKIE['userType'] == 2) {    //老师用户
        $db = new Database('order_user');
        unset($menuList[0]);
    }
    $pageData = array(
        'userName' => $_COOKIE['userName'],
        'menuList' => $menuList
    );
    die(json_encode(array('code'=> 200,'data'=>$pageData)));
}



/**
 * 添加用户
 */
if($action && $action == 'addUser'){
    if(empty($_COOKIE['userId']) || empty($_COOKIE['userName'])) die('<script>window.location.href = "./login.html";</script>');
    $db = new Database('order_user');
    $postArr = $_POST;
    if(empty($postArr['user_name'])) die(json_encode(array('code'=>400,'msg'=>'用户姓名不能为空')));
    if(empty($postArr['login_name'])) die(json_encode(array('code'=>400,'msg'=>'登陆名不能为空')));
    if(empty($postArr['password'])) die(json_encode(array('code'=>400,'msg'=>'密码不能为空')));

    $postArr['user_name']  = htmlspecialchars(trim($postArr['user_name']));
    $postArr['login_name'] = htmlspecialchars(trim($postArr['login_name']));
    $postArr['phone'] = htmlspecialchars(trim($postArr['phone'])) ? : '';
    $postArr['password']   = htmlspecialchars(trim($postArr['password']));
    $postArr['description'] = htmlspecialchars(trim($postArr['description'])) ? : '';
    $postArr['create_time'] = time();
    $postArr['update_time'] = time();
    $field = 'user_name,login_name,phone,password,description,create_time,update_time';
    $result = $db->fields($field)->add($postArr);
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'添加成功','url'=>'./index.html?module=user')));
    }else{
        die(json_encode(array('code'=>400,'msg'=>'添加失败')));
    }
}

/**
 * 获取用户
 */
if($action && $action == 'getUser'){
    $postArr = $_POST;
    $db = new Database('order_user');
    $condition = "`user_type` = 2 and `status` = 1";
    if(isset($postArr['keyword']) && $postArr['keyword']) $condition .= " and `user_name` like '".'%'.$postArr['keyword'].'%'."'";
    $result = $db->wheres($condition)->select();
    die(json_encode(array('code'=>200,'data'=>$result)));
}


/**
 * 编辑用户
 */
if($action && $action == 'editUser'){
    $db = new Database('order_user');

    $postArr = $_POST;
    $postArr['create_time'] = time();
    $result = $db->where(array('id'=> $postArr['id']))->update($postArr);
    if(is_numeric($result)){
        die(json_encode(array('code'=>200,'msg'=>'修改成功')));
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常')));
    }

}

/**
 * 删除用户
 */
if($action && $action == 'delUser'){
    $db = new Database('order_user');

    $postArr = $_POST;
    $result = $db->where(array('id'=> $postArr['id']))->delete();
    if(is_numeric($result)){
        die(json_encode(array('code'=>200,'msg'=>'删除成功')));
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常')));
    }
}

/**
 * 注销
 */
if($action && $action == 'logout'){
    setcookie('userName',null);
    setcookie('userType',null);
    setcookie('userId',null);
    die('<script>window.location.href = "../login.html";</script>');
}