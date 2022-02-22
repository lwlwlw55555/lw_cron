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

$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 1000){
    $is_begin = false;
    $sql = "select origin_order_goods_id from origin_order_goods where refund_apply_time is null and refund_time is not null limit 1000";
     echo date("Y-m-d H:i:s")." sql".$sql;
    $list = $oms_db->getCol($sql);
    $count = count($list);
    $start += $count; 
    $s = rand(2*60,5*60);
    $sql = "update origin_order_goods set refund_apply_time = date_sub(refund_time,INTERVAL {$s} second) where origin_order_goods_id in (".implode(",", $list).")";
    echo $sql.PHP_EOL;
    $oms_db->query($sql);
    // die;
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