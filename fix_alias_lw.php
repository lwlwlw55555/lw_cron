<?php
require("includes/init.php");
$url = 'http://localhost:8080/erp_syncinner_prod';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
$redis = getFacilityRedis();

echo date("Y-m-d H:i:s").PHP_EOL;

/*1*/
// echo date("Y-m-d H:i:s").'  开始检查未删除店铺数据'.PHP_EOL;
// $shops = $sync_db->getCol("SELECT DISTINCT g.shop_id from goods_pinduoduo g LEFT JOIN shop s on g.shop_id = s.shop_id WHERE s.shop_id is null");
// if (!empty($shops) && count($shops)>0) {
// 	$str = implode(",", $shops);
// 	var_dump($str);
// 	while($sync_db->getOne("select count(1) from goods_pinduoduo where shop_id in ({$str})") > 0){
// 		$ids = $sync_db->getCol("select goods_id from goods_pinduoduo where shop_id in ({$str}) limit 0,10000");
// 		$sql = "delete from goods_pinduoduo where shop_id in ({$str}) and goods_id in (".implode(",", $ids).")";
// 		echo date("Y-m-d H:i:s").' '.$sql;
// 		$sync_db->query($sql);
// 	}
    
// 	while($sync_db->getOne("select count(1) from sku_pinduoduo where shop_id in ({$str})") > 0){
// 		$ids = $sync_db->getCol("select sku_id from sku_pinduoduo where shop_id in ({$str}) limit 0,10000");
// 		$sql = "delete from sku_pinduoduo where shop_id in ({$str}) and sku_id in (".implode(",", $ids).")";
// 		echo date("Y-m-d H:i:s").' '.$sql;
// 		$sync_db->query($sql);
// 	}


// 	while($sync_db->getOne("select count(1) from sync_pinduoduo_order_info where shop_id in ({$str})") > 0){
// 		$ids = $sync_db->getCol("select order_id from sync_pinduoduo_order_info where shop_id in ({$str}) limit 0,10000");
// 		$sql = "delete from sync_pinduoduo_order_info where shop_id in ({$str}) and order_id in (".implode(",", $ids).")";
// 		echo date("Y-m-d H:i:s").' '.$sql;
// 		$sync_db->query($sql);
// 	}
// }
// if (isset($opt_params['mod_index']) && $opt_params['mod_index'] == 0) {
//     $shops = $db->getAll("SELECT DISTINCT g.shop_id,g.facility_id from platform_goods g LEFT JOIN shop s on g.shop_id = s.shop_id and g.facility_id = s.default_facility_id WHERE s.shop_id is null");
//     if (!empty($shops) && count($shops)>0) {
//         foreach ($shops as $shop) {
//             if($db->getOne("select 1 from shop_back where shop_id = {$shop['shop_id']} and default_facility_id = {$shop['facility_id']}")){
//                 $sql = "update shop_back set is_deleted_data = 0 where shop_id = {$shop['shop_id']} and default_facility_id = {$shop['facility_id']}";
//                 echo date("Y-m-d H:i:s").' '. $sql.PHP_EOL;
//                 $db->query($sql);
//             }else{
//                 $sql = "insert into shop_back(shop_id,default_facility_id,platform_shop_id,platform_shop_secret,party_id,is_deleted_data) VALUES ({$shop['shop_id']} and default_facility_id = {$shop['facility_id']},{$shop['facility_id']},{$shop['shop_id']} and default_facility_id = {$shop['facility_id']},'',{$shop['shop_id']} and default_facility_id = {$shop['facility_id']},0)";
//                 echo date("Y-m-d H:i:s").' '. $sql.PHP_EOL;
//                 $db->query($sql);
//             }
//             // while($db->getOne("select count(1) from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select id from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from platform_sku where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select id from platform_sku where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from platform_sku where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }


//             // while($db->getOne("select count(1) from goods_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select goods_mapping_id from goods_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and goods_mapping_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from sku_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select sku_mapping_id from sku_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from sku_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and sku_mapping_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }
            
//             // while($db->getOne("select count(1) from order_info where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select order_id from order_info where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from order_info where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and order_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select shipment_id from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and shipment_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select order_goods_id from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and order_goods_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select shipment_id from shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and shipment_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from order_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select order_goods_id from order_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from order_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and order_goods_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from goods_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select goods_mapping_history_id from goods_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from goods_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and goods_mapping_history_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select sku_mapping_history_id from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and sku_mapping_history_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select sku_mapping_history_id from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and sku_mapping_history_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from finance_bill_order where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select finance_bill_order_id from finance_bill_order where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and finance_bill_order_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }

