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


$shops = $oms_user->getCol("select shop_id from shop where party_id = 290 and enabled = 1");

foreach ($shops as $shop) {
    $sql = "select count(distinct origin_order_id) from origin_order_goods where shop_id = {$shop} and platform_order_status = 'WAIT_SELLER_SEND_GOODS' and refund_status = 'NONE'";
    // echo $sql.PHP_EOL;
    $c1 = $oms_db->getOne($sql);
    echo $oms_user->getOne("select shop_name from shop where shop_id = ${shop}").' ';
    echo $c1.PHP_EOL;
}

$sql = "select count(distinct og.origin_order_id) from order_info o inner join order_goods og on o.order_id = og.order_id where o.party_id = 290 and o.order_status = 'WAIT_CHECK' and o.shipping_status = 'INIT' and o.refund_status = 'NONE' and o.order_change_type in ('MERGE','SPLIT')";
$c2 = $oms_db->getOne($sql);
echo $c2.PHP_EOL;


$sql = "select count(1) from order_info where party_id = 290 and order_status = 'WAIT_CHECK' and shipping_status = 'INIT' and refund_status = 'NONE' and order_change_type in ('COMMON')";
$c3 = $oms_db->getOne($sql);
echo $c3.PHP_EOL;


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