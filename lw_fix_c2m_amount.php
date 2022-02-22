<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
include 'request/OrderNumberListGetRequest.php';
include 'PddClient.php';
global $oms_db;
$oms_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_0"
);
$oms_db = ClsPdo::getInstance($oms_db_conf);

global $oms_user_db;
$oms_user_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomsuser"
);
$oms_user = ClsPdo::getInstance($oms_user_db_conf);

global $oms_sync_db;
$oms_sync_db_conf = array(
    "host" => "100.65.2.110:32057",
    "user" => "mddomsapi_sync",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomssync"
);
$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);

$sql = "select origin_order_id from origin_order where shop_id in (1415,1416,1427,1432,1434) and created_time < '2021-09-13 10:40'";
$ids = $oms_db->getCol($sql);
var_dump($ids);
$order_ids=[];
foreach ($ids as $id) {
    $sql = "update origin_order set goods_amount = goods_amount/100,order_amount = order_amount/100,pay_amount = pay_amount/100 where 
    origin_order_id = {$id}";
    // echo $sql.PHP_EOL;
    // $oms_db->query($sql);
    $sql = "update origin_order_goods set price = price/100,goods_amount = goods_amount/100,shipping_amount = shipping_amount/100,discount_amount=discount_amount/100,
platform_discount=platform_discount/100,seller_discount=seller_discount/100,seller_order_discount = seller_order_discount/100,order_amount=order_amount/100,
pay_amount=pay_amount/100 where origin_order_id = {$id}";
    // echo $sql.PHP_EOL;
    // $oms_db->query($sql);

    $sql = "select order_id from order_goods where origin_order_id = ${id}";
    $order_id = $oms_db->getOne($sql);
    if (!empty($order_id)) {
        if (in_array($order_id, $order_ids)) {
            echo 'order_id:'.$order_id.' exist!'.PHP_EOL;
            continue;
        }
        $order_ids[] = $order_id;
        $sql = "update order_info set goods_amount = goods_amount/100,order_amount = order_amount/100,pay_amount = pay_amount/100 where order_id = {$order_id}";
        // echo $sql.PHP_EOL;
        // $oms_db->query($sql);
    //     $sql = "update order_goods set price = price/100,goods_amount = goods_amount/100,shipping_amount = shipping_amount/100,discount_amount=discount_amount/100,
    // platform_discount=platform_discount/100,seller_discount=seller_discount/100,seller_order_discount = seller_order_discount/100,order_amount=order_amount/100,
    // pay_amount=pay_amount/10 where order_id = {$order_id}";
     $sql = "update order_goods set 
    pay_amount=pay_amount/10 where order_id = {$order_id}";
        echo $sql.PHP_EOL;
        $oms_db->query($sql);
        // die;
    }
}


function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data))
    );
    $time_start = microtime(true);

    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    
    $result = json_decode($return_content, true);
    if(isset($result['code']) && $result['code'] == 0) {
        $str = "[ExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
    }else{
        $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
    }
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}