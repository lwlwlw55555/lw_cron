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

global $db,$sync_db;

$sql = "select oid from sync_kuaishou_order_info where shop_id = 1505 and created_time > '2021-10-01' and inner_order_status = 1 and inner_order_status = 1";
$ids = $sync_db->getCol($sql);
selectRdsByShopId(5580177);
$sql = "select order_sn from multi_goods_shipment where order_sn in ('".implode("','", $ids)."')";
$inner_ids = $db->getCol($sql);
var_dump($inner_ids);
$diff = array_diff($ids, $inner_ids);
if (!empty($diff)) {
    var_dump($diff);
    $sql = "delete from sync_kuaishou_order_info where oid in ('".implode("','", $diff)."')";
    echo $sql.PHP_EOL;
    $sync_db->query($sql);
    $sql = "delete from sync_kuaishou_order_goods where oid in ('".implode("','", $diff)."')";
    $sync_db->query($sql);
    foreach ($diff as $id) {
        $res = ExpressApiService::downloadSingleOrder(5580177,'pinduoduo',$id);
    }
}