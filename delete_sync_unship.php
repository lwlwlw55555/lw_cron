<?php
require("includes/init.php");
echo date("Y-m-d H:i:s")." delete_three_month_orders begin".PHP_EOL;

global $sync_db;

$is_begin = true;
$count = 0;
$start = 0;
$total = 0;
$limit = 100;

while($is_begin || $count == $limit){
	try{
	    $is_begin = false;
	    // $sql = "select order_id from sync_pinduoduo_order_info where order_status > 1 and created_time < '2021-03-01' limit {$start},2000";
	    $sql = "select order_id from sync_pinduoduo_order_info where order_status =1 and refund_status = 1 and last_updated_time < '2021-10-01' limit {$limit}";
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
	    $wait = rand(200,400);
	    usleep($wait);
	    echo '[]'.date("Y-m-d H:i:s").'[delete_erp_sync] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
	    $sync_db->query($sql);
	    
	    // die;
	}catch(Exception $e){
		continue;
	}
}


