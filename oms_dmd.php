<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
include 'request/LogisticsOnlineSendRequest.php';
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


$sql = "
select * from order_info o inner join order_goods og on o.order_id = og.order_id
where o.order_status = 'CLOSED_SYSTEM' and o.party_id =2 and o.created_time > '2021-10-01'
";

$orders = $oms_db->getAll($sql);
// var_dump(array_diff($pre_ids,$ids));
foreach ($orders as $order) {
    if (!empty($order['platform_order_sn'])) {
   $sql = "select 1 from order_goods og inner join order_info o on og.order_id = o.order_id where o.order_status <> 'CLOSED_SYSTEM'
    and og.platform_order_sn = '{$order['platform_order_sn']}'";
    // echo $sql.PHP_EOL;
   if (!$oms_db->getOne($sql)) {
        echo $order['platform_order_sn'].PHP_EOL;
        $sql = "update order_info set order_status = 'WAIT_CHECK' where order_id = {$order['order_id']}";
        echo $sql.PHP_EOL;
        $oms_db->query($sql);
        // die;
   }
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
 