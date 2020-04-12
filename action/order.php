<?php
$action = $_GET['action'];

date_default_timezone_set("PRC");
require_once '../db/Database.php';
require_once '../function.php';
$expressName = array(
    '1' => '中通快递',
    '2' => '顺丰快递',
    '3' => '申通快递',
    '4' => '圆通快递',
    '5' => '邮政快递',
);

/**
 * 添加和编辑请订单
 */
if($action && $action == 'addOrder'){
    if(empty(getLoginUserInfo('userId') || empty(getLoginUserInfo('userName')))) die('<script>window.location.href = "./login.html";</script>');
    $postArr = $_POST;
    $orderDb = new Database('order_orders');

    if(@$id = $_GET['id']){
        $orderInfo = $orderDb->where(array('id'=>$id))->find();
        if(is_array($orderInfo)){
            $orderInfo['order_date'] = date('Y-m-d',$orderInfo['order_date']);
            die(json_encode(array('code' => 200,'data' => $orderInfo)));
        }
    }
    $postArr['order_no'] = $postArr['order_no'] ? $postArr['order_no'] : createOrderNo();
    $postArr['order_date'] = strtotime($postArr['order_date']);
    $postArr['create_user_id'] = getLoginUserInfo('userId');
    $postArr['create_user_name'] = getLoginUserInfo('userName');
    $postArr['create_time'] = time();
    $postArr['update_time'] = time();
    $field = 'user_name,wechat,phone,order_date,order_no,address,right_eye_num,right_eye_light,right_eye_axle,left_eye_num,left_eye_light,left_eye_axle,eye_len,eyeglasses_frame,eyeglasses,price,service_no,description,create_user_id,create_user_name,create_time,update_time';

    if(empty($postArr['id'])) unset($postArr['id']);
    $field = implode(',',array_keys($postArr));

    if(isset($postArr['id']) && $postArr['id']){
        $result = $orderDb->where(array('id' => $postArr['id']))->fields($field)->update($postArr);
    }else{
        unset($postArr['id']);
        $result = $orderDb->fields($field)->add($postArr);
    }
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'操作成功')));
    }else{
        die(json_encode(array('code'=>400,'msg'=>'操作失败')));
    }
}

/**
 * 获取订单
 */
if($action && $action == 'getOrder'){
    $db = new Database('order_orders');
    $page = $_GET['page'];
    $size = $_GET['limit'];
    $sort = @$_GET['sort'];
    $postArr = $_POST;
    $id = isset($_GET['id']) ? $_GET['id']['id'] : '';
    $user_name = isset($_GET['user_name']) ? $_GET['user_name']['user_name'] : '';
    $phone = isset($_GET['phone']) ? $_GET['phone']['phone'] : '';
    $order_date = (isset($_GET['order_date']['order_date']) && $_GET['order_date']['order_date']) ? explode(' - ',$_GET['order_date']['order_date']) : [];
    $create_user_id = isset($_GET['create_user_id']) ? $_GET['create_user_id']['create_user_id'] : '';
    $status = isset($_GET['status']) ? $_GET['status']['status'] : '';

    $where = '`status` in (1,3)';
    if(getLoginUserInfo('userType') == 2){
        $where .= " and `create_user_id` = ".getLoginUserInfo('userId');
    }

    $order = 'id desc';
    $sort == 'id' && $order = 'id asc';

    $id && $where .= ' and `id` = '.$id;
    $user_name && $where .= ' and `user_name` like '.'"%'.$user_name.'%'.'"';
    $phone && $where .= ' and `phone` like '.'"%'.$phone.'%'.'"';
    $order_date && $where .= ' and `order_date` between '.strtotime($order_date[0]) .' and ' .@strtotime($order_date[1]);
    $create_user_id && $where .= ' and `create_user_id` = '.$create_user_id;
    $status && $where .= ' and `status` = '.$status;
    $totalCount = $db->wheres($where)->counts();
//    print_r($where);die;
    $result = $db->wheres($where)->order($order)->limit(($page-1)*$size,$size)->select();
    if(is_array($result)) foreach ($result as $key => $value){
        $result[$key]['editUrl'] = './index.html?module=addLeave&id='.$value['id'];
        $result[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        $result[$key]['order_date'] = date('Y-m-d',$value['order_date']);
    }
    die(json_encode(array('code'=>0,'data'=>$result,'count' => $totalCount)));
}

/**
 * 完成订单
 */
if($action && $action == 'done'){
    $db = new Database('order_orders');
    $postArr = $_POST;
    $result = $db->where(array('id'=> $postArr['id']))->fields('express_no,express_name,update_time,status')->update(array(
        'express_no' => $postArr['express_no'],
        'express_name' => $postArr['express_name'],
        'update_time' => time(),
        'status'      => 3
    ));
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'操作成功')));
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常')));
    }
}



