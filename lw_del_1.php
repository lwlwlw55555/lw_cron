<?php
require("includes/init.php");
echo date("Y-m-d H:i:s")." delete_three_month_orders begin".PHP_EOL;

global $sync_db;

// while (date('H') >= 8) {
// 	echo date("Y-m-d H:i:s").' will into sleep'.PHP_EOL;
// 	sleep(60*60);
// }


$index = isset($argv[1])?$argv[1]:0;

$is_begin = true;
$count = 0;
$start = 0;
$total = 0;
$limit = 1000;

if ($index == 0) {
	while($is_begin || $count == $limit){
		try{
		    $is_begin = false;
		    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
		    $sql = "select order_id from sync_pinduoduo_order_info where order_status in (2,3) and created_time < '2021-10-17' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_erp_sync] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_pinduoduo_order_info where order_id in (".implode(",",$order_list).")";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_erp_sync] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 1) {
	while($is_begin || $count == $limit){
		try{
	    	$is_begin = false;
		    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
		    // $sql = "select order_id from sync_pinduoduo_order_info where last_updated_time < '2021-09-01' limit {$limit}";
		    $sql = "select order_id from sync_pinduoduo_order_info where refund_status = 4 and created_time < '2021-11-01' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_erp_sync] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_pinduoduo_order_info where order_id in (".implode(",",$order_list).")";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_erp_sync] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 2) {
	while($is_begin || $count == $limit){
		try{
		    $is_begin = false;
		    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
		    $sql = "select goods_id from goods_pinduoduo where last_updated_time < '2021-10-17' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_erp_sync] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from goods_pinduoduo where goods_id in (".implode(",",$order_list).")";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_goods_pinduoduo] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 3) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
		    $sql = "select sku_id from sku_pinduoduo where last_updated_time < '2021-10-01' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_erp_sync] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sku_pinduoduo where sku_id in (".implode(",",$order_list).")";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sku_pinduoduo] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 4) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
		    $sql = "select sku_id from sku_pinduoduo where last_updated_time < '2021-10-01' limit {$limit}";
		     // echo date("Y-m-d H:i:s")." sql".$sql;
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_erp_sync] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sku_pinduoduo where sku_id in (".implode(",",$order_list).")";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sku_pinduoduo] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}


if ($index == 5) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    $sql = "select tid from sync_taobao_order_info where last_updated_time < '2021-09-01' limit {$limit}";
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_sync_taobao_order_info] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_taobao_order_info where tid in ('".implode("','",$order_list)."')";
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sync_taobao_order_info] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 6) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    $sql = "select oid from sync_taobao_order_goods where last_updated_time < '2021-09-01' limit {$limit}";
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_sync_taobao_order_goods] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_taobao_order_goods where oid in ('".implode("','",$order_list)."')";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sync_taobao_order_goods] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 7) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    $sql = "select order_id from sync_douyin_order_info where last_updated_time < '2021-09-01' limit {$limit}";
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_sync_douyin_order_info] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_douyin_order_info where order_id in ('".implode("','",$order_list)."')";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sync_douyin_order_info] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 8) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    $sql = "select order_id from sync_douyin_order_goods where last_updated_time < '2021-09-01' limit {$limit}";
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_sync_douyin_order_goods] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_douyin_order_goods where order_id in ('".implode("','",$order_list)."')";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sync_douyin_order_goods] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 9) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    $sql = "select oid from sync_kuaishou_order_info where last_updated_time < '2021-09-01' limit {$limit}";
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_sync_kuaishou_order_info] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_kuaishou_order_info where oid in ('".implode("','",$order_list)."')";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sync_kuaishou_order_info] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}

if ($index == 10) {
	while($is_begin || $count == $limit){
		try{
			$is_begin = false;
		    $sql = "select oid from sync_kuaishou_order_goods where last_updated_time < '2021-09-01' limit {$limit}";
		    $order_list = $sync_db->getCol($sql);
		    $count = count($order_list);
		    if (empty($count)) {
		    	echo '[]'.'[delete_sync_kuaishou_order_goods] delete count:'.$count.' total:'.$total.' is 0 die!'.PHP_EOL;
		    	die;
		    }
		    $start += $count; 
		    $sql = "delete from sync_kuaishou_order_goods where oid in ('".implode("','",$order_list)."')";
		    // echo $sql.PHP_EOL;
		    $total += $count;
		    $wait = rand(100,200);
		    usleep($wait);
		    echo '[]'.date("Y-m-d H:i:s").'[delete_sync_kuaishou_order_goods] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
		    $sync_db->query($sql);
		    
		    // die;
		}catch(Exception $e){
			continue;
		}
	}
}


