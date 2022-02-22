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

// if (!empty($argv[1])) {
//     $shops = $sync_db->getCol("select shop_id from shop where  enabled = 1 and platform_name = 'pinduoduo' and default_facility_id = {$argv[1]}");
// }else{
//     $shops = $sync_db->getCol("select shop_id from shop where  enabled = 1 and platform_name = 'pinduoduo'");
// }

$sql = "select shop_id,pid from sync_douyin_order_info where created_time > '2021-09-26 22:00:00'";
$orders = $sync_db->getAll($sql);
foreach ($orders as $order) {
    $sql = "update sync_douyin_order_info set post_receiver = 'lw' where pid = '{$order['pid']}'";
    $sync_db->query($sql);
    $result = postJsonData('http://100.65.132.5:10314/order/downloadSingleOrder', json_encode(['platform_shop_id'=>$order['shop_id'],'platform_order_sn'=>$order['pid']]),0);
    var_dump($result);
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