/**
 * 删除订单
 */
if($action && $action == 'delete'){
    $db = new Database('order_orders');
    $result = $db->where(array('id'=> $_POST['id']))->update(array(
        'delete_time' => time(),
        'status'      => 2,
        'delete_user_id' => getLoginUserInfo('userId'),
        'delete_user_name' => getLoginUserInfo('userName')
    ));
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'删除成功')));
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常')));
    }
}

/**
 * 订单详情
 */
if($action && $action == 'orderInfo'){
    $db = new Database('order_orders');
    $id = @$_POST['id'];
    $orderInfo = $db->wheres('`id` ='.$id)->find();
    if(is_array($orderInfo)){
        $orderInfo['order_date'] = date('Y-m-d',$orderInfo['order_date']);
        $orderInfo['order_num'] = create_order_num($orderInfo['id']);
        die(json_encode(array('code'=>200,'data'=>$orderInfo)));
    }
}


/**
 * 导出订单
 */
if($action && $action == 'export'){
    $db = new Database('order_orders');
    $ids = $_GET['id'];
    $data = $db->wheres('`id` in ('.$ids.') ')->select();
    if($data && is_array($data))foreach ($data as $key => $val){
        $data[$key]['order_date'] = date('Y-m-d',$val['order_date']);
    }
    $fileName= date('YmdHis').time();
    $headerArr = array(
        array('field' => 'id',  'name' => '序号', 'width' => '20'),
        array('field' => 'order_no', 'name' => '订单编号', 'width' => '20'),
        array('field' => 'user_name',     'name' => '用户名', 'width' => '20'),
        array('field' => 'phone',  'name' => '电话号码', 'width'=>'20'),
        array('field' => 'wechat','name' => '微信','width' => '20'),
        array('field' => 'order_date', 'name' => '订单日期','width' => '20'),
        array('field' => 'address', 'name' => '收货地址','width' => '60'),
        array('field' => 'express_no', 'name' => '快递单号','width' => '20'),
        array('field' => 'express_name', 'name' => '快递名称','width' => '20'),
        array('field' => 'left_eye_num', 'name' => '左眼度数','width' => '20'),
        array('field' => 'left_eye_light', 'name' => '左眼散光','width' => '20'),
        array('field' => 'left_eye_axle', 'name' => '左眼轴位','width' => '20'),
        array('field' => 'right_eye_num', 'name' => '右眼度数','width' => '20'),
        array('field' => 'right_eye_light', 'name' => '右眼散光','width' => '20'),
        array('field' => 'right_eye_axle', 'name' => '右眼轴位','width' => '20'),
        array('field' => 'eye_len', 'name' => '瞳距','width' => '20'),
        array('field' => 'eyeglasses_frame', 'name' => '镜架','width' => '20'),
        array('field' => 'eyeglasses', 'name' => '镜片','width' => '20'),
        array('field' => 'price', 'name' => '价格','width' => '20'),
        array('field' => 'description', 'name' => '描述','width' => '40')
    );
    $sheetArr = array(
        array(
            'dataList' => $data,
            'titleArr' => [],
            'groupName' => 'Sheet1',
            'headerArr' => $headerArr
        )
    );
    include_once '../helper/PHPExcel.php';
    $phpExcel = new \Helpers\PHPExcel();
    $phpExcel->exportExcel($fileName, $sheetArr);

}

