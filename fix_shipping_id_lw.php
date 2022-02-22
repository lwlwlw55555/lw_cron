<?php
require("includes/init.php");
$url = 'http://localhost:8080/erp_syncinner_prod';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
$redis = getFacilityRedis();

echo date("Y-m-d H:i:s").PHP_EOL;

global $sync_db,$db;

$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);
$update_count = 0;
for($i=0;$i<256;$i++){
    try{
        $erp_ddyun_db_conf['name'] = 'erp_'.$i;
        echo date("Y-m-d H:i:s")." check [{$erp_ddyun_db_conf['name']}] begin".PHP_EOL;
        if (isset($erp_ddyun_db)) {
            unset($erp_ddyun_db);
        }
        global $erp_ddyun_db;
        $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
        $is_begin = true;
        $count = 0;
        $start = 0;
        while($is_begin || $count == 2000){
            global $erp_ddyun_db;
            $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
            $is_begin = false;
            $sql = "select shipping_name,shipping_id,shipment_id from multi_goods_shipment where 
                 address_id = 1997612999900006220 and shipment_status = 'SHIPPED' and created_time >= '2020-04-10'
                  limit {$start},2000";
             echo date("Y-m-d H:i:s")." sql".$sql;
            $orders = $erp_ddyun_db->getAll($sql);
            $count = count($orders);
            $start += $count;
            echo 'count:'.$count.' start:'.$start.' update_count:'.$update_count.PHP_EOL;
            foreach ($orders as $order) {
                $sql_shipping = "select shipping_id from erpuser.shipping where shipping_name = '{$order['shipping_name']}'";
                $shipping_id = $erp_ddyun_db->getOne($sql_shipping);
                if (!empty($shipping_id) && $shipping_id <> $order['shipping_id']) {
                    $update_count++;
                    $sql_1 = "update multi_goods_shipment set shipping_id = {$shipping_id},origin_name='SHIPPING_DONE' where shipment_id = {$order['shipment_id']}";
                   // echo $sql_1.PHP_EOL;
                    $erp_ddyun_db->query($sql_1);
                    $sql_2 = "update shipment set shipping_id = {$shipping_id} where shipment_id = {$order['shipment_id']}";
                    $erp_ddyun_db->query($sql_2);
                   // echo $sql_2.PHP_EOL;
                    //die;
                }

            }
        }
    }catch(Exception $e){
        echo '出现异常2'.$e->getMessage().'重新获取连接'.PHP_EOL;
        sleep(1);
        $i=$i==0?0:$i-1;
        // $is_start = true;
        continue;
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
    }else{
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

