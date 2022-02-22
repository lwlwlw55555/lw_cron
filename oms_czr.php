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



$sql = "select o.order_id,o.order_sn,o.party_id from order_info o
inner join shipment s on o.order_id = s.shipment_id
where o.shipping_status = 'SHIPPED' and s.is_print_tracking = 0
and o.order_change_type = 'SPLIT'
and o.created_time > '2021-07-15'
";
$orders = $oms_db->getAll($sql);
foreach ($orders as $order) {
    $sql = "select origin_order_id from order_goods where order_id = {$order['order_id']}";
    $origin_order_id = $oms_db->getOne($sql);
    if (!empty($origin_order_id)) {
        $sql = "select 1 from order_goods og inner join shipment s on og.order_id = s.shipment_id
where og.origin_order_id = {$origin_order_id} and s.is_print_tracking = 1";
        if ($oms_db->getOne($sql)) {
            echo '--'.$order['order_sn'].'--'.$oms_db->getOne("select platform_order_sn from origin_order where origin_order_id = {$origin_order_id}").'--'.$oms_user->getOne("select user_name from user where party_id = {$order['party_id']}").PHP_EOL;
        }
    }
    
}


function ($url, $data) {
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