/**
 * 订单查询
 */
if($action && $action == 'search'){
    $db = new Database('order_orders');
    $imageDb = new Database('order_images');
    $postArr = $_POST;
    if(empty($postArr['phone'])) die(json_encode(array('code'=>400,'msg'=>'手机号码不能为空')));
    $orderList = $db->wheres('`phone` = "'.$postArr['phone'].'"')->select();
    if($orderList && is_array($orderList)) foreach($orderList as $key => $value){
        $order_img = $imageDb->wheres('`order_id` = '.$value['id'])->fields('url')->find();
        $orderList[$key]['statusStr'] = $value['express_no'] ? '已完成' : '制作中';
        $orderList[$key]['order_image'] = $order_img['url'];
    }
    if(count($orderList) == 0) die(json_encode(array('code'=>400,'msg'=>'抱歉,还没有您的订单信息!')));
    die(json_encode(array('code'=>200,'data'=>$orderList)));
}


/**
 * 订单打印后修改打印状态
 */
if($action && $action == 'print'){
    $db = new Database('order_orders');
    $orderDb = new Database('order_orders');
    $postArr = $_POST;
    $result = $db->where(array('id'=> $postArr['id']))->fields('is_print')->update(array(
        'is_print'   => 1  
    ));
    if(is_numeric($result) && $result){
        die(json_encode(array('code'=>200,'msg'=>'操作成功')));
    }else{
        die(json_encode(array('code'=>500,'msg'=>'系统异常')));
    }
}

/**
 * 导入
 */
if($action && $action == 'import'){
    if ( $files = $_FILES ) {
        $type = $_POST['type'];
        $baseUrl = BASE_PATH.'/public/upload/excel/';
        $name = $files["file"]["name"];
        move_uploaded_file($files["file"]["tmp_name"],$baseUrl.$name);
        $fileName = $baseUrl . $name;
        if (!file_exists($fileName)) {
            exit("文件" . $fileName . "不存在");
        }
        $orderDb = new Database('order_orders');
        $sheetSelected = 0; $dataArr = array(); $result = 1;
        require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileName);
        $objPHPExcel->setActiveSheetIndex($sheetSelected);
        $rowCount = $objPHPExcel->getActiveSheet()->getHighestRow();
        $columnCount = $objPHPExcel->getActiveSheet()->getHighestColumn();
        for ($row = 1; $row <= $rowCount; $row++) {
            for ($column = 'A'; $column <= $columnCount; $column++) {
                $dataArr[] = $objPHPExcel->getActiveSheet()->getCell($column . $row)->getValue();
            }
        }
        if (file_exists($fileName)) {
            $fileName = rtrim((substr($fileName, 0, 2) == './' ? BASE_PATH . substr($fileName, 1) : $fileName), '/');
            chmod($baseUrl, 0777);
            @unlink(str_replace('/', '\\', $fileName));
        }

        if(in_array($type,[1,2,3,4,5])){
            if (is_array($dataArr)) foreach ($dataArr as $key => $value) {
                if ($key <= 1) unset($dataArr[$key]);
            }
        }
        $newData = array_chunk(array_values($dataArr), 2);
        if(is_array($newData)) foreach ($newData as $key => $value) {
            $result = $orderDb->wheres('`id` = '.$value[0])
                ->fields('express_no,express_name,update_time,status')
                ->update(array(
                    'express_no' => $value[1],
                    'express_name' => $expressName[$type],
                    'update_time' => time(),
                    'status'      => 3
                ));
        }
        if(is_numeric($result)){
            die(json_encode(array('code'=>200,'msg'=>'操作成功')));
        }else{
            die(json_encode(array('code'=>500,'msg'=>'系统异常')));
        }
    }
}

