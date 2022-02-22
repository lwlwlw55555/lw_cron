<?php
require("includes/init.php");
$url = 'http://100.65.128.171:10317';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

global $erp_ddyun_db;

for($ii = 0; $ii < 256; $ii++){
echo date("Y-m-d H:i:s").' '.(isset($opt_params['mod_index'])?$opt_params['mod_index']:"ALL")." erp_{$ii} delete_check begin".PHP_EOL;
$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_history_db_conf = array(
    "host" => "100.65.2.183:32058",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_db_conf['name'] = 'erp_'.$ii;
$erp_history_db_conf['name'] = 'erp_'.$ii;

$erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
$erp_history_db = ClsPdo::getInstance($erp_history_db_conf);

if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 12)){
	$exist = false;
	$shop_ids = $erp_ddyun_db->getAll("select shop_id,default_facility_id from shop_back where back_created_time < DATE_SUB(now(),INTERVAL 5 minute) and is_deleted_data = 0");
	echo date("Y-m-d H:i:s").' 发现5分钟前未删除店铺:'.json_encode($shop_ids).PHP_EOL;
	foreach ($shop_ids as $shop) {
		if(!$exist && $sync_db->getOne("select 1 from goods_pinduoduo  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_pinduoduo exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sku_pinduoduo  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_pinduoduo exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_pinduoduo_order_info  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_pinduoduo_order_info exist!'.PHP_EOL;
			}
		}
		if(!$exist && $sync_db->getOne("select 1 from goods_taobao  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_taobao exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sku_taobao  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_taobao exist!'.PHP_EOL;
			}
		}
		if(!$exist && $sync_db->getOne("select 1 from goods_alibaba  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_alibaba exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sku_alibaba  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_alibaba exist!'.PHP_EOL;
			}
		}
		if(!$exist && $sync_db->getOne("select 1 from goods_kuaishou  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_kuaishou exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sku_kuaishou  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_kuaishou exist!'.PHP_EOL;
			}
		}
		if(!$exist && $sync_db->getOne("select 1 from goods_douyin  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_douyin exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sku_douyin  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_douyin exist!'.PHP_EOL;
			}
		}
		if(!$exist && $sync_db->getOne("select 1 from sync_taobao_order_info  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_taobao_order_info exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_taobao_order_goods  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_taobao_order_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_alibaba_order_info  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_alibaba_order_info exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_alibaba_order_goods  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_alibaba_order_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_kuaishou_order_info  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_kuaishou_order_info exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_kuaishou_order_goods  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_kuaishou_order_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_douyin_order_info  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_douyin_order_info exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $sync_db->getOne("select 1 from sync_douyin_order_goods  where shop_id = {$shop['shop_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sync_douyin_order_goods exist!'.PHP_EOL;
			}
		}	
		// if(!$exist && $sync_db->getOne("select 1 from sync_pinduoduo_order_goods where shop_id = {$shop['shop_id']}")){
		// 	$exist = true;
		// }	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from platform_goods  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' platform_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from platform_sku  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' platform_sku exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from goods_mapping  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_mapping exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from sku_mapping  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_mapping exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from multi_goods_shipment  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' multi_goods_shipment exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from multi_goods_shipment_goods  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' multi_goods_shipment_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from print_log  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' print_log exist!'.PHP_EOL;
			}
		}
		if(!$exist && $erp_history_db->getOne("select 1 from multi_goods_shipment  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' history_multi_goods_shipment exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_history_db->getOne("select 1 from multi_goods_shipment_goods  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' history_multi_goods_shipment_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_history_db->getOne("select 1 from print_log  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' history_print_log exist!'.PHP_EOL;
			}
		}
		if(!$exist && $erp_ddyun_db->getOne("select 1 from shipment  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' shipment exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from order_goods  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' order_goods exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from goods_mapping_history  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' goods_mapping_history exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from sku_mapping_history  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' sku_mapping_history exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from order_info  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}	")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' order_info exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from finance_bill_order  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' finance_bill_order exist!'.PHP_EOL;
			}
		}	
		if(!$exist && $erp_ddyun_db->getOne("select 1 from finance_bill_goods  where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$exist = true;
			if($exist){
				echo date("Y-m-d H:i:s").json_encode($shop).' finance_bill_goods exist!'.PHP_EOL;
			}
		}
		if(!$exist){
			$sql = "update shop_back set is_deleted_data = 1 where shop_id = {$shop['shop_id']}";
			echo date("Y-m-d H:i:s").' shop_id:'.$shop['shop_id'].' 已全部删除 '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
		$exist = false;
	}
}	


$shops = $erp_ddyun_db->getAll("select shop_id,default_facility_id from shop_back where is_deleted_data = 0");
foreach ($shops as $shop) {
	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 1)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_pinduoduo begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from goods_pinduoduo where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select goods_id from goods_pinduoduo where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from goods_pinduoduo where shop_id = {$shop['shop_id']} and goods_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_taobao begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from goods_taobao where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select num_iid from goods_taobao where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from goods_taobao where shop_id = {$shop['shop_id']} and num_iid in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_alibaba begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from goods_alibaba where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select product_iD from goods_alibaba where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from goods_alibaba where shop_id = {$shop['shop_id']} and product_iD in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_kuaishou begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from goods_kuaishou where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select item_id from goods_kuaishou where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from goods_kuaishou where shop_id = {$shop['shop_id']} and item_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_douyin begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from goods_douyin where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select goods_douyin_id from goods_douyin where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from goods_douyin where shop_id = {$shop['shop_id']} and goods_douyin_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 2)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_pinduoduo begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sku_pinduoduo where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select sku_id from sku_pinduoduo where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sku_pinduoduo where shop_id = {$shop['shop_id']} and sku_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_taobao begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sku_taobao where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select sku_id from sku_taobao where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sku_taobao where shop_id = {$shop['shop_id']} and sku_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_alibaba begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sku_alibaba where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select sku_id from sku_alibaba where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sku_alibaba where shop_id = {$shop['shop_id']} and sku_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_kuaishou begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sku_kuaishou where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select sku_id from sku_kuaishou where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sku_kuaishou where shop_id = {$shop['shop_id']} and sku_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_douyin begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sku_douyin where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select sku_id from sku_douyin where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sku_douyin where shop_id = {$shop['shop_id']} and sku_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 3)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_pinduoduo_order_info begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_pinduoduo_order_info where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select order_id from sync_pinduoduo_order_info where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_pinduoduo_order_info where shop_id = {$shop['shop_id']} and order_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_taobao_order_info begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_taobao_order_info where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select tid from sync_taobao_order_info where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_taobao_order_info where shop_id = {$shop['shop_id']} and tid in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_taobao_order_goods begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_taobao_order_goods where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select oid from sync_taobao_order_goods where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_taobao_order_goods where shop_id = {$shop['shop_id']} and oid in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_alibaba_order_info begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_alibaba_order_info where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select id from sync_alibaba_order_info where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_alibaba_order_info where shop_id = {$shop['shop_id']} and id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_alibaba_order_goods begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_alibaba_order_goods where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select sub_item_iD from sync_alibaba_order_goods where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_alibaba_order_goods where shop_id = {$shop['shop_id']} and sub_item_iD in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_kuaishou_order_info begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_kuaishou_order_info where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select oid from sync_kuaishou_order_info where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_kuaishou_order_info where shop_id = {$shop['shop_id']} and oid in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_kuaishou_order_goods begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_kuaishou_order_goods where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select oid from sync_kuaishou_order_goods where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_kuaishou_order_goods where shop_id = {$shop['shop_id']} and oid in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_douyin_order_info begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_douyin_order_info where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select pid from sync_douyin_order_info where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_douyin_order_info where shop_id = {$shop['shop_id']} and pid in ('".implode("','", $ids)."')";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_douyin_order_goods begin'.PHP_EOL;
		while($sync_db->getOne("select 1 from sync_douyin_order_goods where shop_id = {$shop['shop_id']}")){
			$ids = $sync_db->getCol("select order_id from sync_douyin_order_goods where shop_id = {$shop['shop_id']} limit 0,1000");
			$sql = "delete from sync_douyin_order_goods where shop_id = {$shop['shop_id']} and order_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$sync_db->query($sql);
		}
	}

	// if((!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 4)) && $sync_db->getOne("select 1 from sync_pinduoduo_order_goods where shop_id = {$shop['shop_id']}")){
	// 	echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sync_pinduoduo_order_goods begin'.PHP_EOL;
	// 	while($sync_db->getOne("select 1 from sync_pinduoduo_order_goods where shop_id = {$shop['shop_id']}")){
	// 		$ids = $sync_db->getCol("select order_goods_id from sync_pinduoduo_order_goods where shop_id = {$shop['shop_id']} limit 0,1000");
	// 		$sql = "delete from sync_pinduoduo_order_goods where shop_id = {$shop['shop_id']} and order_goods_id in (".implode(",", $ids).")";
	// 		echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
	// 		$sync_db->query($sql);
	// 	}
	// }


	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 4)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' platform_goods begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select id from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from platform_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 5)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' platform_sku begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from platform_sku where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select id from platform_sku where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from platform_sku where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 6)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_mapping begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from goods_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select goods_mapping_id from goods_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from goods_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and goods_mapping_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 7)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_mapping begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from sku_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select sku_mapping_id from sku_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from sku_mapping where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and sku_mapping_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 8)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' multi_goods_shipment begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select shipment_id from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and shipment_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
			$sql = "delete from multi_goods_shipment_extension where facility_id = {$shop['default_facility_id']} and shipment_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' history_multi_goods_shipment begin'.PHP_EOL;
		while($erp_history_db->getOne("select 1 from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_history_db->getCol("select shipment_id from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from multi_goods_shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and shipment_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_history_db->query($sql);
			$sql = "delete from multi_goods_shipment_extension where facility_id = {$shop['default_facility_id']} and shipment_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_history_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 9)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' multi_goods_shipment_goods begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select order_goods_id from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and order_goods_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' print_log begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from print_log where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select id from print_log where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from print_log where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' history_multi_goods_shipment_goods begin'.PHP_EOL;
		while($erp_history_db->getOne("select 1 from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_history_db->getCol("select order_goods_id from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from multi_goods_shipment_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and order_goods_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_history_db->query($sql);
		}

		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' history_print_log begin'.PHP_EOL;
		while($erp_history_db->getOne("select 1 from print_log where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_history_db->getCol("select id from print_log where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from print_log where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_history_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 10)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' shipment begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select shipment_id from shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from shipment where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and shipment_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 11)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' order_goods begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from order_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select order_goods_id from order_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from order_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and order_goods_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 12)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' goods_mapping_history begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from goods_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select goods_mapping_history_id from goods_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from goods_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and goods_mapping_history_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 13)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' sku_mapping_history begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select sku_mapping_history_id from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from sku_mapping_history where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and sku_mapping_history_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 14)){	
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' order_info begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from order_info where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select order_id from order_info where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from order_info where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and order_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 15)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' finance_bill_order begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from finance_bill_order where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select finance_bill_order_id from finance_bill_order where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from finance_bill_order where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and finance_bill_order_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}

	if(!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 16)){
		echo date("Y-m-d H:i:s").' '.$shop['shop_id'].' finance_bill_goods begin'.PHP_EOL;
		while($erp_ddyun_db->getOne("select 1 from finance_bill_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']}")){
			$ids = $erp_ddyun_db->getCol("select finance_bill_goods_id from finance_bill_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} limit 0,1000");
			$sql = "delete from finance_bill_goods where shop_id = {$shop['shop_id']} and facility_id = {$shop['default_facility_id']} and finance_bill_goods_id in (".implode(",", $ids).")";
			echo date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
			$erp_ddyun_db->query($sql);
		}
	}
}

