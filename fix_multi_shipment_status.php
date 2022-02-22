<?php
require("includes/init.php");
$url = 'http://localhost:8080/express_dderpsync_inner';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

$redis = getFacilityRedis();

$default_start_date = date("Y-m-d 00:00:00",strtotime(" -2 day"));
echo date("Y-m-d H:i:s").PHP_EOL;

global $sync_db,$db;
if (date('H') >= 2 && date('H') <= 5) {
    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );
    $cc=0;
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
                $sql = "select distinct m.merged_main_shipment_id from multi_goods_shipment m
                      where m.created_time >= '{$default_start_date}' and m.is_merged = 1 and m.is_main_shipment = 1 and m.status = 'STOP' and m.is_print_tracking = 0
                      limit {$start},2000";
                 echo date("Y-m-d H:i:s")." sql".$sql;
                $orders = $erp_ddyun_db->getAll($sql);
                // echo json_encode($orders);die;
                $count = count($orders);
                $start += $count;
                echo 'count:'.$count.' start:'.$start.PHP_EOL;
                if (!empty($orders)) {
                   echo json_encode($orders);
                }
                foreach ($orders as $order) {
                    $sql = "select * from multi_goods_shipment where merged_main_shipment_id = {$order['merged_main_shipment_id']}";
                    $c_orders = $erp_ddyun_db->getAll($sql);
                    echo json_encode($c_orders);
                    $is_split = true;
                    foreach ($c_orders as $co) {
                        if ($co['is_print_tracking'] == 1) {
                            $is_split = false;
                        }
                    }
                    if ($is_split) {
                        echo $is_split.PHP_EOL;
                        $sql1 = "update multi_goods_shipment set is_merged = 0,merged_main_shipment_id = shipment_id,is_main_shipment = 1 where merged_main_shipment_id = {$order['merged_main_shipment_id']}";
                        echo $sql1.PHP_EOL;
                        $erp_ddyun_db->query($sql1);
                        $sql2 = "update multi_goods_shipment_goods set  shipment_id = original_shipment_id where shipment_id = {$order['merged_main_shipment_id']}";
                        echo $sql2.PHP_EOL;
                        $erp_ddyun_db->query($sql2);
                    }
                }
                continue;
            }
        }catch(Exception $e){
            $i=$i-1;
            continue;
        }
        echo 'total:'.$cc;
    }

    echo date("Y-m-d H:i:s").PHP_EOL;

    global $sync_db,$db;

    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );
    $cc=0;
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
                $sql = "select distinct m.merged_main_shipment_id from multi_goods_shipment m
                      where m.created_time >= '{$default_start_date}' and m.is_merged = 1 and m.is_main_shipment = 0 and m.status = 'CONFIRM' and m.is_print_tracking = 0
                      limit {$start},2000";
                 echo date("Y-m-d H:i:s")." sql".$sql;
                $orders = $erp_ddyun_db->getAll($sql);
                // echo json_encode($orders);die;
                $count = count($orders);
                $start += $count;
                echo 'count:'.$count.' start:'.$start.PHP_EOL;
                if (!empty($orders)) {
                   echo json_encode($orders);
                }
                foreach ($orders as $order) {
                    $status = $erp_ddyun_db->getOne("select status from multi_goods_shipment where shipment_id = {$order['merged_main_shipment_id']}");
                    if ($status == 'STOP') {
                        $sql = "select * from multi_goods_shipment where merged_main_shipment_id = {$order['merged_main_shipment_id']}";
                        $c_orders = $erp_ddyun_db->getAll($sql);
                        echo json_encode($c_orders);
                        $is_split = true;
                        foreach ($c_orders as $co) {
                            if ($co['is_print_tracking'] == 1) {
                                $is_split = false;
                            }
                        }
                        if ($is_split) {
                            echo $is_split.PHP_EOL;
                            $sql1 = "update multi_goods_shipment set is_merged = 0,merged_main_shipment_id = shipment_id,is_main_shipment = 1 where merged_main_shipment_id = {$order['merged_main_shipment_id']}";
                            echo $sql1.PHP_EOL;
                            $erp_ddyun_db->query($sql1);
                            $sql2 = "update multi_goods_shipment_goods set  shipment_id = original_shipment_id where shipment_id = {$order['merged_main_shipment_id']}";
                            echo $sql2.PHP_EOL;
                            $erp_ddyun_db->query($sql2);
                        }
                    }
                }
                continue;
            }
        }catch(Exception $e){
            $i=$i-1;
            continue;
        }
        echo 'total:'.$cc;
    }
}

