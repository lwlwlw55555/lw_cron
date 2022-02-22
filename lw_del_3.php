<?php
require("includes/init.php");
echo date("Y-m-d H:i:s")." delete_three_month_orders begin".PHP_EOL;

global $sync_db;

// while (date('H') >= 8) {
// 	echo date("Y-m-d H:i:s").' will into sleep'.PHP_EOL;
// 	sleep(60*60);
// }


$is_begin = true;
$count = 0;
$start = 0;
$total = 0;
$limit = 1000;

while($is_begin || $count == $limit){
	try{
	    $is_begin = false;
	    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
	    $sql = "select goods_id from goods_pinduoduo where last_updated_time < '2021-10-01' limit {$limit}";
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


