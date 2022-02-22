<?php
require("includes/init.php");
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

echo '[]'.(date("Y-m-d H:i:s").' ' . " fix_platform_error_order  begin \r\n");

if (isset($argv[1])) {
	$mod = $argv[1];
	$mod_id = 10;
	if (isset($argv[2])) {
		$mod_id = $argv[2]; 
	}
	echo '[]params: mod:'.$mod.' mod_id'.$mod_id.PHP_EOL;
	$sql = "select shop_id,order_sn from platform_error_order where retry_count < 10 and mod(shop_id,{$mod_id}) = {$mod}";
	echo '[]'.date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
}else{
	$sql = "select shop_id,order_sn from platform_error_order where retry_count < 10";
	echo '[]'.date("Y-m-d H:i:s").' '.$sql.PHP_EOL;
}
global $sync_db;
$orders = $sync_db->getAll($sql);
foreach ($orders as $order) {
	$order_sn  = $order['order_sn'];
	$shop_id = $order['shop_id'];
	$response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
	if (isset($response['data']['msg']) && strpos($response['data']['msg'], '订单为两天外已发货或退款订单不保存') !== false) {
        selectRdsByShopId($shop_id);
        $db->query("delete from order_info where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
        $db->query("delete from shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
        if ($platform_name == 'pinduoduo') {
            $db->query("delete from single_goods_shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
        }else{
            $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
            $db->query("delete from multi_goods_shipment_goods where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
        }
        echo '[]'.date("Y-m-d H:i:s").' '.'发现可能超时导致的sync库数据(order_sn:'.$order_sn.')回滚不存在，已删除生产库数据'.PHP_EOL;
        $error_sql = "update platform_error_order set retry_count = 0 where order_sn = '{$order_sn}'";
        echo '[]'.date("Y-m-d H:i:s").' '.$error_sql.PHP_EOL;
        $sync_db->query($error_sql);
    }

    if (!isset($response['code']) || $response['code'] == 1){
    	if(isset($response['msg']) && strpos($response['msg'], 'inner_fail') !== false) {
            selectRdsByShopId($shop_id);
            $order_info_created_time = $db->getOne("SELECT created_time from order_info where shop_id = {$shop_id} and order_sn = '{$order_sn}'");
            $shipment_exist = $db->getOne("SELECT 1 from shipment where shop_id = {$shop_id} and order_sn = '{$order_sn}'");
             $sigle_multi_exist = $db->getOne("SELECT 1 from single_goods_shipment where shop_id = {$shop_id} and order_sn = '{$order_sn}'");
            echo '[]'.date("Y-m-d H:i:s").' '.'发现shop_id:'.$shop_id.' order_sn:'.$order_sn.'系统多于平台且发生inner_fail错误 生产库各表存在情况:order_info_created_time:'.$order_info_created_time.' shipment_exist:'.$shipment_exist.' sigle_multi_exist:'.$sigle_multi_exist.PHP_EOL;
            if ((!$shipment_exist || !$sigle_multi_exist)) {
                deleteSyncData('pinduoduo',$order_sn,$shop_id);
                $db->query("delete from order_info where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                if ($shipment_exist) {
                    $db->query("delete from shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                }
                if ($sigle_multi_exist) {
                    $db->query("delete from single_goods_shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                }
                $response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
                echo '[]'.date("Y-m-d H:i:s").' '.'shop_id:'.$shop_id.' order_sn:'.$order_sn.'系统多于平台且发生inner_fail错误 且满足删除数据重跑条件已删除重跑'.PHP_EOL;
                $error_sql = "update platform_error_order set retry_count = 0 where order_sn = '{$order_sn}'";
	            echo '[]'.date("Y-m-d H:i:s").' '.$error_sql.PHP_EOL;
	            $sync_db->query($error_sql);
            }
        }elseif(isset($response['msg']) && strpos($response['msg'], '订单不属于当前店铺或订单不存在') !== false) {
			echo '发现三个月前找不到的待发货订单:'.$order_sn.' 删除该数据'.PHP_EOL;
			$error_sql = "delete from platform_error_order where order_sn = '{$order_sn}'";
	        echo '[]'.date("Y-m-d H:i:s").' '.$error_sql.PHP_EOL;
	        $sync_db->query($error_sql);
		}else{
            $error_sql = "update platform_error_order set retry_count = retry_count + 1 where order_sn = '{$order_sn}'";
            echo '[]'.date("Y-m-d H:i:s").' '.$error_sql.PHP_EOL;
		    $sync_db->query($error_sql);
		}
    }else if($response['code'] == 0 || $response['code'] == 20004) {
    	$error_sql = "delete from platform_error_order where order_sn = '{$order_sn}'";
        echo '[]'.date("Y-m-d H:i:s").' '.$error_sql.PHP_EOL;
        $sync_db->query($error_sql);
    }
    echo PHP_EOL.PHP_EOL.PHP_EOL;
}

echo '[]'.(date("Y-m-d H:i:s").' ' . " fix_platform_error_order  end \r\n");

function deleteSyncData($platform_name,$order_sn,$shop_id){
    global $sync_db;
    if ($platform_name == 'pinduoduo') {
        $order_id = $sync_db->getOne("select order_id from sync_pinduoduo_order_info where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
        if (!empty($order_id)) {
            $sync_db->query("delete from sync_pinduoduo_order_goods where order_id = {$order_id}");
            $sync_db->query("delete from sync_pinduoduo_order_info where order_id = {$order_id}");
        }
    } 
}

