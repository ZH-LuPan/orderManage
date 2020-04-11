<?php
$action = $_GET['action'];

require_once '../db/Database.php';
require_once '../function.php';

/**
 * 查询订单图片
 */
if($action && $action == 'get'){
    $order_id = $_POST['id'];
    $imageDb = new Database('order_images');
    $imageList = $imageDb->fields('id,url,image_width,image_height,order_id')->wheres('`status` = 1 and `order_id` = '.$order_id)->select();
    if(is_array($imageList)){
        die(json_encode(array('code' => 200 ,'data' => $imageList)));
    }else{
        die(json_encode(array('code' => 500 ,'data' => '系统异常')));
    }
}


/**
 * 上传订单图片
 */
if($action && $action == 'upload'){
    $files = $_FILES;
    $id = $_POST['id'];
    $extendName = substr($files['file']['name'], strrpos($files['file']['name'], '.')+1);
    $allowExtend = ['jpg','gif','png','jpeg'];
    if(in_array(strtolower($extendName),['jpg','gif','png','jpeg'])) $filePath = '/public/upload/images/';

    if(in_array(strtolower($extendName),$allowExtend) === false){
        die(json_encode(array('code'=>400,'msg'=>'不支持此类型文件')));
    }
    if($files && $files['file']['error'] == 0){

        $tmp = pathinfo($files['file']['name']);
        $newName = $tmp['filename'].rand(1000000,9999999).'.'.$tmp['extension'];
        $fullPath = BASE_PATH.$filePath;
        if(!is_dir($fullPath)){
            mkdir($fullPath,0777);
        }
        if(move_uploaded_file($files['file']['tmp_name'], $fullPath.$newName)){
            $image_info = getimagesize($fullPath.$newName);
            update_order_image($id,$filePath.$newName,$image_info);
            die(json_encode(array('code'=>200,'msg'=>'上传成功','data'=>$filePath.$newName)));
        }else{
            die(json_encode(array('code'=>500,'msg'=>'系统异常,稍后重试')));
        }
    }else{
        $info = '文件上传失败';
        switch($_FILES['myFile']['error']){
            case 1:
                $info = '上传文件超过php.ini中upload_max_filesize配置参数';
                break;
            case 2:
                $info = '上传文件超过表单MAX_FILE_SIZE选项指定的值';
                break;
            case 3:
                $info = '文件只有部份被上传';
                break;
            case 4:
                $info = '没有文件被上传';
                break;
            case 5:
                $info = '上传文件大小为0';
                break;
        }
        die(json_encode(array('code'=>400,'msg'=>$info)));
    }
}


/**
 * 获取订单图片
 */
if($action && $action === 'orderImage'){
    $order_id = $_POST['order_id'];
    $imageDb = new Database('order_images');
    $orderImage = $imageDb->wheres('`order_id` = '.$order_id)->select();
    if(is_array($orderImage)) foreach ($orderImage as $key => $value){
        $orderImage[$key]['url'] ='/orderManage'.$value['url'];
    }
    if(is_array($orderImage)){
        die(json_encode(array('code' => 200 ,'data' => $orderImage)));
    }else {
        die(json_encode(array('code' => 500, 'data' => '系统异常')));
    }
}



