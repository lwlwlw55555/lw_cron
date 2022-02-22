<?php
require("includes/init.php");
$url = 'http://100.65.128.171:10317';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
$redis = getFacilityRedis();
$default_start_date = date("Y-m-d 00:00:00",strtotime(" -2 day"));
echo date("Y-m-d H:i:s").PHP_EOL;

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

if (isset($opt_params['mod_index']) && $opt_params['mod_index'] == 0) {
    if (isset($oms_user)) {
        $is_begin = true;
        $count = 0;
        $start = 0;
        $total = 0;
        $limit = 1000;
        $date = date("Y-m-d H:i:s",strtotime("-3 day"));

        while($is_begin || $count == $limit){
            try{
                $is_begin = false;
                $sql = "select id from inventory_sync_job_log where sync_time < '{$date}' limit {$limit}";
                echo $sql.PHP_EOL;
                $order_list = $oms_user->getCol($sql);
                $count = count($order_list);
                if (empty($count)) {
                    echo '[]'.'[delete_inventory_sync_job] delete count:'.$count.' total:'.$total.' is 0 break!'.PHP_EOL;
                    break;
                }
                $start += $count; 
                $sql = "delete from inventory_sync_job_log where id in (".implode(",",$order_list).")";
                echo $sql.PHP_EOL;
                $total += $count;
                $wait = rand(200,400);
                usleep($wait);
                echo '[]'.date("Y-m-d H:i:s").'[delete_inventory_sync_job] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
                $oms_user->query($sql);
                
                // die;
            }catch(Exception $e){
                continue;
            }
        }

        if (date('H') < 8) {
            $sql = "optimize table inventory_sync_job_log";
            $oms_user->query($sql);
        }
    }
}

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
        $facilitys = [$opt_params['facility_id']];
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

echo 'facilitys:'.PHP_EOL;
var_dump($facilitys);

