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

$shop_id = $argv[1];
if (empty($shop_id)) {
    die('null shop_id');
}

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

$sql = "select
            distinct o.platform_order_sn
        from
            origin_order_goods oog
            inner join origin_order o on oog.origin_order_id = o.origin_order_id
        where
            o.order_type = 'SALE' and 
            oog.shop_id = {$shop_id} and
            oog.platform_order_status in ('WAIT_BUYER_PAY', 'WAIT_SELLER_SEND_GOODS') and
            oog.refund_status in ('NONE','DELETED');";
$sys_orders = $oms_db->getCol($sql);

$result = postJsonData('http://100.65.130.195:11353/order/downloadOrder', json_encode(['shop_id'=>$shop_id]),0);
$platform_orders = $result['data']['orderSnList'];
echo 'diff_orders:'.PHP_EOL;
$diff_orders_1 = array_diff($platform_orders, $sys_orders);

var_dump($diff_orders_1);

foreach ($diff_orders_1 as $order) {
    $result = postJsonData('http://100.65.130.195:11353/order/downloadSingleOrder', json_encode(['platform_shop_id'=>$shop_id,'platform_order_sn'=>$order]),0);
    if ( strpos(json_encode($result), 'no record') !== false) {
        $sql  = "delete from sync_douyin_order_info where pid = '{$order}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
    }
    $exist = $oms_db->getOne("select 1 from origin_order where platform_order_sn = '{$order}'");
    if (!$exist) {
        $sql  = "delete from sync_douyin_order_info where pid = '{$order}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
        $sql  = "delete from sync_douyin_order_goods where pid = '{$order}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
    }
}


$diff_orders_2 = array_diff($sys_orders, $platform_orders);

var_dump($diff_orders_2);

foreach ($diff_orders_2 as $order) {
    $sql  = "update sync_douyin_order_info set remark = 'lw_test',receiver_name = 'lw',logistics_id = -1,refund_status = 1,tracking_number='lw' where order_sn = '{$order}'";
    echo $sql.PHP_EOL;
    // $oms_sync_db->query($sql);
    $result = postJsonData('http://100.65.130.195:11353/order/downloadSingleOrder', json_encode(['platform_shop_id'=>$shop_id,'platform_order_sn'=>$order]),0);
    if ( strpos(json_encode($result), 'no record') !== false) {
        $sql  = "delete from sync_douyin_order_info where order_sn = '{$order}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
    }
    $exist = $oms_db->getOne("select 1 from origin_order where platform_order_sn = '{$order}'");
    if (!$exist) {
        $sql  = "delete from sync_douyin_order_info where pid = '{$order}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
        $sql  = "delete from sync_douyin_order_goods where pid = '{$order}'";
        echo $sql.PHP_EOL;
        $oms_sync_db->query($sql);
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