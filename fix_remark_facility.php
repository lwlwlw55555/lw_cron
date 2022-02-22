<?php
require("includes/init.php");
$url = 'http://localhost:8080/erp_syncinner_prod';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

echo date("Y-m-d H:i:s").PHP_EOL;

global $sync_db,$db;
$facility_id = null;
if (isset($argv[1])) {
    $facility_id = $argv[1];
}else{
    echo PHP_EOL.'ERROR facility_id can not be null'.PHP_EOL;
    die;
}
selectRdsByFacilityId($facility_id);

$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 2000){
    $is_begin = false;
    $sql = "select m.shop_id,m.order_sn,m.district_name,m.shipping_address,m.mobile,m.seller_note,m.buyer_note,m.receive_name,m.shipment_status,m.status from multi_goods_shipment m
          where m.shop_id<>0 and m.facility_id = {$facility_id} and m.created_time >= '2020-02-20 13:00:00' and m.shipment_status = 'WAIT_SHIP'
          limit {$start},2000";
     echo date("Y-m-d H:i:s")." sql".$sql;
     selectRdsByFacilityId($facility_id);
    $orders = $db->getAll($sql);
    $count = count($orders);
    $start += $count;
    echo 'count:'.$count.' start:'.$start.PHP_EOL;
    foreach ($orders as $order) {
        $sync_order = $sync_db->getRow("select town,address,receiver_phone,remark,customer_remark,receiver_name,order_status,refund_status from sync_pinduoduo_order_info where order_sn = '{$order['order_sn']}'");
        if (empty($sync_order)){
            $sql = "insert ignore into sync_pinduoduo_order_info (shop_id, order_sn, confirm_time, platform_created_time, logistics_id, tracking_number, shipping_time,order_status,refund_status) 
                VALUES ({$order['shop_id']},'{$order['order_sn']}','2020-04-13 13:00:00','2020-04-13 13:00:00',0,''
                    ,null,1,1)";
            echo $sql.PHP_EOL;
            $sync_db->query($sql);
            ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
        }else if (!empty($sync_order)){
            if($sync_order['refund_status'] == 1 && ($sync_order['town'] != $order['district_name'] ||
            $sync_order['receiver_name'] != $order['receive_name'] ||
            $sync_order['address'] != $order['shipping_address'] ||
            $sync_order['receiver_phone'] != $order['mobile'] ||
            $sync_order['remark'] != $order['seller_note'] ||
            $sync_order['customer_remark'] != $order['buyer_note'])) {
               echo 'sync_order:'.json_encode($sync_order).' order:'.json_encode($order).PHP_EOL; 
            $sql="update sync_pinduoduo_order_info set remark = '',customer_remark='',town='lw_test',address='lw_test',receiver_phone = '1888888888',receiver_name='lw_test'
                where order_sn = '{$order['order_sn']}'
                ";
                echo date("Y-m-d H:i:s")."sync_update_sql".$sql;
            $sync_db->query($sql);
            ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
        }
         if ($sync_order['order_status'] > 1 && $order['shipment_status'] == 'WAIT_SHIP') {
                $sql="update sync_pinduoduo_order_info set order_status = 1,tracking_number='111'
                    where order_sn = '{$order['order_sn']}'
                    ";
                    echo date("Y-m-d H:i:s")."sync_update_sql".$sql;
                $sync_db->query($sql);
                ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
        }
         if ($sync_order['refund_status'] == 1 && $order['status'] == 'STOP') {
                $sql="update sync_pinduoduo_order_info set refund_status = 2
                where order_sn = '{$order['order_sn']}'
                ";
                echo date("Y-m-d H:i:s")."sync_update_sql".$sql;
                $sync_db->query($sql);
                ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
            }
             if ($sync_order['refund_status'] > 1 && ($order['status'] == 'CONFIRM'|| $order['status'] == 'CANCEL')) {
                $sql="update sync_pinduoduo_order_info set refund_status = 1
                where order_sn = '{$order['order_sn']}'
                ";
                echo date("Y-m-d H:i:s")."sync_update_sql".$sql;
                $sync_db->query($sql);
                 ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
            }
             if ($sync_order['refund_status'] == 1 && $order['status'] == 'DELETED'){
                $sql="update sync_pinduoduo_order_info set refund_status = 2
                where order_sn = '{$order['order_sn']}'
                ";
                echo date("Y-m-d H:i:s")."sync_update_sql".$sql;
                $sync_db->query($sql);
                 ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
            }
        }
    }
}

function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
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
    }
    {
        $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
    }
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}

function refreshGroupSkuWeightAndPrice($platform_sku_id,$facility_id){
    global $db,$sync_db,$redis;
     $sql = "select s.sku_id,sum(sk.weight*g.number) weight,sum(i.purchase_price*g.number) purchase_price from group_sku_mapping g inner join sku_mapping s
            on g.facility_id = s.facility_id and g.platform_sku_id = s.platform_sku_id
            inner join sku sk on sk.sku_id = g.sku_id and sk.facility_id = g.facility_id
            inner join inventory i on i.sku_id = g.sku_id and i.facility_id = g.facility_id
            where g.facility_id = {$facility_id} and g.platform_sku_id = {$platform_sku_id}";
    $group_goods = $db->getRow($sql);
    var_dump($group_goods);
    if (!empty($group_goods)&&!empty($group_goods['sku_id'])) {
        echo date("Y-m-d H:i:s").' '."update sku set weight = {$group_goods['weight']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
        $db->query("update sku set weight = {$group_goods['weight']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
        echo date("Y-m-d H:i:s").' '."update inventory set purchase_price = {$group_goods['purchase_price']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
        $db->query("update inventory set purchase_price = {$group_goods['purchase_price']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
        $redis_value = ['sku_id'=>$group_goods['sku_id'],'facility_id'=>$facility_id];
        $redis->lpush("order_weight_update", json_encode($redis_value));
    }
}

function refreshArrToShopMap($arr){
    $map = [];
    foreach ($arr as $value) {
        if (isset($value['shop_id']) && isset($value['enabled'])) {
            $map[$value['shop_id']] = $value['enabled'];
        }
    }
    return $map;
}

function getFacilityRedis() {
    global $redis_config;
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => $redis_config['host'],
          'port' => $redis_config['port']
    ]);
    if ($redis_config['auth']) {
        $redis->auth($redis_config['auth']);
    }
    if ($redis_config['database']) {
        $redis->select($redis_config['database']);
    }
    return $redis;
}

