<?php
require("includes/init.php");

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

    $sql = "optimize table inventory_sync_job_log";
    $oms_user->query($sql);
}
