<?php

date_default_timezone_set("PRC");
define('BASE_PATH',__DIR__);

function createOrderNo()
{
    return date('YmdHis').rand(1000,9999);
}


function getLoginUserInfo($field)
{
    return $_COOKIE[$field];
}

function getOrderStatus()
{
    return '已完成';
}

function update_order_image($orderId,$imageUrl,$imageInfo)
{
    $imageDb = new Database('order_images');
    $orderDb = new Database('order_orders');
    $imageData = array(
        'order_id' => $orderId,
        'url' => $imageUrl,
        'image_height' => $imageInfo[1],
        'image_width' => $imageInfo[0],
        'create_time' => time(),
        'update_time' => time()
    );
    $result = $imageDb->fields('order_id,url,image_height,image_width,create_time,update_time')->add($imageData);
    if(is_numeric($result) && $result){
        $orderDb->wheres('`id` = '.$orderId)->fields('has_image')->update(array('has_image'=>1));
        return array('code'=>200,'msg'=>'操作成功');
    }else{
        return array('code'=>400,'msg'=>'操作失败');
    }
}

function create_order_num($order_no)
{
    $str = '000000000000000000';
    $len = strlen($order_no);
    return substr($str,0,(7-$len)).$order_no;
}


//对象转换成数组的方法
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    } if(is_array($array)) {
        foreach($array as $key=>$value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
};
