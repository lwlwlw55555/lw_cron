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

// $orders = $oms_sync_db->getAll("select * from sync_douyin_order_info where last_updated_time > '2021-10-25 0'");
$orders = $oms_sync_db->getAll("select * from sync_douyin_order_info where province_name in ('北京市','天津市','上海市','重庆市') and created_time > '2021-11-01'  and order_status = 2 order by created_time desc");

foreach ($orders as $order) {
    // if (strpos($order['refund_map'], '"4"')) {
    //     echo $order['refund_map'].PHP_EOL;
    //     $sql = "update sync_douyin_order_info set town_name = 'lw' where pid = '{$order['pid']}'";
    //     echo $sql.PHP_EOL;
    //     $oms_sync_db->query($sql);
    //     $result = postJsonData('http://100.65.130.195:11353/order/downloadSingleOrder', json_encode(['platform_shop_id'=>$order['shop_id'],'platform_order_sn'=>$order['pid']]),0);
    // }

     // echo $order['refund_map'].PHP_EOL;
        $sql = "update sync_douyin_order_info set town_name = 'lw' where pid = '{$order['pid']}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
        $result = postJsonData('http://100.65.130.195:11353/order/downloadSingleOrder', json_encode(['platform_shop_id'=>$order['shop_id'],'platform_order_sn'=>$order['pid']]),0);
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