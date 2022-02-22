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
    "name" => "mddoms_1"
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

// $is_begin = true;
// $count = 0;
// $start = 0;
// while($is_begin || $count == 2000){
//     $is_begin = false;
//     $sql = "select id from origin_transform_error_order where last_updated_time < '2021-06-01 0' and transform_level =1
//           limit 2000";
//      echo date("Y-m-d H:i:s")." sql".$sql;
//     $ids = $oms_user->getCol($sql);
//     $count = count($ids);

//     $start += $count;
//     if ($count >0) {
//         $sql = "delete from origin_transform_error_order where id in (".implode("," , $ids).")";
//     }
//     echo $sql.PHP_EOL;
//     $oms_user->query($sql);
// }

$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 2000){
    $is_begin = false;
    $sql = "select id,platform_order_sn from origin_transform_error_order 
          where transform_level =1
          limit {$start},2000";
     echo date("Y-m-d H:i:s")." sql".$sql;
    $orders = $oms_user->getAll($sql);
    $count = count($orders);

    $start += $count;
    if ($count >0) {
        foreach ($orders as $order) {

            $exist_wait = $oms_db->getOne("select 1 from origin_order o 
            inner join origin_order_goods og on o.origin_order_id = og.origin_order_id
            where o.platform_order_sn = '{$order['platform_order_sn']}' and og.platform_order_status = 'WAIT_SELLER_SEND_GOODS'
             ");
            if (empty($exist_wait)) {
                $sql = "delete from origin_transform_error_order where id = {$order['id']}";
                echo $sql.PHP_EOL;
                $oms_user->query($sql);
            }else{
                var_dump($order);
            }
        }
    }
    echo $sql.PHP_EOL;
    $oms_user->query($sql);
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