echo date("Y-m-d H:i:s").'  开始检查group_sku is_group 不一致'.PHP_EOL;
foreach ($facilitys as $facility_id) {
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

	$no_is_group_skus = $db->getAll("SELECT p.platform_sku_id,s.sku_id from platform_sku p inner JOIN sku_mapping m
	    on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
	  inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
	where p.facility_id = {$facility_id} and p.is_group = 1 AND s.is_group = 0
    union
    SELECT p.platform_sku_id,s.sku_id from platform_sku p inner JOIN sku_mapping m
        on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
      inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
    where p.facility_id = {$facility_id} and p.is_group = 0 AND s.is_group = 1
    ");
	if (!empty($no_is_group_skus)) {
		echo date("Y-m-d H:i:s")." 发现以下sku is_group不一致".PHP_EOL;
		var_dump($no_is_group_skus);
	}
	foreach ($no_is_group_skus as $sku) {
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
		$platform_sku_id = $db->getOne("SELECT g.platform_sku_id from group_sku_mapping g inner join sku_mapping m
	    on g.facility_id = m.facility_id and g.platform_sku_id = m.platform_sku_id
	    where m.sku_id = {$sku['sku_id']} and g.facility_id = {$facility_id}");
	    if (!empty($platform_sku_id)) {
	    	$groups = $db->getAll("select sku_id,number,package_fee from group_sku_mapping where platform_sku_id = {$platform_sku_id} and facility_id = {$facility_id}");
	    	foreach ($groups as $key => $group) {
                if (date('H') >= 8) {
            die("hour:8 die\n");
        }
	    		$group['package_fee'] = empty($group['package_fee'])?"null":$group['package_fee'];
	    		$sql = "insert into group_sku_mapping(facility_id,platform_sku_id,sku_id,number,package_fee) values ({$facility_id},{$sku['platform_sku_id']},{$group['sku_id']},{$group['number']},{$group['package_fee']})";
	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
	    		$db->query($sql);
	    	}
	    	$sql = "update sku set is_group = 1 where sku_id = {$sku['sku_id']} and facility_id = {$facility_id}";
	    	echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
	    	$db->query($sql);
            refreshGroupSkuWeightAndPrice($sku['platform_sku_id'],$facility_id);
			$platform_sku_ids = $db->getCol("select platform_sku_id from sku_mapping where facility_id = {$facility_id} and sku_id = {$sku['sku_id']}");
	    	if (!empty($platform_sku_ids)) {
	    		$sql = "update platform_sku set is_group = 1 where platform_sku_id in (".implode(",", $platform_sku_ids).") and facility_id = {$facility_id}";
	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
	    		$db->query($sql);
	    	}
	    }else{
	    	$sql = "update sku set is_group = 0 where sku_id = {$sku['sku_id']} and facility_id = {$facility_id}";
	    	echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
	    	$db->query($sql);
	    	$platform_sku_ids = $db->getCol("select platform_sku_id from sku_mapping where facility_id = {$facility_id} and sku_id = {$sku['sku_id']}");
	    	if (!empty($platform_sku_ids)) {
	    		$sql = "update platform_sku set is_group = 0 where platform_sku_id in (".implode(",", $platform_sku_ids).") and facility_id = {$facility_id}";
	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
	    		$db->query($sql);
	    	}
	    }

        $no_is_group_skus2 = $db->getAll("SELECT p.platform_sku_id,s.sku_id from platform_sku p 
            inner join sku_mapping s on s.facility_id = p.facility_id and p.platform_sku_id = s.platform_sku_id
            left JOIN group_sku_mapping m on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
        where p.facility_id = {$facility_id} and p.is_group = 1 and m.platform_sku_id is null");
        if (!empty($no_is_group_skus2)) {
            echo date("Y-m-d H:i:s")." 发现以下paltform_sku group不存在".PHP_EOL;
            var_dump($no_is_group_skus);
        }
        foreach ($no_is_group_skus2 as $sku) {
            if (date('H') >= 8) {
                die("hour:8 die\n");
            }
            $sql = "update sku set is_group = 0 where sku_id = {$sku['sku_id']} and facility_id = {$facility_id}";
            echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
            $db->query($sql);
            $platform_sku_ids = $db->getCol("select platform_sku_id from sku_mapping where facility_id = {$facility_id} and sku_id = {$sku['sku_id']}");
            if (!empty($platform_sku_ids)) {
                $sql = "update platform_sku set is_group = 0 where platform_sku_id in (".implode(",", $platform_sku_ids).") and facility_id = {$facility_id}";
                echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
                $db->query($sql);
            }
        }
	}

	$no_mapping_sku  = $db->getAll("SELECT g.group_sku_mapping_id,g.sku_id,sk.sku_alias from group_sku_mapping g
			  inner join sku sk on sk.facility_id = g.facility_id and sk.sku_id = g.sku_id
			  left JOIN sku_mapping s on g.sku_id = s.sku_id and g.facility_id = s.facility_id
			  where s.sku_id is null and g.facility_id = {$facility_id} and sk.sku_alias <> '' and sk.sku_alias is not null");
	if (!empty($no_mapping_sku)) {
		echo date("Y-m-d H:i:s")." 发现以下sku 作为被组合sku mapping is null".PHP_EOL;
		var_dump($no_mapping_sku);
	}
	foreach ($no_mapping_sku as $sku) {
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
		$can_group_sku = $db->getOne("select s.sku_id from sku s
				  inner join sku_mapping sk on s.facility_id = sk.facility_id and s.sku_id = sk.sku_id
				where s.facility_id = {$facility_id} and sku_alias = '{$sku['sku_alias']}'");
		if (!empty($can_group_sku)) {
			$sql = "update group_sku_mapping set sku_id = {$can_group_sku} where group_sku_mapping_id = {$sku['group_sku_mapping_id']} and facility_id = {$facility_id}";
			echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
			$db->query($sql);
		}
	}

    // echo date("Y-m-d H:i:s").'  开始检查group_sku_not_exist '.PHP_EOL;
    $groups = $db->getAll("SELECT g.group_sku_mapping_id,g.platform_sku_id,g.sku_id,h.platform_sku_id as new_platform_sku_id,g.facility_id,sm.sku_id as new_sku_id
        from group_sku_mapping g
      LEFT JOIN sku s on g.facility_id = s.facility_id and g.sku_id = s.sku_id
      LEFT JOIN sku_mapping_history h on h.facility_id = g.facility_id and g.sku_id = h.sku_id
      LEFT JOIN sku_mapping sm on sm.facility_id = g.facility_id and sm.platform_sku_id = h.platform_sku_id
    where s.sku_id is null and g.facility_id = {$facility_id}");
    if (!empty($groups)) {
        var_dump($groups);
    }
    foreach ($groups as $group) {
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
        if (!empty($group['new_platform_sku_id']) && !empty($group['new_sku_id'])) {
            $sql = "update group_sku_mapping set sku_id = {$group['new_sku_id']} where group_sku_mapping_id = {$group['group_sku_mapping_id']} and facility_id = {$group['facility_id']}";
            echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
            $db->query($sql);
            echo PHP_EOL.PHP_EOL;
            refreshGroupSkuWeightAndPrice($group['platform_sku_id'],$group['facility_id']);
        }else{
            echo date("Y-m-d H:i:s").' '.'platform_sku_id:'.$group['platform_sku_id'].' 发现有不存在的历史记录 删除该组合商品'.PHP_EOL;
            $sku_ids = $db->getCol("select sku_id from group_sku_mapping where platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}");
            if (!empty($sku_ids)) {
                $sql = "update platform_sku set is_group = 0 where platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}";
                echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
                $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}");
                if (!empty($sku_id)) {
                    $sql = "update sku set is_group = 0 where sku = {$sku_id} and facility_id = {$group['facility_id']}";
                    echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
                    $db->query($sql);
                }
                $sql = "delete from group_sku_mapping where platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}";
                echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
                $db->query($sql);
            }
            
        }
    }

    // echo date("Y-m-d H:i:s").'  开始检查group_weight 不正确 '.PHP_EOL;
    $weight_groups = $db->getAll("SELECT g.platform_sku_id,sum(s.weight*g.number) sweight,sk.weight,sk.sku_id,sum(ii.purchase_price*g.number)purchase_price,g.package_fee,sum(ii.purchase_price*g.number) s_package_fee,sk.package_fee sk_package_fee
        from group_sku_mapping g
      LEFT JOIN sku s on g.facility_id = s.facility_id and g.sku_id = s.sku_id
      LEFT JOIN sku_mapping sm on sm.facility_id = g.facility_id and sm.platform_sku_id = g.platform_sku_id
      left join sku sk on sk.sku_id = sm.sku_id and sk.facility_id = g.facility_id  
      left join inventory ii on ii.sku_id = s.sku_id
      where g.facility_id = {$facility_id}
      group by g.platform_sku_id");
    foreach ($weight_groups as $weight_group) {
        if (empty($weight_group['weight']) || $weight_group['sweight'] <> $weight_group['weight']) {
            echo date("Y-m-d H:i:s")." 发现group_weight不一致:".json_encode($weight_group).PHP_EOL;
            refreshGroupSkuWeightAndPrice($weight_group['platform_sku_id'],$facility_id);
        }
        if ($weight_group['s_package_fee'] <> $weight_group['package_fee'] || $weight_group['sk_package_fee'] <> $weight_group['package_fee']) {
            echo date("Y-m-d H:i:s")." 发现package_fee不一致:".json_encode($weight_group).PHP_EOL;
            refreshGroupSkuWeightAndPrice($weight_group['platform_sku_id'],$facility_id);
        }
        if (!empty($weight_group['purchase_price']) && !empty($weight_group['sku_id'])){
            $purchase_price = $db->getOne("select purchase_price from inventory where sku_id = {$weight_group['sku_id']}");
            if ($weight_group['purchase_price'] <> $purchase_price) {
                echo date("Y-m-d H:i:s")." 发现group_purchase_price不一致:".json_encode($weight_group).' purchase_price:'.$purchase_price.PHP_EOL;
                refreshGroupSkuWeightAndPrice($weight_group['platform_sku_id'],$facility_id);
            }
        }
    }
}
// echo date("Y-m-d H:i:s").'  开始检查platform_sku_is_onsale '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     if (date('H') >= 8) {
//         die("hour:8 die\n");
//     }
//     selectRdsByFacilityId($facility_id);
//     $is_begin = true;
//     $count = 0;
//     $start = 0;
//     while($is_begin || $count == 2000){
//         $is_begin = false;
//         $sql = "select is_onsale,platform_sku_id,shop_id from platform_sku 
//                 where facility_id = {$facility_id}
//                   limit {$start},2000";
//          //echo date("Y-m-d H:i:s")." sql".$sql;
//         $skus = $db->getAll($sql);
//         // echo json_encode($orders);die;
//         $count = count($skus);
//         $start += $count;
//         //echo 'count:'.$count.' start:'.$start.PHP_EOL;
//         foreach ($skus as $sku) {
//             if (date('H') >= 8) {
//             die("hour:8 die\n");
//         }
//             $sync_sku = $sync_db->getRow("select is_onsale from sku_pinduoduo where sku_id = {$sku['platform_sku_id']}");
//             if (!empty($sync_sku)){
//                 if(($sync_sku['is_onsale'] != null && $sync_sku['is_onsale'] != $sku['is_onsale'])) {
//                    echo 'sync_sku:'.json_encode($sync_sku).' order:'.json_encode($sku).PHP_EOL; 
//                     $sql="update sku_pinduoduo set is_onsale = 0,spec=''
//                     where sku_id = {$sku['platform_sku_id']}";
//                     echo date("Y-m-d H:i:s")."sync_update_sql".$sql.PHP_EOL;
//                     $sync_db->query($sql);
//                 }
//             }
//         }
//     }
// }

echo date("Y-m-d H:i:s").'  开始检查order_merge '.PHP_EOL;
foreach ($facilitys as $facility_id) {
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);
    $is_begin = true;
    $count = 0;
    $start = 0;
    $start_date = date("Y-m-d 00:00:00",strtotime(" -3 day"));
    while($is_begin || $count == 2000){
        $is_begin = false;
        $sql = "select distinct m.merged_main_shipment_id from multi_goods_shipment m
              where m.created_time >= '{$start_date}' and m.is_merged = 1 and m.facility_id = {$facility_id}
              limit {$start},2000";
         //echo date("Y-m-d H:i:s")." sql".$sql;
        $orders = $db->getAll($sql);
        // echo json_encode($orders);die;
        $count = count($orders);
        $start += $count;
        //echo 'count:'.$count.' start:'.$start.PHP_EOL;
        foreach ($orders as $order) {
            if (date('H') >= 8) {
                die("hour:8 die\n");
            }
            $sql = "select * from multi_goods_shipment where merged_main_shipment_id = {$order['merged_main_shipment_id']}";
            $c_orders = $db->getAll($sql);
            $is_false = 0;
            $cids = [];
            foreach ($c_orders as $co) {
                if (date('H') >= 8) {
                    die("hour:8 die\n");
                }
                $cids[] = $co['shipment_id'];
                if ($co['is_merged'] == 0) {
                    $is_false = 1;
                    echo date("Y-m-d H:i:s").' '.'create_date:'.$co['created_time'].' merged_main_shipment_id:'.$order['merged_main_shipment_id'].' '.json_encode($co).' is_merged is 0'.PHP_EOL;
                    $sql = "update multi_goods_shipment set is_merged = 1 where shipment_id = {$co['shipment_id']}";
                    echo date("Y-m-d H:i:s").' create_date:'.$co['created_time'].' '.$sql.PHP_EOL;
                    $db->query($sql);
                }
                if ($co['merged_main_shipment_id'] != $order['merged_main_shipment_id']) {
                    $is_false = 2;
                    echo date("Y-m-d H:i:s").' '.'create_date:'.$co['created_time'].' merged_main_shipment_id:'.$order['merged_main_shipment_id'].' '.json_encode($co).' merged_main_shipment_id diff'.PHP_EOL;
                    $sql = "update multi_goods_shipment set merged_main_shipment_id = {$order['merged_main_shipment_id']} where shipment_id = {$co['shipment_id']}";
                    echo date("Y-m-d H:i:s").' create_date:'.$co['created_time'].' '.$sql.PHP_EOL;
                    $db->query($sql);
                }
                if ($co['shipment_id'] == $order['merged_main_shipment_id'] && $co['is_main_shipment'] == 0){
                    $is_false = 3;
                    echo date("Y-m-d H:i:s").' '.'create_date:'.$co['created_time'].' merged_main_shipment_id:'.$order['merged_main_shipment_id'].' '.json_encode($co).' is_merged_main_shipment_id is_main_shipmet is 0'.PHP_EOL;
                    $sql = "update multi_goods_shipment set is_main_shipment = 1 where shipment_id = {$co['shipment_id']}";
                    echo date("Y-m-d H:i:s").' create_date:'.$co['created_time'].' '.$sql.PHP_EOL;
                    $db->query($sql);
                }
                if ($co['shipment_id'] != $order['merged_main_shipment_id'] && $co['is_main_shipment'] == 1){
                    $is_false = 4;
                    echo date("Y-m-d H:i:s").' '.'create_date:'.$co['created_time'].' merged_main_shipment_id:'.$order['merged_main_shipment_id'].' '.json_encode($co).' is_not_merged_main_shipment_id is_main_shipmet is 1'.PHP_EOL;
                    $sql = "update multi_goods_shipment set is_main_shipment = 0 where shipment_id = {$co['shipment_id']}";
                    echo date("Y-m-d H:i:s").' create_date:'.$co['created_time'].' '.$sql.PHP_EOL;
                    $db->query($sql);
                }
                if ($co['shipment_id'] != $order['merged_main_shipment_id']) {
                    $sql3 = "select * from multi_goods_shipment where merged_main_shipment_id = {$co['shipment_id']}";
                    $c_orders3 = $db->getAll($sql3);
                    foreach ($c_orders3 as $c3) {
                        if (date('H') >= 8) {
                            die("hour:8 die\n");
                        }
                        echo date("Y-m-d H:i:s").' '.'create_date:'.$c3['created_time'].' pre_merged_main_shipment_id:'.$co['shipment_id'].' '.json_encode($c3).' should be '.$order['merged_main_shipment_id'].PHP_EOL;
                        $sql = "update multi_goods_shipment set is_main_shipment = 0,is_merged=1,merged_main_shipment_id={$order['merged_main_shipment_id']} where shipment_id = {$c3['shipment_id']}";
                        echo date("Y-m-d H:i:s").' create_date:'.$c3['created_time'].' '.$sql.PHP_EOL;
                        $db->query($sql);  
                        $sql = "update multi_goods_shipment_goods set shipment_id={$order['merged_main_shipment_id']} where original_shipment_id = {$c3['shipment_id']}";
                        echo date("Y-m-d H:i:s").' create_date:'.$c3['created_time'].' '.$sql.PHP_EOL;
                        $db->query($sql);                  
                    }
                }
            }
            if (!empty($cids)) {
                $sql = "select * from multi_goods_shipment_goods where original_shipment_id in (".implode(",", $cids).")";
                $c_orders2 = $db->getAll($sql);
                foreach ($c_orders2 as $co2) {
                    if (date('H') >= 8) {
                        die("hour:8 die\n");
                    }
                    if ($co2['shipment_id'] != $order['merged_main_shipment_id']) {
                        $is_false = 5;
                        echo date("Y-m-d H:i:s").' '.'create_date:'.$co2['created_time'].' merged_main_shipment_id:'.$order['merged_main_shipment_id'].' '.json_encode($co2).' order_goods shipment_id diff'.PHP_EOL;
                        $sql = "update multi_goods_shipment_goods set shipment_id = {$order['merged_main_shipment_id']} where original_shipment_id = {$co2['original_shipment_id']}";
                        echo date("Y-m-d H:i:s").' create_date:'.$co2['created_time'].' '.$sql.PHP_EOL;
                        $db->query($sql);
                    }
                }
            }
        }
    }

    selectRdsByFacilityId($facility_id);
    $start_date = date("Y-m-d 00:00:00",strtotime(" -2 day"));
    $is_begin = true;
    $count = 0;
    $start = 0;
    while($is_begin || $count == 2000){
        $is_begin = false;
        $sql = "select m.shop_id,m.order_sn,m.district_name,m.shipping_address,m.mobile,m.seller_note,m.buyer_note,m.receive_name,m.shipment_status,m.status from multi_goods_shipment m
              where m.facility_id = {$facility_id} and m.created_time >= '{$start_date}' and m.shipment_status = 'WAIT_SHIP'
              limit {$start},2000";
         // echo date("Y-m-d H:i:s")." sql".$sql;
         selectRdsByFacilityId($facility_id);
        $orders = $db->getAll($sql);
        $count = count($orders);
        $start += $count;
        // echo 'count:'.$count.' start:'.$start.PHP_EOL;
        foreach ($orders as $order) {
            if (date('H') >= 8) {
            die("hour:8 die\n");
        }
            $sync_order = $sync_db->getRow("select town,address,receiver_phone,remark,customer_remark,receiver_name,order_status,refund_status from sync_pinduoduo_order_info where order_sn = '{$order['order_sn']}'");
            if (!empty($sync_order)){
                if($sync_order['refund_status'] == 1 && ($sync_order['town'] != $order['district_name'] ||
                // $sync_order['receiver_name'] != $order['receive_name'] ||
                // $sync_order['address'] != $order['shipping_address'] ||
                // $sync_order['receiver_phone'] != $order['mobile'] ||
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
}

echo date("Y-m-d H:i:s").'  开始检查订单表里面的系统sku，关联不到系统sku '.PHP_EOL;
foreach ($facilitys as $facility_id) { 
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

    $orders = $db->getAll("SELECT DISTINCT m.sku_id,m.goods_id,m.platform_goods_id,m.platform_sku_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN sku_mapping g on m.facility_id = g.facility_id and m.platform_goods_id = g.platform_goods_id and m.platform_sku_id = g.platform_sku_id
    where ((m.sku_id is null and g.sku_id is not null) or (m.goods_id is null and g.goods_id is not null)) and m.shop_id <> 0 and m.facility_id = {$facility_id} and m.last_updated_time >= '{$default_start_date}'");
    if (!empty($orders) && count($orders) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '5、订单表里面的系统sku，关联不到系统sku 修改系统sku:'.json_encode($orders).PHP_EOL;
        foreach ($orders as $order) {
            if (date('H') >= 8) {
            die("hour:8 die\n");
        }
            $goods_id = $db->getOne("select goods_id from goods_mapping where platform_goods_id = {$order['platform_goods_id']}  and facility_id = {$order['facility_id']}");
            $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']}");
            $goods_alias =  addslashes($db->getOne("select goods_alias from goods where goods_id = {$goods_id}  and facility_id = {$order['facility_id']}"));
            $sku_alias =  addslashes($db->getOne("select sku_alias from sku where sku_id = {$sku_id}  and facility_id = {$order['facility_id']}"));
            if (empty($sku_id)) {
                $sku_id = "null";
            }	
            if (empty($goods_id)) {
                $goods_id = "null";
            }
            $db->query("update multi_goods_shipment_goods set sku_id = {$sku_id},goods_id={$goods_id},goods_alias='{$goods_alias}',sku_alias='".addslashes($sku_alias)."' where platform_sku_id = {$order['platform_sku_id']}  and facility_id = {$order['facility_id']}");
        }
    }
}

echo date("Y-m-d H:i:s").'  开始检查订单表里面没有系统商品/sku '.PHP_EOL;
foreach ($facilitys as $facility_id) {
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

    $orders = $db->getAll("SELECT DISTINCT m.shop_id,m.order_sn,m.platform_goods_id,m.platform_sku_id,m.facility_id 
        from multi_goods_shipment_goods m LEFT JOIN sku_mapping g on m.facility_id = g.facility_id  and m.platform_sku_id = g.platform_sku_id and m.shop_id = g.shop_id
        left join multi_goods_shipment mm on m.facility_id = mm.facility_id and m.original_shipment_id = mm.shipment_id
  left join goods_mapping gm on gm.facility_id = m.facility_id and gm.platform_goods_id = m.platform_goods_id and gm.shop_id = m.shop_id
    where  ((m.goods_id is null or m.sku_id is null) or (gm.goods_id is null or g.sku_id is null) or (gm.goods_id <> m.goods_id or m.sku_id <> g.sku_id)) and m.shop_id <> 0 and m.created_time > '2019-10-01'
         and m.facility_id = {$facility_id} and mm.shipment_status = 'WAIT_SHIP' and mm.status = 'CONFIRM' and m.last_updated_time >= '{$default_start_date}'");
    if (!empty($orders) && count($orders) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '4.订单表里面没有系统商品/sku（弱管理）修改系统商品:'.json_encode($orders).PHP_EOL;
        foreach ($orders as $order) {
            if (date('H') >= 8) {
            die("hour:8 die\n");
        }
            $goods_id = $db->getOne("select goods_id from goods_mapping where platform_goods_id = {$order['platform_goods_id']}  and facility_id = {$order['facility_id']} and shop_id = {$order['shop_id']}");
            $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']} and shop_id = {$order['shop_id']}");
            if (empty($goods_id) || empty($sku_id)) {
                echo date("Y-m-d H:i:s").' '. '发现'.json_encode($order).'不存在平台goods/sku,重新下载该订单自动生成'.PHP_EOL;
                $sync_db->query("delete from goods_pinduoduo where goods_id = {$order['platform_goods_id']}");
                $sync_db->query("delete from sku_pinduoduo where sku_id = {$order['platform_sku_id']}");
                ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
                $goods_id = $db->getOne("select goods_id from goods_mapping where platform_goods_id = {$order['platform_goods_id']}  and facility_id = {$order['facility_id']}");
                $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']}");
            }
            if (!empty($goods_id)) {
                $goods_alias =  addslashes($db->getOne("select goods_alias from goods where goods_id = {$goods_id}  and facility_id = {$order['facility_id']}"));
                $db->query("update multi_goods_shipment_goods set goods_id = {$goods_id},goods_alias = '".addslashes($goods_alias)."' where platform_goods_id = {$order['platform_goods_id']} and facility_id = {$order['facility_id']}");
            }
            if (!empty($sku_id)) {
                $sku_alias =  addslashes($db->getOne("select sku_alias from sku where sku_id = {$sku_id}  and facility_id = {$order['facility_id']}"));
                $db->query("update multi_goods_shipment_goods set sku_id = {$sku_id},sku_alias = '".addslashes($sku_alias)."' where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']}");
            }
        }
    }
}

echo date("Y-m-d H:i:s").'  开始检查订单商品错乱goods/sku 匹配 '.PHP_EOL;
foreach ($facilitys as $facility_id) {
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

    $datas = $db->getAll("select distinct m.platform_sku_id,m.facility_id,s.sku_id from multi_goods_shipment_goods m
        inner join sku_mapping s on m.facility_id = s.facility_id and m.platform_sku_id = s.platform_sku_id and m.sku_id <> s.sku_id
        where m.facility_id  = {$facility_id} and m.last_updated_time >= '{$default_start_date}'");
    foreach($datas as $data){
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
        $sql = "update multi_goods_shipment_goods set sku_id = {$data['sku_id']} where facility_id = {$data['facility_id']} and platform_sku_id = {$data['platform_sku_id']}";
        echo $sql.PHP_EOL;
        $db->query($sql);
    }

    $datas = $db->getAll("select distinct m.platform_goods_id,m.facility_id,s.goods_id from multi_goods_shipment_goods m
            inner join goods_mapping s on m.facility_id = s.facility_id and m.platform_goods_id = s.platform_goods_id and m.goods_id <> s.goods_id
            where m.facility_id  = {$facility_id} and m.last_updated_time >= '{$default_start_date}'");
    foreach($datas as $data){
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
        $sql = "update multi_goods_shipment_goods set goods_id = {$data['goods_id']} where facility_id = {$data['facility_id']} and platform_goods_id = {$data['platform_goods_id']}";
        echo $sql.PHP_EOL;
        $db->query($sql);
    }
}

echo date("Y-m-d H:i:s").'  开始检查有系统商品，没有匹配关系 '.PHP_EOL;
foreach ($facilitys as $facility_id) {      
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

    $goods = $db->getAll("SELECT g.goods_id,g.facility_id from goods g inner join facility f on g.facility_id = f.facility_id 
        left join goods_mapping m on g.facility_id = m.facility_id and g.goods_id = m.goods_id
    where m.goods_id is null and f.is_force_manage_goods = 0 and g.created_type <> 'GROUP_MANAGE_CREATED' and g.facility_id = {$facility_id} 
        and g.last_updated_time >= '{$default_start_date}'");
    if (!empty($goods) && count($goods) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '1.1、有系统商品，没有匹配关系 删除这些商品:'.json_encode($goods).PHP_EOL;
        $result = postJsonData($url.'/goods/deleteGoodsBatch', json_encode($goods),0);
    }
    $goods = $db->getAll("select goods_id,facility_id from goods where facility_id  = {$facility_id} and mapping_count = 0 and created_type <> 'GROUP_MANAGE_CREATED' 
                        and last_updated_time >= '{$default_start_date}'");
    if (!empty($goods) && count($goods) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '1.2、有系统商品，没有匹配关系 删除这些商品:'.json_encode($goods).PHP_EOL;
        $result = postJsonData($url.'/goods/deleteGoodsBatch', json_encode($goods),0);
    }

    $sku = $db->getAll("SELECT g.sku_id,g.facility_id from sku g inner join facility f on g.facility_id = f.facility_id 
                        left join sku_mapping m on g.facility_id = m.facility_id and g.sku_id = m.sku_id
                        left join group_sku_mapping gs on gs.facility_id = g.facility_id and gs.sku_id = g.sku_id
    where m.sku_id is null and f.is_force_manage_goods = 0 and gs.sku_id is null and g.created_type <> 'GROUP_MANAGE_CREATED' and g.facility_id = {$facility_id} and g.last_updated_time >= '{$default_start_date}'");
    if (!empty($sku) && count($sku) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '1.3、有系统sku，没有匹配关系 删除这些sku:'.json_encode($sku).PHP_EOL;
        $result = postJsonData($url.'/goods/deleteSkuBatch', json_encode($sku),0);
    }
    $sku = $db->getAll("select sku_id,facility_id from sku where facility_id  = {$facility_id} and mapping_count = 0 and created_type <> 'GROUP_MANAGE_CREATED' and last_updated_time >= '{$default_start_date}'");
    if (!empty($sku) && count($sku) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '1.4、有系统sku，没有匹配关系 删除这些sku:'.json_encode($sku).PHP_EOL;
        $result = postJsonData($url.'/goods/deleteSkuBatch', json_encode($sku),0);
    }
}

echo date("Y-m-d H:i:s").'  sku/goods消失 '.PHP_EOL;
foreach ($facilitys as $facility_id) {      
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

   $params = $db->getAll("SELECT s.facility_id,sku_mapping_id,s.platform_sku_id from sku_mapping s left join sku sk on s.facility_id = sk.facility_id and s.sku_id = sk.sku_id
    where s.facility_id = {$facility_id} and sk.sku_id  is null and s.last_updated_time >= '{$default_start_date}'");
   if (!empty($params)) {
        echo date("Y-m-d H:i:s").' '. '发现以下sku_mapping sku消失 删除匹配'.json_encode($params).PHP_EOL;
   }
   foreach ($params as $param) {        
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
        if (!$db->getOne("select 1 from platform_sku where platform_sku_id = {$param['platform_sku_id']} and facility_id = {$facility_id}")) {
            $db->query("delete from sku_mapping where sku_mapping_id = {$param['sku_mapping_id']} and facility_id = {$facility_id}");
        }else{
           $result = postJsonData($url.'/goods/deleteSkuMapping', json_encode($param),0);
        }
    }

    $params = $db->getAll("SELECT s.facility_id,sku_mapping_id,s.platform_sku_id from sku_mapping s left join goods sk on s.facility_id = sk.facility_id and s.goods_id = sk.goods_id
    where s.facility_id = {$facility_id} and sk.goods_id  is null and s.last_updated_time >= '{$default_start_date}'");
    if (!empty($params)) {
        echo date("Y-m-d H:i:s").' '. '发现以下sku_mapping goods消失 删除匹配'.json_encode($params).PHP_EOL;
    }   
   foreach ($params as $param) {        
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
        if (!$db->getOne("select 1 from platform_sku where platform_sku_id = {$param['platform_sku_id']} and facility_id = {$facility_id}")) {
            $db->query("delete from sku_mapping where sku_mapping_id = {$param['sku_mapping_id']} and facility_id = {$facility_id}");
        }else{
           $result = postJsonData($url.'/goods/deleteSkuMapping', json_encode($param),0);
        }
    }


    $params = $db->getAll("SELECT s.facility_id,goods_mapping_id,s.platform_goods_id from goods_mapping s left join goods sk on s.facility_id = sk.facility_id and s.goods_id = sk.goods_id
    where s.facility_id = {$facility_id} and sk.goods_id  is null and s.last_updated_time >= '{$default_start_date}'");
    if (!empty($params)) {
        echo date("Y-m-d H:i:s").' '. '发现以下goods_mapping goods消失 删除匹配'.json_encode($params).PHP_EOL;
    }   
   foreach ($params as $param) {        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
    if (!$db->getOne("select 1 from platform_goods where platform_goods_id = {$param['platform_goods_id']} and facility_id = {$facility_id}")) {
            $db->query("delete from goods_mapping where goods_mapping_id = {$param['goods_mapping_id']} and facility_id = {$facility_id}");
        }else{
           $result = postJsonData($url.'/goods/deleteGoodsMapping', json_encode($param),0);
        }
    }
}

echo date("Y-m-d H:i:s").'  开始检查订单表里面的商品简称，与实际商品简称不符 '.PHP_EOL;
foreach ($facilitys as $facility_id) {      
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);

    $orders = $db->getAll("SELECT distinct m.goods_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN goods g on m.facility_id = g.facility_id and m.goods_id = g.goods_id
    where  (m.goods_alias <> g.goods_alias or ((m.goods_alias is NULL or m.goods_alias = '')  and (g.goods_alias is not null and g.goods_alias <> '')) or ((g.goods_alias is NULL or g.goods_alias = '')  and (m.goods_alias is not null and m.goods_alias <> ''))) 
    and m.facility_id = {$facility_id} and m.last_updated_time >= '{$default_start_date}'");
    if (!empty($orders) && count($orders) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '6、订单表里面的商品简称，与实际商品简称不符 修改商品简称:'.json_encode($orders).PHP_EOL;
        foreach ($orders as $order) {
            if (date('H') >= 8) {
                die("hour:8 die\n");
            }
            $goods_alias = $db->getOne("select goods_alias from goods where goods_id = {$order['goods_id']} and facility_id = {$order['facility_id']}");
            $redis->hset('goods_alias', $order['goods_id'], $goods_alias);
            $db->query("update multi_goods_shipment_goods set goods_alias = '".addslashes($goods_alias)."' where goods_id = {$order['goods_id']} and facility_id = {$order['facility_id']}");
        }
    }

    $orders = $db->getAll("SELECT distinct m.sku_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN sku s on m.facility_id = s.facility_id and m.sku_id = s.sku_id
    where (m.sku_alias <> s.sku_alias or ((m.sku_alias is NULL or m.sku_alias = '')  and (s.sku_alias is not null and s.sku_alias <> '')) or ((s.sku_alias is NULL or s.sku_alias = '')  and (m.sku_alias is not null and m.sku_alias <> ''))) and m.facility_id = {$facility_id} and m.last_updated_time >= '{$default_start_date}'");
    if (!empty($orders) && count($orders) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '6、订单表里面的sku简称，与实际sku简称不符 修改sku简称:'.json_encode($orders).PHP_EOL;
        foreach ($orders as $order) {
            if (date('H') >= 8) {
                die("hour:8 die\n");
            }
            if (!empty($order['sku_id'])) {
                $sku_alias = $db->getOne("select sku_alias from sku where sku_id = {$order['sku_id']} and facility_id = {$order['facility_id']}");
                $redis->hset('sku_alias', $order['sku_id'], $sku_alias);
                $db->query("update multi_goods_shipment_goods set sku_alias = '".addslashes($sku_alias)."' where sku_id = {$order['sku_id']} and facility_id = {$order['facility_id']}");
            }
        }
    }
}

echo date("Y-m-d H:i:s").'  开始检查没有multi '.PHP_EOL;
foreach ($facilitys as $facility_id) { 
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);
     $sql = "
    SELECT m.order_sn,m.shop_id from multi_goods_shipment m left join multi_goods_shipment_goods mg
    on m.facility_id = mg.facility_id and m.shipment_id = mg.original_shipment_id
    where m.facility_id = {$facility_id} and mg.order_sn is null and m.last_updated_time >= '{$default_start_date}'";
        // echo date("Y-m-d H:i:s").' '. $sql.PHP_EOL;
        $data = $db->getAll($sql);
        
        // var_dump($data);
        if (!empty($data)) {
            foreach ($data as $d) {
                if (date('H') >= 8) {
                    die("hour:8 die\n");
                }
                $db->query("delete from multi_goods_shipment where order_sn in ('{$d['order_sn']}')");
                $db->query("delete from order_info where order_sn in ('{$d['order_sn']}')");
                $db->query("delete from shipment where order_sn in ('{$d['order_sn']}')");
                $sync_db->query("delete from sync_pinduoduo_order_info where order_sn in ('{$d['order_sn']}')");
                ExpressApiService::downloadSingleOrder($d['shop_id'],'pinduoduo',$d['order_sn']);
            }
        }
    }	

echo date("Y-m-d H:i:s").'  开始检查系统sku表,库存表中没有相关sku的库存 '.PHP_EOL;
foreach ($facilitys as $facility_id){
    if (date('H') >= 8) {
        die("hour:8 die\n");
    }
    selectRdsByFacilityId($facility_id);
    $sql = "
    SELECT 
        s.facility_id ,
        s.goods_id ,
        s.sku_id 
    FROM
        sku s 
        left join inventory i on s.sku_id = i.sku_id and s.facility_id = i.facility_id 
    WHERE
        s.facility_id = {$facility_id}
        and i.sku_id is NULL and s.last_updated_time >= '{$default_start_date}'
    ";
    $data = $db->getAll($sql);
    if(!empty($data)){
        echo date("Y-m-d H:i:s"). ' 库存表中没有相关sku信息，向库存表中插入sku信息 '.PHP_EOL;
        foreach ($data as $d){
            if (date('H') >= 8) {
                die("hour:8 die\n");
            }
            $db->query("INSERT INTO inventory (facility_id, goods_id, sku_id, quantity)
                VALUES({$d['facility_id']}, {$d['goods_id']}, {$d['sku_id']}, 0)");
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
    }else{
        $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
    }
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}

function refreshGroupSkuWeightAndPrice($platform_sku_id,$facility_id){
        try{
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
                    sum(i.purchase_price*g.number) purchase_price,
                    sum(sk.package_fee*g.number) s_package_fee,
                    g.package_fee g_package_fee
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
            // var_dump($group_goods);
            if (!empty($group_goods)&&!empty($group_goods['sku_id'])) {
                echo date("Y-m-d H:i:s").' '."update sku set weight = {$group_goods['weight']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
                $db->query("update sku set weight = {$group_goods['weight']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
                echo date("Y-m-d H:i:s").' '."update inventory set purchase_price = {$group_goods['purchase_price']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
                $db->query("update inventory set purchase_price = {$group_goods['purchase_price']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
                if (!empty($group_goods['s_package_fee']) || !empty($group_goods['g_package_fee'])) {
                    if (!empty($group_goods['g_package_fee'])) {
                        $group_goods['s_package_fee'] = $group_goods['g_package_fee'];
                    }
                    $db->query("update sku set package_fee = {$group_goods['s_package_fee']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
                    echo date("Y-m-d H:i:s").' '."update sku set package_fee = {$group_goods['s_package_fee']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
                }
                $redis_value = ['sku_id'=>$group_goods['sku_id'],'facility_id'=>$facility_id];
                $redis->lpush("order_weight_update", json_encode($redis_value));
            }
        }catch(\Exception $e){
            echo $e->getMessage().PHP_EOL;
        }
    }

function refreshArrToShopMap($arr){
    $map = [];
    foreach ($arr as $value) {
        if (date('H') >= 8) {
            die("hour:8 die\n");
        }
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

