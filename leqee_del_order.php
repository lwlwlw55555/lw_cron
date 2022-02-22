<?php
require("includes/init.php");

global $oms;
$oms_db_conf = array(
    "host" => "rm-bp10hv462sva1muzk5o.mysql.rds.aliyuncs.com:3306",
    "user" => "admin_omsv2",
    "pass" => "7o%01XSpZPE%",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "omssync"
);
$oms = ClsPdo::getInstance($oms_db_conf);

if (isset($oms)) {
    $is_begin = true;
    $count = 0;
    $start = 0;
    $total = 0;
    $limit = 1000;
    $date = date("Y-m-d H:i:s",strtotime("-7 day"));

    while($is_begin || $count == $limit){
        try{
            $is_begin = false;
            $sql = "select sync_order_info_id from omssync.sync_order_info where api_platform ='VIP_MP' and shop_nick = 'Asahi酒水旗舰店' and last_update_time > '{$date}'";
            echo $sql.PHP_EOL;
            $ids = $oms->getCol($sql);
            $count = count($ids);
            if (empty($count)) {
                echo '[]'.'[delete_sync_order_info] delete count:'.$count.' total:'.$total.' is 0 break!'.PHP_EOL;
                break;
            }
            $start += $count; 
            $sql = "delete from omssync.sync_order_info where sync_order_info_id in (".implode(",",$ids).")";
            echo $sql.PHP_EOL;
            $total += $count;
            $wait = rand(200,400);
            usleep($wait);
            echo '[]'.date("Y-m-d H:i:s").'[delete_sync_order_info] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
            $oms->query($sql);

            // die;
        }catch(Exception $e){
            continue;
        }
    }


    $is_begin = true;
    $count = 0;
    $start = 0;
    $total = 0;
    $limit = 1000;
    $date = date("Y-m-d H:i:s",strtotime("-7 day"));
    while($is_begin || $count == $limit){
        try{
            $is_begin = false;
            $sql = "select oo.origin_order_id from oms.origin_order oo left join oms.order_info oi on oo.origin_order_id = oi.origin_order_id where platform ='VIP_MP' and seller = 'Asahi酒水旗舰店' and oi.origin_order_id is null";
            echo $sql.PHP_EOL;
            $ids = $oms->getCol($sql);
            $count = count($ids);
            if (empty($count)) {
                echo '[]'.'[delete_origin_order] delete count:'.$count.' total:'.$total.' is 0 break!'.PHP_EOL;
                break;
            }
            $start += $count; 
            $sql = "delete from oms.origin_order where origin_order_id in (".implode(",",$ids).")";
            echo $sql.PHP_EOL;
            $sql1 = "delete from oms.origin_order_goods where origin_order_id in (".implode(",",$ids).")";
            echo $sql1.PHP_EOL;
            $total += $count;
            $wait = rand(200,400);
            usleep($wait);
            echo '[]'.date("Y-m-d H:i:s").'[delete_origin_order] delete count:'.$count.' total:'.$total.' wait:'.$wait.PHP_EOL;
            $oms->query($sql);
            $oms->query($sql1);

            // die;
        }catch(Exception $e){
            continue;
        }
    }
}
