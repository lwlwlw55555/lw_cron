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


$sql = "  select id from 
( (select max(id) id,shop_id,platform_order_sn,COUNT(1) c from origin_refund_order 
   group by shop_id,platform_order_sn 
   HAVING c > 1 ) tt)";
$ids = $oms_db->getCol($sql);
var_dump($ids);
if (!empty($ids)) {
	$sql = "delete from origin_refund_order where id in (".implode(",", $ids).")";
	echo $sql.PHP_EOL;
	$oms_db->query($sql);
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