if((!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 6)) && !empty($shops)){
	foreach ($shops as $shop) {
		$goods = $erp_ddyun_db->getAll("SELECT g.goods_id,g.facility_id from goods g inner join facility f on g.facility_id = f.facility_id 
		left join goods_mapping m on g.facility_id = m.facility_id and g.goods_id = m.goods_id
		where m.goods_id is null and f.is_force_manage_goods = 0 and g.created_type <> 'GROUP_MANAGE_CREATED' and g.facility_id = {$shop['default_facility_id']}");
		if (!empty($goods) && count($goods) > 0) {
			echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
			echo date("Y-m-d H:i:s").' '. '有系统商品，没有匹配关系 删除这些商品:'.json_encode($goods).PHP_EOL;
			$result = postJsonData($url.'/goods/deleteGoodsBatch', json_encode($goods),0);
		}
	}
}

if((!isset($opt_params['mod_index']) || (!empty($opt_params['mod_index']) && $opt_params['mod_index'] == 7)) && !empty($shops)){
	foreach ($shops as $shop) {
		$sku = $erp_ddyun_db->getAll("SELECT g.sku_id,g.facility_id from sku g inner join facility f on g.facility_id = f.facility_id 
							left join sku_mapping m on g.facility_id = m.facility_id and g.sku_id = m.sku_id
							left join group_sku_mapping gs on gs.facility_id = g.facility_id and gs.sku_id = g.sku_id
		where m.sku_id is null and f.is_force_manage_goods = 0 and gs.sku_id is null and g.created_type <> 'GROUP_MANAGE_CREATED' and g.facility_id = {$shop['default_facility_id']}");
		if (!empty($sku) && count($sku) > 0) {
			echo date("Y-m-d H:i:s").' '. PHP_EOL.PHP_EOL.PHP_EOL;
			echo date("Y-m-d H:i:s").' '. '有系统sku，没有匹配关系 删除这些sku:'.json_encode($sku).PHP_EOL;
			$result = postJsonData($url.'/goods/deleteSkuBatch', json_encode($sku),0);
		}
	}
}

echo date("Y-m-d H:i:s").' '.(isset($opt_params['mod_index'])?$opt_params['mod_index']:"ALL")." erp_{$ii} delete_check end".PHP_EOL;
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
