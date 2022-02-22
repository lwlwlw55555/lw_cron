<?php
require("includes/init.php");
echo date("Y-m-d H:i:s")." delete_three_month_orders begin".PHP_EOL;

global $db, $db_conf, $jj;

for ($jj = 0; $jj < 256; $jj ++) {
    $db_conf['name'] = "erp_{$jj}";
    $db = ClsPdo::getInstance($db_conf);
$is_begin = true;
$facility_id_wait_ship = null;
while($is_begin || !empty($facility_id_wait_ship)){
	$is_begin = false;
	$facility_id_wait_ship = $db->getOne("SELECT facility_id
	FROM multi_goods_shipment
	WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('WAIT_SHIP','PRE_SHIP') AND
	      confirm_time < DATE_SUB(now(), INTERVAL 3 MONTH)");
	if (!empty($facility_id_wait_ship)) {
		deleteByFacility($facility_id_wait_ship,'WAIT_SHIP');
	}
}

$is_begin = true;
$facility_id_shipped = null;
while($is_begin || !empty($facility_id_shipped)){
	$is_begin = false;
	$facility_id_shipped = $db->getOne("SELECT facility_id
FROM multi_goods_shipment
WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('SHIPPED', 'SIGNED') AND
			shipping_time < DATE_SUB(now(), INTERVAL 3 MONTH)
			union 
			SELECT facility_id
FROM multi_goods_shipment
WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('SHIPPED', 'SIGNED') AND
			last_updated_time < DATE_SUB(now(), INTERVAL 3 MONTH) and shipping_time is null
			union 
			SELECT facility_id
FROM multi_goods_shipment
WHERE status = 'STOP' and 
			last_updated_time < DATE_SUB(now(), INTERVAL 4 MONTH)
			");
	if (!empty($facility_id_shipped)) {
		deleteByFacility($facility_id_shipped,'SHIPPED');
	}
}

$is_begin = true;
$facility_id_refund = null;
while($is_begin || !empty($facility_id_refund)){
	$is_begin = false;
	$facility_id_refund = $db->getOne("SELECT facility_id
FROM order_info
WHERE pay_status in ('PS_REFUNDING','PS_REFUND_APPLY','PS_REFUND_SUCC') AND
      refund_time < DATE_SUB(now(), INTERVAL 3 MONTH)");
	if (!empty($facility_id_refund)) {
		deleteByFacility($facility_id_refund,'REFUND');
	}
}
}

function deleteByFacility($facility_id,$method){
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
	global $db;
	echo date("Y-m-d H:i:s").' facility_id'.$facility_id.' method:'.$method.' deleteByFacility begin'.PHP_EOL;
	if ('WAIT_SHIP' == $method) {
		while($db->getOne("SELECT 1
						FROM multi_goods_shipment
						WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('WAIT_SHIP','PRE_SHIP') AND
	      				confirm_time < DATE_SUB(now(), INTERVAL 3 MONTH) and facility_id = {$facility_id}")){
			$ids = $db->getAll("SELECT order_sn,shipment_id
						FROM multi_goods_shipment
						WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('WAIT_SHIP','PRE_SHIP') AND
	      				confirm_time < DATE_SUB(now(), INTERVAL 3 MONTH) and facility_id = {$facility_id} limit 0,500");
			deleteInfo($facility_id,$ids,$method);
		}
	}
	if ('SHIPPED' == $method) {
		while($db->getOne("SELECT 1
						FROM multi_goods_shipment
						WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('SHIPPED', 'SIGNED') AND
						      shipping_time < DATE_SUB(now(), INTERVAL 3 MONTH) and facility_id = {$facility_id}")){
			$ids = $db->getAll("SELECT order_sn,shipment_id
						FROM multi_goods_shipment
						WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('SHIPPED', 'SIGNED') AND
      					shipping_time < DATE_SUB(now(), INTERVAL 3 MONTH) and facility_id = {$facility_id} limit 0,500");
			deleteInfo($facility_id,$ids,$method);
		}
		while($db->getOne("SELECT 1
						FROM multi_goods_shipment
						WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('SHIPPED', 'SIGNED') AND
						      last_updated_time < DATE_SUB(now(), INTERVAL 3 MONTH) and shipping_time is null and facility_id = {$facility_id}")){
			$ids = $db->getAll("SELECT order_sn,shipment_id
						FROM multi_goods_shipment
						WHERE status IN ('CONFIRM','CANCEL', 'DELETED') AND shipment_status in ('SHIPPED', 'SIGNED') AND
						last_updated_time < DATE_SUB(now(), INTERVAL 3 MONTH) and shipping_time is null and facility_id = {$facility_id} limit 0,500");
			deleteInfo($facility_id,$ids,$method);
		}

		while($db->getOne("SELECT 1
						FROM multi_goods_shipment
						WHERE status = 'STOP' and
						      last_updated_time < DATE_SUB(now(), INTERVAL 4 MONTH) and facility_id = {$facility_id}")){
			$ids = $db->getAll("SELECT order_sn,shipment_id
						FROM multi_goods_shipment
						WHERE status = 'STOP' and
						last_updated_time < DATE_SUB(now(), INTERVAL 4 MONTH) and facility_id = {$facility_id} limit 0,500");
			deleteInfo($facility_id,$ids,$method);
		}
	}
	if ('REFUND' == $method) {
		while($db->getOne("SELECT 1
						FROM order_info
						WHERE pay_status in ('PS_REFUNDING','PS_REFUND_APPLY','PS_REFUND_SUCC') AND
						      refund_time < DATE_SUB(now(), INTERVAL 3 MONTH) and  facility_id = {$facility_id}")){
			$ids = $db->getAll("SELECT order_sn,shipment_id
						FROM order_info
						WHERE pay_status in ('PS_REFUNDING','PS_REFUND_APPLY','PS_REFUND_SUCC') AND
						      refund_time < DATE_SUB(now(), INTERVAL 3 MONTH) and facility_id = {$facility_id} limit 0,500");
			deleteInfo($facility_id,$ids,$method);
		}
	}
}

function deleteInfo($facility_id,$ids,$method){
	if (!empty($ids)) {
		global $db,$sync_db, $jj;
		$order_sns = getAllValByCol($ids,'order_sn');
		$shipment_ids = getAllValByCol($ids,'shipment_id');
		$sql_sync = "delete from sync_pinduoduo_order_info where order_sn in ('".implode("','", $order_sns)."')";
		echo date("Y-m-d H:i:s").' method:'.$method.' sql_sync :'.PHP_EOL.PHP_EOL;
        $sync_db->query($sql_sync);

		$order_ids = $db->getCol("select order_id from order_info where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")");

        if (!empty($order_ids)) {
			$sql_order_goods = "delete from order_goods where facility_id = {$facility_id} and order_id in (".implode(",", $order_ids).")";
	        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_order_goods :'.PHP_EOL.PHP_EOL;
	        $db->query($sql_order_goods);
	    }

		$sql_shipment = "delete from shipment where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_shipment :'.PHP_EOL.PHP_EOL;
        $db->query($sql_shipment);

		$sql_multi_goods = "delete from multi_goods_shipment_goods where facility_id = {$facility_id} and original_shipment_id in (".implode(",", $shipment_ids).")";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_multi_goods :'.PHP_EOL.PHP_EOL;
        $db->query($sql_multi_goods);

        $sql_finance_detail = "delete from finance_detail where facility_id = {$facility_id} and order_sn in ('".implode("','", $order_sns)."')";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_finance_detail :'.PHP_EOL.PHP_EOL;
        $db->query($sql_finance_detail);

		$sql_print_log = "delete from print_log where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_print_log :'.PHP_EOL.PHP_EOL;
        $db->query($sql_print_log);

		$sql_inventory_detail_order = "delete from inventory_detail_order where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_inventory_detail_order :'.PHP_EOL.PHP_EOL;
        $db->query($sql_inventory_detail_order);

		$package_ids = $db->getCol("select package_id from shipment_package where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")");
		if (!empty($package_ids)) {
			$sql_shipment_package = "delete from shipment_package where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")";
	        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_shipment_package :'.PHP_EOL.PHP_EOL;
	        $db->query($sql_shipment_package);

			$sql_package = "delete from package where facility_id = {$facility_id} and package_id in (".implode(",", $package_ids).")";
	        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_package :'.PHP_EOL.PHP_EOL;
	        $db->query($sql_package);

	        $sql_mailnos = "delete from mailnos where facility_id = {$facility_id} and package_id in (".implode(",", $package_ids).")";
	        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_mailnos :'.PHP_EOL.PHP_EOL;
	        $db->query($sql_mailnos);
	    }

	   	$sql_order_info = "delete from order_info where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_order_info :'.PHP_EOL.PHP_EOL;
        $db->query($sql_order_info);

        $sql_multi = "delete from multi_goods_shipment where facility_id = {$facility_id} and shipment_id in (".implode(",", $shipment_ids).")";
        echo date("Y-m-d H:i:s") . " jj:{$jj} ".' method:'.$method.' sql_multi :' . PHP_EOL.PHP_EOL;
        $db->query($sql_multi);
	}
}

function getAllValByCol($arr,$column){
    $res = [];
    if (is_array($arr) && !empty($arr)) {
        foreach ($arr as $row) {
            if (isset($row[$column])) {
                $res[] = $row[$column];
            }
        }
        return $res;
    }
    return [];
}