echo date("Y-m-d H:i:s").'  开始检查group_sku is_group 不一致'.PHP_EOL;
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
        $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

        if($erp_ddyun_db->getOne("select 1 from platform_sku p inner JOIN sku_mapping m
            on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
          inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
        where p.is_group = 1 AND  s.is_group = 0
        UNION
        select 1 from platform_sku p inner JOIN sku_mapping m
            on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
          inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
        where p.is_group = 0 AND  s.is_group = 1  
        ")){
            echo $erp_ddyun_db_conf['name'].'发现有 sku is_group不一致 情况'.PHP_EOL;
        	$no_is_group_skus = $erp_ddyun_db->getAll("SELECT p.platform_sku_id,s.sku_id from platform_sku p inner JOIN sku_mapping m
                    on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
                  inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
                where p.facility_id = {$facility_id} and p.is_group = 1 AND s.is_group = 0
                union
                SELECT p.platform_sku_id,s.sku_id from platform_sku p inner JOIN sku_mapping m
                    on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
                  inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
                where p.facility_id = {$facility_id} and p.is_group = 0 AND s.is_group = 1");
        	if (!empty($no_is_group_skus)) {
        		echo date("Y-m-d H:i:s")." 发现以下sku is_group不一致".PHP_EOL;
        		var_dump($no_is_group_skus);
        	}
        	foreach ($no_is_group_skus as $sku) {
        		$platform_sku_id = $erp_ddyun_db->getOne("SELECT g.platform_sku_id from group_sku_mapping g inner join sku_mapping m
        	    on g.facility_id = m.facility_id and g.platform_sku_id = m.platform_sku_id
        	    where m.sku_id = {$sku['sku_id']}");
        	    if (!empty($platform_sku_id)) {
        	    	$groups = $erp_ddyun_db->getAll("select facility_id,sku_id,number,package_fee from group_sku_mapping where platform_sku_id = {$platform_sku_id}");
        	    	foreach ($groups as $key => $group) {
        	    		$group['package_fee'] = empty($group['package_fee'])?"null":$group['package_fee'];
        	    		$sql = "insert into group_sku_mapping(facility_id,platform_sku_id,sku_id,number,package_fee) values ({$sku['facility_id']},{$sku['platform_sku_id']},{$group['sku_id']},{$group['number']},{$group['package_fee']})";
        	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
        	    		$erp_ddyun_db->query($sql);
        	    	}
        	    	$sql = "update sku set is_group = 1 where sku_id = {$sku['sku_id']}";
        	    	echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
        	    	$erp_ddyun_db->query($sql);
                    refreshGroupSkuWeightAndPrice($sku['platform_sku_id'],$sku['facility_id']);
        			$platform_sku_ids = $erp_ddyun_db->getCol("select platform_sku_id from sku_mapping where sku_id = {$sku['sku_id']}");
        	    	if (!empty($platform_sku_ids)) {
        	    		$sql = "update platform_sku set is_group = 1 where platform_sku_id in (".implode(",", $platform_sku_ids).")";
        	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
        	    		$erp_ddyun_db->query($sql);
        	    	}
        	    }else{
        	    	$sql = "update sku set is_group = 0 where sku_id = {$sku['sku_id']} ";
        	    	echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
        	    	$erp_ddyun_db->query($sql);
        	    	$platform_sku_ids = $erp_ddyun_db->getCol("select platform_sku_id from sku_mapping where  sku_id = {$sku['sku_id']}");
        	    	if (!empty($platform_sku_ids)) {
        	    		$sql = "update platform_sku set is_group = 0 where platform_sku_id in (".implode(",", $platform_sku_ids).")";
        	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
        	    		$erp_ddyun_db->query($sql);
        	    	}
    	       }
           }
            echo $erp_ddyun_db_conf['name'].'发现有 sku group weight不一致 情况'.PHP_EOL;
            $weight_groups = $erp_ddyun_db->getAll("SELECT g.facility_id,g.platform_sku_id,sum(s.weight*g.number) sweight,sk.weight
                from group_sku_mapping g
              LEFT JOIN sku s on g.facility_id = s.facility_id and g.sku_id = s.sku_id
              LEFT JOIN sku_mapping sm on sm.facility_id = g.facility_id and sm.platform_sku_id = g.platform_sku_id
              left join sku sk on sk.sku_id = sm.sku_id and sk.facility_id = g.facility_id  
              group by g.platform_sku_id");
            foreach ($weight_groups as $weight_group) {
                if (empty($weight_group['weight']) || $weight_group['sweight'] <> $weight_group['weight']) {
                    echo date("Y-m-d H:i:s")." 发现group_weight不一致:".json_encode($weight_group).PHP_EOL;
                    refreshGroupSkuWeightAndPrice($weight_group['platform_sku_id'],$weight_group['facility_id']);
                }
            }
    	}
    }catch(Exception $e){
        $i=$i-1;
        continue;
    }
}

function refreshGroupSkuWeightAndPrice($platform_sku_id,$facility_id){
    global $db,$sync_db,$redis;
    selectRdsByFacilityId($facility_id);
    $sql = "
        select 
            default_warehouse_id
        from
            facility
        where 
            facility_id = {$facility_id}
     ";
     $default_warehouse_id = $db->getOne($sql);
     $warehouse_sql = !empty($default_warehouse_id)?" and i.warehouse_id = {$default_warehouse_id}":"";
     $sql = "
        select 
            s.sku_id,
            sum(sk.weight*g.number) weight,
            sum(i.purchase_price*g.number) purchase_price 
        from 
            group_sku_mapping g 
            inner join sku_mapping s on g.facility_id = s.facility_id and g.platform_sku_id = s.platform_sku_id
            inner join sku sk on sk.sku_id = g.sku_id and sk.facility_id = g.facility_id
            inner join inventory i on i.sku_id = g.sku_id and i.facility_id = g.facility_id {$warehouse_sql}
        where 
            g.facility_id = {$facility_id} 
            and g.platform_sku_id = {$platform_sku_id}
    ";
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

// echo date("Y-m-d H:i:s").PHP_EOL;
// $facilitys = $db->getCol("select facility_id from facility where is_force_manage_goods = 0 and enabled = 1  ");
// echo date("Y-m-d H:i:s").'  开始检查multi_goods_shipment shipment_status 不一致'.PHP_EOL;
// foreach ($facilitys as $facility_id) {
// 	$sql1 = "select order_sn from multi_goods_shipment where shipping_time is NOT  null and shipment_status = 'WAIT_SHIP' and facility_id = {$facility_id}";
// 	//echo $sql1.PHP_EOL;
// 	$orders = $db->getCol($sql1);
//  	if (!empty($orders)) {
//  		$sql = "update multi_goods_shipment set shipment_status = 'SHIPPED' where order_sn in ('".implode("','", $orders)."') and facility_id = {$facility_id}";
//  		echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//  		$db->query($sql);
//  	}
// 	//echo date("Y-m-d H:i:s").'  开始检查order_info_shipment_id'.PHP_EOL;
//     $orders = $db->getAll("select order_sn,facility_id from order_info where shipment_id is null and facility_id = {$facility_id}");
// 	if (!empty($orders)) {
// 		foreach ($orders as $order) {
// 			$shipment_id = $db->getOne("select shipment_id from shipment where order_sn = '{$order['order_sn']}' and facility_id = {$order['facility_id']}");
// 			if (!empty($shipment_id)) {
// 				$sql = "update order_info set shipment_id = {$shipment_id} where order_sn = '{$order['order_sn']}' and facility_id = {$order['facility_id']}";
// 				echo date("Y-m-d H:i:s").' '. $sql.PHP_EOL;
// 				$db->query($sql);
// 			}
// 		}
// 	}
// }