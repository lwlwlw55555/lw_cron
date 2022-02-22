<?php
require("includes/init.php");
$url = 'http://localhost:8080/erp_syncinner_prod';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
global $db,$sync_db;

$facilitys = [];
if (isset($opt_params['mod_index']) || isset($opt_params['facility_id'])) {
    if (isset($opt_params['mod_index'])) {
        $facilitys = $db_user->getCol("
            select 
                distinct u.facility_id
            from 
                user u 
                inner join session_date sd on u.user_id = sd.user_id
            where 
                sd.session_date > date_sub(now(), interval 14 day) and 
                mod(u.facility_id,10) = {$opt_params['mod_index']} and
                u.is_force_manage_goods = 0
        ");
    }else{
        $facilitys = $db->getCol("select facility_id from facility where facility_id = {$opt_params['facility_id']}");
    }
}else{
        $facilitys = $db_user->getCol("
            select 
                distinct u.facility_id
            from 
                user u
                inner join session_date sd on u.user_id = sd.user_id
            where 
                sd.session_date > date_sub(now(), interval 14 day) and 
                u.is_force_manage_goods = 0
        ");
}

echo date("Y-m-d H:i:s").PHP_EOL;
$facilitys = $db->getCol("select facility_id from facility where is_force_manage_goods = 0 and enabled = 1  ");
echo date("Y-m-d H:i:s").'  开始检查multi_goods_shipment order_info shipping_time 不一致'.PHP_EOL;
foreach ($facilitys as $facility_id) {
	selectRdsByFacilityId($facility_id);
	$is_begin = true;
    $count = 0;
    $start = 0;
    while($is_begin || $count == 2000){
	    $is_begin = false;
		$sql1 = "select m.shipment_id,m.shipping_time from multi_goods_shipment m inner join order_info o on m.facility_id = o.facility_id and m.shipment_id = o.shipment_id
		 where m.shipping_time >= '2020-04-14 07:00:00' and o.shipping_time is null and m.shipment_status = 'SHIPPED' and m.is_print_tracking = 1 
		 and m.facility_id = {$facility_id}
		 limit {$start},2000";
		 echo date("Y-m-d H:i:s")." sql1".$sql1;
		$orders = $db->getAll($sql1);
		$count = count($orders);
        $start += $count;
        echo 'count:'.$count.' start:'.$start.PHP_EOL;
	 	if (!empty($orders)) {
	 		foreach ($orders as $order) {
	 			$sql_update = "update order_info set shipping_time = '{$order['shipping_time']}',order_status='WAIT_BUYER_CONFIRM_GOODS' where shipment_id = {$order['shipment_id']}";
	 			echo date("Y-m-d H:i:s").' '.$sql_update.PHP_EOL;
	 			$db->query($sql_update);
	 		}
	 	}
	 }
}