//             // while($db->getOne("select count(1) from finance_bill_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']}") > 0){
//             // 	$ids = $db->getCol("select finance_bill_goods_id from finance_bill_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} limit 0,10000");
//             // 	$sql = "delete from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['facility_id']} and finance_bill_goods_id in (".implode(",", $ids).")";
//             // 	echo date("Y-m-d H:i:s").' '.$sql;
//             // 	$db->query($sql);
//             // }
//             // $db->query("update shop_back set is_deleted_data = 0 where shop_id = {$shop['shop_id']}");
//         }
//     }
// }

// echo date("Y-m-d H:i:s").'  开始检查order_info_shipment_id'.PHP_EOL;
// $orders = $db->getAll("select order_sn,facility_id from order_info where shipment_id is null");
// if (!empty($orders)) {
// 	foreach ($orders as $order) {
// 		$shipment_id = $db->getOne("select shipment_id from shipment where order_sn = '{$order['order_sn']}' and facility_id = {$order['facility_id']}");
// 		if (!empty($shipment_id)) {
// 			$sql = "update order_info set shipment_id = {$shipment_id} where order_sn = '{$order['order_sn']}' and facility_id = {$order['facility_id']}";
// 			echo date("Y-m-d H:i:s").' '. $sql.PHP_EOL;
// 			$db->query($sql);
// 		}
// 	}
// }



// echo date("Y-m-d H:i:s").'  开始检查multi_goods_shipment shipment_status 不一致'.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);
//  	$orders = $db->getCol("select order_sn from multi_goods_shipment where shipping_time is NOT  null and shipment_status = 'WAIT_SHIP' and facility_id = {$facility_id}");
//  	if (!empty($orders)) {
//  		$sql = "update multi_goods_shipment set shipment_status = 'SHIPPED' where order_sn in ('".implode("','", $orders)."') and facility_id = {$facility_id}";
//  		echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//  		$db->query($sql);
//  	}
// }

// if (isset($opt_params['mod_index']) && $opt_params['mod_index'] == 9) {
    
//     echo date("Y-m-d H:i:s").'  开始检查group_sku_not_exist '.PHP_EOL;
//     $groups = $db->getAll("SELECT g.group_sku_mapping_id,g.platform_sku_id,g.sku_id,h.platform_sku_id as new_platform_sku_id,g.facility_id,sm.sku_id as new_sku_id 
//         from group_sku_mapping g
//       LEFT JOIN sku s on g.facility_id = s.facility_id and g.sku_id = s.sku_id
//       LEFT JOIN sku_mapping_history h on h.facility_id = g.facility_id and g.sku_id = h.sku_id
//       LEFT JOIN sku_mapping sm on sm.facility_id = g.facility_id and sm.platform_sku_id = h.platform_sku_id
//     where s.sku_id is null");
//     if (!empty($groups)) {
//         var_dump($groups);
//     }
//     foreach ($groups as $group) {
//         if (!empty($group['new_platform_sku_id']) && !empty($group['new_sku_id'])) {
//             $sql = "update group_sku_mapping set sku_id = {$group['new_sku_id']} where group_sku_mapping_id = {$group['group_sku_mapping_id']} and facility_id = {$group['facility_id']}";
//             echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//             $db->query($sql);
//             echo PHP_EOL.PHP_EOL;
//             refreshGroupSkuWeightAndPrice($group['platform_sku_id'],$group['facility_id']);
//         }else{
//             echo date("Y-m-d H:i:s").' '.'platform_sku_id:'.$group['platform_sku_id'].' 发现有不存在的历史记录 删除该组合商品'.PHP_EOL;
//             $sku_ids = $db->getCol("select sku_id from group_sku_mapping where platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}");
//             if (!empty($sku_ids)) {
//                 $sql = "update platform_sku set is_group = 0 where platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}";
//                 echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//                 $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}");
//                 if (!empty($sku_id)) {
//                     $sql = "update sku set is_group = 0 where sku = {$sku_id} and facility_id = {$group['facility_id']}";
//                     echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//                     $db->query($sql);
//                 }
//                 $sql = "delete from group_sku_mapping where platform_sku_id = {$group['platform_sku_id']} and facility_id = {$group['facility_id']}";
//                 echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//                 $db->query($sql);
//             }
            
//         }
//     }

