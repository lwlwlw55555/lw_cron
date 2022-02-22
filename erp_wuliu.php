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

if (!empty($argv[1])) {
    $shops = $sync_db->getCol("select shop_id from shop where  enabled = 1 and platform_name = 'pinduoduo' and default_facility_id = {$argv[1]}");
}else{
    $shops = $sync_db->getCol("select shop_id from shop where  enabled = 1 and platform_name = 'pinduoduo'");
}

foreach ($shops as $shop) {
    $result = postJsonData('http://100.65.132.5:10314/order/syncShopAllOrderWithTimestamp', json_encode(['start_time'=>'2021-09-21 00:00:00','end_time'=>'2021-09-26 15:00:00','shop_id'=>strval($shop)]),0);
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