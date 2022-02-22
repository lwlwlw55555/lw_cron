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

$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 2000){
    $is_begin = false;
    $sql = "select order_id from order_info where order_type in ('RESHIP','CHANGE') and created_time > '2021-08-01' and create_from = 'SYNC' limit 2000";
    $ids = $oms_db->getCol($sql);
    if (!empty($ids)) {
        $sql = "update order_info set create_from = 'MANUAL',last_updated_time=last_updated_time where order_id in (".implode(",", $ids).")";
        echo $sql.PHP_EOL;
        $oms_db->query($sql);
        die;
    }
    $count = count($ids);
}

$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 2000){
    $is_begin = false;
    $sql = "select ditinct order_id from order_info o inner join order_goods og on o.order_id = og.order_id where og.platform_name = 'MANUAL' and created_time > '2021-08-01' and create_from = 'SYNC' limit 2000";
    $ids = $oms_db->getCol($sql);
    if (!empty($ids)) {
        $sql = "update order_info set create_from = 'MANUAL',last_updated_time=last_updated_time where order_id in (".implode(",", $ids).")";
        echo $sql.PHP_EOL;
        $oms_db->query($sql);
    }
    $count = count($ids);
}


$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 2000){
    $is_begin = false;
    $sql = "select origin_order_id from origin_order where order_type in ('RESHIP','CHANGE') and created_time > '2021-08-01' and create_from = 'SYNC'
            union 
            select origin_order_id from origin_order where platform_name = 'MANUAL' and created_time > '2021-08-01' and create_from = 'SYNC'
            limit 2000";
    $ids = $oms_db->getCol($sql);
    if (!empty($ids)) {
        $sql = "update origin_order set create_from = 'MANUAL',last_updated_time=last_updated_time where origin_order_id in (".implode(",", $ids).")";
        echo $sql.PHP_EOL;
        $oms_db->query($sql);
    }
    $count = count($ids);
}

