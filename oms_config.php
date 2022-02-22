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

$ids = $oms_user->getCol("select DISTINCT owner_user_id from user");
foreach ($ids as $id) {
    $sql = "insert into owner_user_config_transform (owner_user_id, action, is_wms_warehouse, is_print_or_check, is_exception)
values ({$id}, 'refund', 0, 0, 1),
       ({$id}, 'refund', 0, 1, 1),
       ({$id}, 'refund', 1, 0, 1),
       ({$id}, 'refund', 1, 1, 2),
       ({$id}, 'shippingOut', 0, 0, 1),
       ({$id}, 'shippingOut', 0, 1, 1),
       ({$id}, 'shippingOut', 1, 0, 1),
       ({$id}, 'shippingOut', 1, 1, 2),
       ({$id}, 'updateNote', 0, 0, 1),
       ({$id}, 'updateNote', 0, 1, 1),
       ({$id}, 'updateNote', 1, 0, 1),
       ({$id}, 'updateNote', 1, 1, 2),
       ({$id}, 'updateAddress', 0, 0, 1),
       ({$id}, 'updateAddress', 0, 1, 1),
       ({$id}, 'updateAddress', 1, 0, 1),
       ({$id}, 'updateAddress', 1, 1, 2),
       ({$id}, 'deleted', 0, 0, 1),
       ({$id}, 'deleted', 0, 1, 1),
       ({$id}, 'deleted', 1, 0, 1),
       ({$id}, 'deleted', 1, 1, 2),
       ({$id}, 'syncPromise', 0, 0, 1),
       ({$id}, 'syncPromise', 0, 1, 1),
       ({$id}, 'syncPromise', 1, 0, 1),
       ({$id}, 'syncPromise', 1, 1, 2)";
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