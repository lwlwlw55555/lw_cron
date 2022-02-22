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



$sql = "select distinct oo.platform_order_sn,pm.platform_shipping_id,o.tracking_number,s.access_token from order_info o
inner join order_goods og on og.order_id = o.order_id
inner join origin_order oo on oo.origin_order_id = og.origin_order_id
inner join origin_order_goods oog on oog.origin_order_id = oo.origin_order_id
inner join mddomsuser.sync_platform_shipping_mapping pm on pm.system_shipping_id = o.shipping_id and pm.platform_name = 'pinduoduo'
inner join mddomsuser.shop s on oo.shop_id = s.shop_id
 where 
 oo.platform_name = 'PINDUODUO' and
 oog.platform_order_status = 'WAIT_SELLER_SEND_GOODS' and oog.refund_status = 'NONE' and 
 o.shipping_status = 'SHIPPED' and o.created_time > '2021-08-07'";
$orders = $oms_db->getAll($sql);
var_dump($orders);
foreach ($orders as $order) {
   checkToken($order);
 } 

function _($url, $data) {
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

function checkToken($order){
        global $pdd_new_app_config;
        $pddClient = null;
        // if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        //     $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
        // }else{
        $token = $order['access_token'];
        $pddClient = new PddClient('94b911bd020f4973b4e8fd3e1b2963f8','a074b4b4dc16ff009faca99608a64acdad373cae',$token);
        // }
        $request = new LogisticsOnlineSendRequest();
        $request->setOrderSn($order['platform_order_sn']);
        $request->setTrackingNumber($order['tracking_number']);
        $request->setLogisticsId($order['platform_shipping_id']);

        var_dump($order);
        $result = $pddClient->execute($request);

        if (isset($result->error_code)) {
            echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
            return false;
        }else if(isset($result->mall_name)){
            return true;
        }
        echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
        return false;
    }
