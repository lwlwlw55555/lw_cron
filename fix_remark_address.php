<?php
require("includes/init.php");
$url = 'http://localhost:8080/erp_syncinner_prod';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

echo date("Y-m-d H:i:s").PHP_EOL;

global $sync_db,$db;

$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

for($i=0;$i<256;$i++){
    try{
        $erp_ddyun_db_conf['name'] = 'erp_'.$i;
        echo date("Y-m-d H:i:s")." check [{$erp_ddyun_db_conf['name']}] begin".PHP_EOL;
        if (isset($erp_ddyun_db)) {
            unset($erp_ddyun_db);
        }
        global $erp_ddyun_db;
        $is_begin = true;
        $count = 0;
        $start = 0;
        $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
        while($is_begin || $count == 2000){
            $is_begin = false;
            $sql = "select m.shop_id,m.order_sn,m.district_name,m.shipping_address,m.mobile,m.seller_note,m.buyer_note,m.receive_name,m.shipment_status,m.status from multi_goods_shipment m
                  where m.created_time >= '2020-05-13 22:30:00' and m.shipment_status = 'WAIT_SHIP'
                  limit {$start},2000";
             echo date("Y-m-d H:i:s")." sql".$sql;
            $orders = $erp_ddyun_db->getAll($sql);
            $count = count($orders);
            $start += $count;
            echo 'count:'.$count.' start:'.$start.PHP_EOL;
            foreach ($orders as $order) {
                $sync_order = $sync_db->getRow("select town,address,receiver_phone,remark,customer_remark,receiver_name,order_status,refund_status from sync_pinduoduo_order_info where order_sn = '{$order['order_sn']}'");
                if (!empty($sync_order)){
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
    }catch(Exception $e){
        $i=$i-1;
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