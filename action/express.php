<?php
$action = $_GET['action'];

date_default_timezone_set("PRC");
require_once '../db/Database.php';
require_once '../function.php';

/**
 * 添加快递管理订单
 */
if($action && $action == 'add'){
    if(empty(getLoginUserInfo('userId') || empty(getLoginUserInfo('userName')))) die('<script>window.location.href = "./login.html";</script>');
    $postArr = $_POST;
    $expressDb = new Database('order_express');
    $isHave = $expressDb->wheres('`order_id` = '.$postArr['order_id'])->find();
    if($isHave){
        die(json_encode(array('code'=>400,'msg'=>'此订单已加入订单管理')));
    }
    $postArr['create_user_id'] = getLoginUserInfo('userId');
    $postArr['create_time'] = time();
    $postArr['update_time'] = time();
    $fields = implode(',',array_keys($postArr));

    if(isset($postArr['id']) && $postArr['id']){
        $result = $expressDb->where(array('id' => $postArr['id']))->fields($fields)->update($postArr);
    }else{
        unset($postArr['id']);
        $result = $expressDb->fields($fields)->add($postArr);
    }
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'加入快递管理成功')));
    }else{
        die(json_encode(array('code'=>400,'msg'=>'操作失败')));
    }
}

/**
 * 获取快递管理
 */
if($action && $action == 'get'){
    $db = new Database('order_express');
    $page = $_GET['page'];
    $size = $_GET['limit'];
    $sort = @$_GET['sort'];
    $postArr = $_POST;
    $user_name = isset($_GET['user_name']) ? $_GET['user_name']['user_name'] : '';
    $phone = isset($_GET['phone']) ? $_GET['phone']['phone'] : '';
    $is_export = isset($_GET['is_export']) ? $_GET['is_export']['is_export'] : '';

    $where = '`status` = 1';
    if(getLoginUserInfo('userType') == 2){
        $where .= " and `create_user_id` = ".getLoginUserInfo('userId');
    }

    $order = 'id desc';
    $sort == 'id' && $order = 'id asc';

    $user_name && $where .= ' and `user_name` like '.'"%'.$user_name.'%'.'"';
    $phone && $where .= ' and `phone` like '.'"%'.$phone.'%'.'"';
    $is_export && $where .= ' and `is_export` = '. $is_export;
    $totalCount = $db->wheres($where)->counts();
    $result = $db->wheres($where)->order($order)->limit(($page-1)*$size,$size)->select();
    if(is_array($result)) foreach ($result as $key => $value){
        $result[$key]['is_export'] = $value['is_export'] == 1 ? '是' : '否';
        $result[$key]['update_time'] = $value['is_export'] == 1 ? date('Y-m-d H:i:s',$value['update_time'])  : '';
        $result[$key]['create_time'] = date('Y-m-d H:i:s',$value['update_time']);
    }
    die(json_encode(array('code'=>0,'data'=>$result,'count' => $totalCount)));
}

/**
 * 删除
 */
if($action && $action == 'delete'){
    $db = new Database('order_express');
    $postArr = $_POST;
    $result = $db->wheres('id in ('.$postArr['id'].')')->delete();
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'删除成功')));
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常')));
    }
}

/**
 * 导出
 */
if($action && $action == 'export'){
    $db = new Database('order_express');
    $ids = $_GET['id'];
    $express_type = $_GET['type'];
    $data = $db->wheres('`id` in ('.$ids.')')->select();
    $fileName = date('YmdHis').time();
    switch ($express_type){
        case 1:
            $headerArr = array(
                array('field' => 'order_id',  'name' => '订单号', 'width' => '15'),
                array('field' => '', 'name' => '代收金额', 'width' => '15'),
                array('field' => 'user_name',     'name' => '收件人姓名', 'width' => '15'),
                array('field' => 'phone',  'name' => '收件人手机', 'width'=>'15'),
                array('field' => '','name' => '收件人电话','width' => '15'),
                array('field' => 'address', 'name' => '收件人地址','width' => '70'),
                array('field' => '', 'name' => '收件人单位','width' => '30'),
                array('field' => '', 'name' => '品名','width' => '15'),
                array('field' => '', 'name' => '数量','width' => '15'),
                array('field' => '', 'name' => '买家备注','width' => '40'),
                array('field' => '', 'name' => '卖家备注','width' => '40')
            );
            $sheetArr = array( array(
                    'dataList' => $data,
                    'titleArr' => [],
                    'groupName' => 'Sheet1',
                    'headerArr' => $headerArr
            ));
            break;
    }
    //修改导出状态
    $result = $db->wheres('`id` in ('.$ids.')')
        ->fields('is_export,update_time')
        ->update(array('is_export' => 1,'update_time' => time()));
    if(is_numeric($result)){
        include_once '../helper/PHPExcel.php';
        $phpExcel = new \Helpers\PHPExcel();
        $phpExcel->exportExcel($fileName, $sheetArr);
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常'),JSON_UNESCAPED_UNICODE));
    }

}
