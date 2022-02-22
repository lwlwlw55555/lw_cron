<?php
require("includes/init.php");
echo date("Y-m-d H:i:s")." delete_three_month_orders begin".PHP_EOL;

global $sync_db;


global $db_user;
global $db;

$platform_shop_ids = $db_user->getCol("select platform_shop_id from shop_oauth where expire_time < '2021-10-01'");

foreach ($platform_shop_ids as $platform_shop_id) {
	$sql = "select * from shop where platform_shop_id = '{$platform_shop_id}' and enabled = 0";
	echo $sql.PHP_EOL;
	$shop = $sync_db->getRow($sql);
	if (empty($shop)) {
		continue;
	}

	selectRdsByFacilityId($shop['default_facility_id']);

	$is_begin = true;
	$count = 0;
	$start = 0;
	$total = 0;
	$limit = 1000;
	while($is_begin || $count == $limit){
		try{
		    $is_begin = false;
		 
		    $sql = "select order_sn from multi_goods_shipment where shipment_status = 'WAIT_SHIP' and status = 'CONFIRM' and last_updated_time < '2021-10-1' and platform_name = 'pinduoduo' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    $order_list = $db->getCol($sql);
		    // $sql = "select order_sn from sync_pinduoduo_order_info where order_status = 1 and refund_status = 1 and last_updated_time < '2021-10-1' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    // $order_list = $sync_db->getCol($sql);

		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_order] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	// die;
		    	break;
		    }
		    $start += $count; 
		    $sql = "delete from order_info where order_sn in ('".implode("','",$order_list)."')";
		    echo $sql.PHP_EOL;
		    $db->query($sql);


		    $sql = "delete from shipment where order_sn in ('".implode("','",$order_list)."')";
		    echo $sql.PHP_EOL;
		    $db->query($sql);

		    $sql = "delete from multi_goods_shipment where order_sn in ('".implode("','",$order_list)."')";
		    echo $sql.PHP_EOL;
		    $db->query($sql);

		    $sql = "delete from multi_goods_shipment_goods where order_sn in ('".implode("','",$order_list)."') and facility_id = {$shop['default_facility_id']}";
		    echo $sql.PHP_EOL;
		    $db->query($sql);

		    $sql = "delete from sync_pinduoduo_order_info where order_sn in ('".implode("','",$order_list)."')";
		   	echo $sql.PHP_EOL;
		    $sync_db->query($sql);

		    $total += $count;
		    $wait = rand(200,400);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_order] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    // die;
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}