//     echo date("Y-m-d H:i:s").'  开始检查group_sku_mapping_not_exist '.PHP_EOL;
//     $groups = $db->getAll("SELECT g.group_sku_mapping_id,g.platform_sku_id,g.sku_id,s.sku_alias,gd.goods_alias,g.facility_id,s.created_type
//         from group_sku_mapping g
//       LEFT JOIN sku s on g.facility_id = s.facility_id and g.sku_id = s.sku_id
//       LEFT JOIN goods gd on gd.facility_id = g.facility_id and gd.goods_id = s.goods_id
//       LEFt join sku_mapping sm on sm.facility_id = g.facility_id and sm.sku_id = g.sku_id
//       where sm.sku_id is null");
//     if (!empty($groups)) {
//         var_dump($groups);
//     }
//     foreach ($groups as $group) {
//         if ('GROUP_MANAGE_CREATED' != $group['created_type'] && !empty($group['sku_alias'])) {
//             $new_sku_id = $db->getOne("select s.sku_id from sku s inner join sku_mapping m on s.sku_id = m.sku_id and s.facility_id = m.facility_id 
//                     inner join goods g on g.facility_id = s.facility_id and m.goods_id = s.goods_id
//                  where s.sku_alias = '{$group['sku_alias']}' and g.goods_alias = '{$group['goods_alias']}'");
//             if (empty($new_sku_id)) {
//                 $new_sku_id = $db->getOne("select s.sku_id from sku s inner join sku_mapping m on s.sku_id = m.sku_id and s.facility_id = m.facility_id where s.sku_alias = '{$group['sku_alias']}'");
//             }
//             if (!empty($new_sku_id)) {
//                 $sql = "update group_sku_mapping set sku_id = {$new_sku_id} where group_sku_mapping_id = {$group['group_sku_mapping_id']} and facility_id = {$group['facility_id']}";
//                 echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//                 // $db->query($sql);
//                 echo PHP_EOL.PHP_EOL;
//                 // refreshGroupSkuWeightAndPrice($group['platform_sku_id'],$group['facility_id']);
//             }
//         }
//     }
// }

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
    }else
    {
	    //selectRdsByFacilityId($op_params['facility_id']);
        //$facilitys = $db->getCol("select facility_id from facility where facility_id = {$opt_params['facility_id']}");
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

// echo date("Y-m-d H:i:s").'  开始检查shop与shop_extension不一致 '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);
//     $sync_shops = $sync_db->getAll("select shop_id,enabled from shop where default_facility_id = {$facility_id}");
//     if (!empty($sync_shops)) {
//         $shop_ids = [];
//         foreach ($sync_shops as $shop) {
//             $shop_ids[] = $shop['shop_id'];
//         }
//         try{
//             $sync_shop_extensions = $sync_db->getAll("select shop_id,enabled from shop_extension where shop_id in (".implode(",", $shop_ids).")");
//             $shops = $db->getAll("select shop_id,enabled from shop where default_facility_id = {$facility_id}");
//             $sync_shops = refreshArrToShopMap($sync_shops);
//             $sync_shop_extensions = refreshArrToShopMap($sync_shop_extensions);
//             $shops = refreshArrToShopMap($shops);
//             foreach ($shop_ids as $shop_id) {
//                 if ($sync_shops[$shop_id] <> $sync_shop_extensions[$shop_id] || 
//                     $shops[$shop_id] <> $sync_shops[$shop_id] ||
//                     $shops[$shop_id] <> $sync_shop_extensions[$shop_id]) {
//                     $sql = "update shop set enabled = 1 where shop_id = {$shop_id}";
//                     echo date("Y-m-d H:i:s").' 发现shop_id:'.$shop_id.' shop与shop_extension不一致'.PHP_EOL;
//                     echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//                     // $db->query($sql);
//                     // $sync_db->query($sql);
//                     $sql = "update shop_extension set enabled = 1 where shop_id = {$shop_id}";
//                     echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
//                     // $sync_db->query($sql);
//                 }
//             }
//         }catch(Exception $e){
//             echo date("Y-m-d H:i:s").'检查shop与shop_extension不一致 facility_id:'.$facility_id.' exception:'.$e->getMessage();
//         }
//     }
// }

// echo date("Y-m-d H:i:s").'  开始检查group_sku is_group 不一致'.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);

// 	$no_is_group_skus = $db->getAll("SELECT p.platform_sku_id,s.sku_id from platform_sku p inner JOIN sku_mapping m
// 	    on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
// 	  inner join sku s on s.facility_id = p.facility_id and s.sku_id = m.sku_id
// 	where p.facility_id = {$facility_id} and p.is_group <> s.is_group");
// 	if (!empty($no_is_group_skus)) {
// 		echo date("Y-m-d H:i:s")." 发现以下sku is_group不一致".PHP_EOL;
// 		var_dump($no_is_group_skus);
// 	}
// 	foreach ($no_is_group_skus as $sku) {
// 		$platform_sku_id = $db->getOne("SELECT g.platform_sku_id from group_sku_mapping g inner join sku_mapping m
// 	    on g.facility_id = m.facility_id and g.platform_sku_id = m.platform_sku_id
// 	    where m.sku_id = {$sku['sku_id']} and g.facility_id = {$facility_id}");
// 	    if (!empty($platform_sku_id)) {
// 	    	$groups = $db->getAll("select sku_id,number,package_fee from group_sku_mapping where platform_sku_id = {$platform_sku_id} and facility_id = {$facility_id}");
// 	    	foreach ($groups as $key => $group) {
// 	    		$group['package_fee'] = empty($group['package_fee'])?"null":$group['package_fee'];
// 	    		$sql = "insert into group_sku_mapping(facility_id,platform_sku_id,sku_id,number,package_fee) values ({$facility_id},{$sku['platform_sku_id']},{$group['sku_id']},{$group['number']},{$group['package_fee']})";
// 	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
// 	    		$db->query($sql);
// 	    	}
// 	    	$sql = "update sku set is_group = 1 where sku_id = {$sku['sku_id']} and facility_id = {$facility_id}";
// 	    	echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
// 	    	$db->query($sql);
//             refreshGroupSkuWeightAndPrice($sku['platform_sku_id'],$facility_id);
// 			$platform_sku_ids = $db->getCol("select platform_sku_id from sku_mapping where facility_id = {$facility_id} and sku_id = {$sku['sku_id']}");
// 	    	if (!empty($platform_sku_ids)) {
// 	    		$sql = "update platform_sku set is_group = 1 where platform_sku_id in (".implode(",", $platform_sku_ids).") and facility_id = {$facility_id}";
// 	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
// 	    		$db->query($sql);
// 	    	}
// 	    }else{
// 	    	$sql = "update sku set is_group = 0 where sku_id = {$sku['sku_id']} and facility_id = {$facility_id}";
// 	    	echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
// 	    	$db->query($sql);
// 	    	$platform_sku_ids = $db->getCol("select platform_sku_id from sku_mapping where facility_id = {$facility_id} and sku_id = {$sku['sku_id']}");
// 	    	if (!empty($platform_sku_ids)) {
// 	    		$sql = "update platform_sku set is_group = 0 where platform_sku_id in (".implode(",", $platform_sku_ids).") and facility_id = {$facility_id}";
// 	    		echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
// 	    		$db->query($sql);
// 	    	}
// 	    }

//         $no_is_group_skus2 = $db->getAll("SELECT p.platform_sku_id,s.sku_id from platform_sku p 
//             inner join sku_mapping s on s.facility_id = p.facility_id and p.platform_sku_id = s.platform_sku_id
//             left JOIN group_sku_mapping m on p.facility_id = m.facility_id and p.platform_sku_id = m.platform_sku_id
//         where p.facility_id = {$facility_id} and p.is_group = 1 and m.platform_sku_id is null");
//         if (!empty($no_is_group_skus2)) {
//             echo date("Y-m-d H:i:s")." 发现以下paltform_sku group不存在".PHP_EOL;
//             var_dump($no_is_group_skus);
//         }
//         foreach ($no_is_group_skus2 as $sku) {
//             $sql = "update sku set is_group = 0 where sku_id = {$sku['sku_id']} and facility_id = {$facility_id}";
//             echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
//             $db->query($sql);
//             $platform_sku_ids = $db->getCol("select platform_sku_id from sku_mapping where facility_id = {$facility_id} and sku_id = {$sku['sku_id']}");
//             if (!empty($platform_sku_ids)) {
//                 $sql = "update platform_sku set is_group = 0 where platform_sku_id in (".implode(",", $platform_sku_ids).") and facility_id = {$facility_id}";
//                 echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
//                 $db->query($sql);
//             }
//         }
// 	}

// 	$no_mapping_sku  = $db->getAll("SELECT g.group_sku_mapping_id,g.sku_id,sk.sku_alias from group_sku_mapping g
// 			  inner join sku sk on sk.facility_id = g.facility_id and sk.sku_id = g.sku_id
// 			  left JOIN sku_mapping s on g.sku_id = s.sku_id and g.facility_id = s.facility_id
// 			  where s.sku_id is null and g.facility_id = {$facility_id} and sk.sku_alias <> '' and sk.sku_alias is not null");
// 	if (!empty($no_mapping_sku)) {
// 		echo date("Y-m-d H:i:s")." 发现以下sku 作为被组合sku mapping is null".PHP_EOL;
// 		var_dump($no_mapping_sku);
// 	}
// 	foreach ($no_mapping_sku as $sku) {
// 		$can_group_sku = $db->getOne("select s.sku_id from sku s
// 				  inner join sku_mapping sk on s.facility_id = sk.facility_id and s.sku_id = sk.sku_id
// 				where s.facility_id = {$facility_id} and sku_alias = '{$sku['sku_alias']}'");
// 		if (!empty($can_group_sku)) {
// 			$sql = "update group_sku_mapping set sku_id = {$can_group_sku} where group_sku_mapping_id = {$sku['group_sku_mapping_id']} and facility_id = {$facility_id}";
// 			echo date("Y-m-d H:i:s")." {$sql}".PHP_EOL;
// 			$db->query($sql);
// 		}
// 	}
// }

// echo date("Y-m-d H:i:s").'  开始检查订单表里面的系统sku，关联不到系统sku '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);

//     $orders = $db->getAll("SELECT DISTINCT m.sku_id,m.goods_id,m.platform_goods_id,m.platform_sku_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN sku_mapping g on m.facility_id = g.facility_id and m.platform_goods_id = g.platform_goods_id and m.platform_sku_id = g.platform_sku_id
//     where ((m.sku_id is null and g.sku_id is not null) or (m.goods_id is null and g.goods_id is not null)) and m.shop_id <> 0 and m.facility_id = {$facility_id}");
//     if (!empty($orders) && count($orders) > 0) {
//         echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
//         echo date("Y-m-d H:i:s").' '. '5、订单表里面的系统sku，关联不到系统sku 修改系统sku:'.json_encode($orders).PHP_EOL;
//         foreach ($orders as $order) {
//             $goods_id = $db->getOne("select goods_id from goods_mapping where platform_goods_id = {$order['platform_goods_id']}  and facility_id = {$order['facility_id']}");
//             $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']}");
//             $goods_alias =  addslashes($db->getOne("select goods_alias from goods where goods_id = {$goods_id}  and facility_id = {$order['facility_id']}"));
//             $sku_alias =  addslashes($db->getOne("select sku_alias from sku where sku_id = {$sku_id}  and facility_id = {$order['facility_id']}"));
//             if (empty($sku_id)) {
//                 $sku_id = "null";
//             }	
//             if (empty($goods_id)) {
//                 $goods_id = "null";
//             }
//             $db->query("update multi_goods_shipment_goods set sku_id = {$sku_id},goods_id={$goods_id},goods_alias='{$goods_alias}',sku_alias='".addslashes($sku_alias)."' where platform_sku_id = {$order['platform_sku_id']}  and facility_id = {$order['facility_id']}");
//         }
//     }
// }

// echo date("Y-m-d H:i:s").'  开始检查订单表里面没有系统商品/sku '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);

//     $orders = $db->getAll("SELECT DISTINCT m.shop_id,m.order_sn,m.platform_goods_id,m.platform_sku_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN sku_mapping g on m.facility_id = g.facility_id  and m.platform_sku_id = g.platform_sku_id and m.shop_id = g.shop_id
//   left join goods_mapping gm on gm.facility_id = m.facility_id and gm.platform_goods_id = m.platform_goods_id and gm.shop_id = m.shop_id
//     where  ((m.goods_id is null or m.sku_id is null) or (gm.goods_id is null or g.sku_id is null) or (gm.goods_id <> m.goods_id or m.sku_id <> g.sku_id)) and m.shop_id <> 0 and m.created_time > '2019-10-01'
//          and m.facility_id = {$facility_id}");
//     if (!empty($orders) && count($orders) > 0) {
//         echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
//         echo date("Y-m-d H:i:s").' '. '4.订单表里面没有系统商品/sku（弱管理）修改系统商品:'.json_encode($orders).PHP_EOL;
//         foreach ($orders as $order) {
//             $goods_id = $db->getOne("select goods_id from goods_mapping where platform_goods_id = {$order['platform_goods_id']}  and facility_id = {$order['facility_id']} and shop_id = {$order['shop_id']}");
//             $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']} and shop_id = {$order['shop_id']}");
//             if (empty($goods_id) || empty($sku_id)) {
//                 echo date("Y-m-d H:i:s").' '. '发现'.json_encode($order).'不存在平台goods/sku,重新下载该订单自动生成'.PHP_EOL;
//                 $sync_db->query("delete from goods_pinduoduo where goods_id = {$order['platform_goods_id']}");
//                 $sync_db->query("delete from sku_pinduoduo where sku_id = {$order['platform_sku_id']}");
//                 ExpressApiService::downloadSingleOrder($order['shop_id'],'pinduoduo',$order['order_sn']);
//                 $goods_id = $db->getOne("select goods_id from goods_mapping where platform_goods_id = {$order['platform_goods_id']}  and facility_id = {$order['facility_id']}");
//                 $sku_id = $db->getOne("select sku_id from sku_mapping where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']}");
//             }
//             if (!empty($goods_id)) {
//                 $goods_alias =  addslashes($db->getOne("select goods_alias from goods where goods_id = {$goods_id}  and facility_id = {$order['facility_id']}"));
//                 $db->query("update multi_goods_shipment_goods set goods_id = {$goods_id},goods_alias = '".addslashes($goods_alias)."' where platform_goods_id = {$order['platform_goods_id']} and facility_id = {$order['facility_id']}");
//             }
//             if (!empty($sku_id)) {
//                 $sku_alias =  addslashes($db->getOne("select sku_alias from sku where sku_id = {$sku_id}  and facility_id = {$order['facility_id']}"));
//                 $db->query("update multi_goods_shipment_goods set sku_id = {$sku_id},sku_alias = '".addslashes($sku_alias)."' where platform_sku_id = {$order['platform_sku_id']} and facility_id = {$order['facility_id']}");
//             }
//         }
//     }
// }

// echo date("Y-m-d H:i:s").'  开始检查订单商品错乱goods/sku 匹配 '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);

//     $datas = $db->getAll("select distinct m.platform_sku_id,m.facility_id,s.sku_id from multi_goods_shipment_goods m
//         inner join sku_mapping s on m.facility_id = s.facility_id and m.platform_sku_id = s.platform_sku_id and m.sku_id <> s.sku_id
//         where m.facility_id  = {$facility_id}");
//     foreach($datas as $data){
//         $sql = "update multi_goods_shipment_goods set sku_id = {$data['sku_id']} where facility_id = {$data['facility_id']} and platform_sku_id = {$data['platform_sku_id']}";
//         echo $sql.PHP_EOL;
//         $db->query($sql);
//     }

//     $datas = $db->getAll("select distinct m.platform_goods_id,m.facility_id,s.goods_id from multi_goods_shipment_goods m
//             inner join goods_mapping s on m.facility_id = s.facility_id and m.platform_goods_id = s.platform_goods_id and m.goods_id <> s.goods_id
//             where m.facility_id  = {$facility_id}");
//     foreach($datas as $data){
//         $sql = "update multi_goods_shipment_goods set goods_id = {$data['goods_id']} where facility_id = {$data['facility_id']} and platform_goods_id = {$data['platform_goods_id']}";
//         echo $sql.PHP_EOL;
//         $db->query($sql);
//     }
// }

// echo date("Y-m-d H:i:s").'  开始检查有系统商品，没有匹配关系 '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);

//     $goods = $db->getAll("SELECT g.goods_id,g.facility_id from goods g inner join facility f on g.facility_id = f.facility_id 
//         left join goods_mapping m on g.facility_id = m.facility_id and g.goods_id = m.goods_id
//     where m.goods_id is null and f.is_force_manage_goods = 0 and g.created_type <> 'GROUP_MANAGE_CREATED' and g.facility_id = {$facility_id}");
//     if (!empty($goods) && count($goods) > 0) {
//         echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
//         echo date("Y-m-d H:i:s").' '. '1.1、有系统商品，没有匹配关系 删除这些商品:'.json_encode($goods).PHP_EOL;
//         $result = postJsonData($url.'/goods/deleteGoodsBatch', json_encode($goods),0);
//     }
//     $goods = $db->getAll("select goods_id,facility_id from goods where facility_id  = {$facility_id} and mapping_count = 0 and created_type <> 'GROUP_MANAGE_CREATED'");
//     if (!empty($goods) && count($goods) > 0) {
//         echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
//         echo date("Y-m-d H:i:s").' '. '1.2、有系统商品，没有匹配关系 删除这些商品:'.json_encode($goods).PHP_EOL;
//         $result = postJsonData($url.'/goods/deleteGoodsBatch', json_encode($goods),0);
//     }

//     $sku = $db->getAll("SELECT g.sku_id,g.facility_id from sku g inner join facility f on g.facility_id = f.facility_id 
//                         left join sku_mapping m on g.facility_id = m.facility_id and g.sku_id = m.sku_id
//                         left join group_sku_mapping gs on gs.facility_id = g.facility_id and gs.sku_id = g.sku_id
//     where m.sku_id is null and f.is_force_manage_goods = 0 and gs.sku_id is null and g.created_type <> 'GROUP_MANAGE_CREATED' and g.facility_id = {$facility_id}");
//     if (!empty($sku) && count($sku) > 0) {
//         echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
//         echo date("Y-m-d H:i:s").' '. '1.3、有系统sku，没有匹配关系 删除这些sku:'.json_encode($sku).PHP_EOL;
//         $result = postJsonData($url.'/goods/deleteSkuBatch', json_encode($sku),0);
//     }
//     $sku = $db->getAll("select sku_id,facility_id from sku where facility_id  = {$facility_id} and mapping_count = 0 and created_type <> 'GROUP_MANAGE_CREATED'");
//     if (!empty($sku) && count($sku) > 0) {
//         echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
//         echo date("Y-m-d H:i:s").' '. '1.4、有系统sku，没有匹配关系 删除这些sku:'.json_encode($sku).PHP_EOL;
//         $result = postJsonData($url.'/goods/deleteSkuBatch', json_encode($sku),0);
//     }
// }

// echo date("Y-m-d H:i:s").'  sku/goods消失 '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);

//    $params = $db->getAll("SELECT s.facility_id,sku_mapping_id,s.platform_sku_id from sku_mapping s left join sku sk on s.facility_id = sk.facility_id and s.sku_id = sk.sku_id
//     where s.facility_id = {$facility_id} and sk.sku_id  is null");
//    if (!empty($params)) {
//         echo date("Y-m-d H:i:s").' '. '发现以下sku_mapping sku消失 删除匹配'.json_encode($params).PHP_EOL;
//    }
//    foreach ($params as $param) {
//         if (!$db->getOne("select 1 from platform_sku where platform_sku_id = {$param['platform_sku_id']} and facility_id = {$facility_id}")) {
//             $db->query("delete from sku_mapping where sku_mapping_id = {$param['sku_mapping_id']} and facility_id = {$facility_id}");
//         }else{
//            $result = postJsonData($url.'/goods/deleteSkuMapping', json_encode($param),0);
//         }
//     }

//     $params = $db->getAll("SELECT s.facility_id,sku_mapping_id,s.platform_sku_id from sku_mapping s left join goods sk on s.facility_id = sk.facility_id and s.goods_id = sk.goods_id
//     where s.facility_id = {$facility_id} and sk.goods_id  is null");
//     if (!empty($params)) {
//         echo date("Y-m-d H:i:s").' '. '发现以下sku_mapping goods消失 删除匹配'.json_encode($params).PHP_EOL;
//     }   
//    foreach ($params as $param) {
//         if (!$db->getOne("select 1 from platform_sku where platform_sku_id = {$param['platform_sku_id']} and facility_id = {$facility_id}")) {
//             $db->query("delete from sku_mapping where sku_mapping_id = {$param['sku_mapping_id']} and facility_id = {$facility_id}");
//         }else{
//            $result = postJsonData($url.'/goods/deleteSkuMapping', json_encode($param),0);
//         }
//     }


//     $params = $db->getAll("SELECT s.facility_id,goods_mapping_id,s.platform_goods_id from goods_mapping s left join goods sk on s.facility_id = sk.facility_id and s.goods_id = sk.goods_id
//     where s.facility_id = {$facility_id} and sk.goods_id  is null");
//     if (!empty($params)) {
//         echo date("Y-m-d H:i:s").' '. '发现以下goods_mapping goods消失 删除匹配'.json_encode($params).PHP_EOL;
//     }   
//    foreach ($params as $param) {
//     if (!$db->getOne("select 1 from platform_goods where platform_goods_id = {$param['platform_goods_id']} and facility_id = {$facility_id}")) {
//             $db->query("delete from goods_mapping where goods_mapping_id = {$param['goods_mapping_id']} and facility_id = {$facility_id}");
//         }else{
//            $result = postJsonData($url.'/goods/deleteGoodsMapping', json_encode($param),0);
//         }
//     }
// }

echo date("Y-m-d H:i:s").'  开始检查订单表里面的商品简称，与实际商品简称不符 '.PHP_EOL;
foreach ($facilitys as $facility_id) {
	if($facility_id == 100432 || $facility_id == 200025){continue;}
    selectRdsByFacilityId($facility_id);
try{
    $orders = $db->getAll("SELECT distinct m.goods_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN goods g on m.facility_id = g.facility_id and m.goods_id = g.goods_id
    where  (m.goods_alias <> g.goods_alias or ((m.goods_alias is NULL or m.goods_alias = '')  and (g.goods_alias is not null and g.goods_alias <> '')) or ((g.goods_alias is NULL or g.goods_alias = '')  and (m.goods_alias is not null and m.goods_alias <> ''))) and m.facility_id = {$facility_id}");
    if (!empty($orders) && count($orders) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '6、订单表里面的商品简称，与实际商品简称不符 修改商品简称:'.json_encode($orders).PHP_EOL;
        foreach ($orders as $order) {
            $goods_alias = $db->getOne("select goods_alias from goods where goods_id = {$order['goods_id']} and facility_id = {$order['facility_id']}");
            $redis->hset('goods_alias', $order['goods_id'], $goods_alias);
            $db->query("update multi_goods_shipment_goods set goods_alias = '".addslashes($goods_alias)."' where goods_id = {$order['goods_id']} and facility_id = {$order['facility_id']}");
        }
    }

    $orders = $db->getAll("SELECT distinct m.sku_id,m.facility_id from multi_goods_shipment_goods m LEFT JOIN sku s on m.facility_id = s.facility_id and m.sku_id = s.sku_id
    where (m.sku_alias <> s.sku_alias or ((m.sku_alias is NULL or m.sku_alias = '')  and (s.sku_alias is not null and s.sku_alias <> '')) or ((s.sku_alias is NULL or s.sku_alias = '')  and (m.sku_alias is not null and m.sku_alias <> ''))) and m.facility_id = {$facility_id}");
    if (!empty($orders) && count($orders) > 0) {
        echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
        echo date("Y-m-d H:i:s").' '. '6、订单表里面的sku简称，与实际sku简称不符 修改sku简称:'.json_encode($orders).PHP_EOL;
        foreach ($orders as $order) {
            $sku_alias = $db->getOne("select sku_alias from sku where sku_id = {$order['sku_id']} and facility_id = {$order['facility_id']}");
            $redis->hset('sku_alias', $order['sku_id'], $sku_alias);
            $db->query("update multi_goods_shipment_goods set sku_alias = '".addslashes($sku_alias)."' where sku_id = {$order['sku_id']} and facility_id = {$order['facility_id']}");
        }
    }
}catch(\Exception $e){continue;}
}

// echo date("Y-m-d H:i:s").'  开始检查没有multi '.PHP_EOL;
// foreach ($facilitys as $facility_id) {
//     selectRdsByFacilityId($facility_id);
//      $sql = "
//     SELECT m.order_sn,m.shop_id from multi_goods_shipment m left join multi_goods_shipment_goods mg
//     on m.facility_id = mg.facility_id and m.shipment_id = mg.original_shipment_id
//     where m.facility_id = {$facility_id} and mg.order_sn is null";
//         // echo date("Y-m-d H:i:s").' '. $sql.PHP_EOL;
//         $data = $db->getAll($sql);
        
//         // var_dump($data);
//         if (!empty($data)) {
//             foreach ($data as $d) {
//                 $db->query("delete from multi_goods_shipment where order_sn in ('{$d['order_sn']}')");
//                 $db->query("delete from order_info where order_sn in ('{$d['order_sn']}')");
//                 $db->query("delete from shipment where order_sn in ('{$d['order_sn']}')");
//                 $sync_db->query("delete from sync_pinduoduo_order_info where order_sn in ('{$d['order_sn']}')");
//                 ExpressApiService::downloadSingleOrder($d['shop_id'],'pinduoduo',$d['order_sn']);
//             }
//         }
//     }	

// echo date("Y-m-d H:i:s").'  开始检查系统sku表,库存表中没有相关sku的库存 '.PHP_EOL;
// foreach ($facilitys as $facility_id){
//     selectRdsByFacilityId($facility_id);
//     $sql = "
//     SELECT 
//         s.facility_id ,
//         s.goods_id ,
//         s.sku_id 
//     FROM
//         sku s 
//         left join inventory i on s.sku_id = i.sku_id and s.facility_id = i.facility_id 
//     WHERE
//         s.facility_id = {$facility_id}
//         and i.sku_id is NULL
//     ";
//     $data = $db->getAll($sql);
//     if(!empty($data)){
//         echo date("Y-m-d H:i:s"). ' 库存表中没有相关sku信息，向库存表中插入sku信息 '.PHP_EOL;
//         foreach ($data as $d){
//             $db->query("INSERT INTO inventory (facility_id, goods_id, sku_id, quantity)
//                 VALUES({$d['facility_id']}, {$d['goods_id']}, {$d['sku_id']}, 0)");
//         }
//     }
// }

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

