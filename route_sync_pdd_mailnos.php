<?php
require("includes/init.php");
require("Services/ExpressApiService.php");
echo("[]".date("Y-m-d H:i:s") . " route_sync_pdd_mailnos  begin \r\n");

$end_time = date('Y-m-d H:i:s');


$sql = "select * from datasource_send_time where datasource_name = 'pdd_mailnos'";
$datasource_send_time_list = $route_drds_db->getAll($sql);
foreach($datasource_send_time_list as $datasource_send_time){
    send_route_pre_by_datasource($datasource_send_time['datasource_name'],$datasource_send_time['last_send_time'],$end_time);
}


echo("[]".date("Y-m-d H:i:s") . " route_sync_pdd_mailnos end \r\n");


function send_route_pre_by_datasource($datasource_name,$start_time,$end_time){
    global $db, $route_drds_db;
    if($start_time == null || $start_time == '' ){
        $start_time = '2019-04-18 11:00:00';
    }
    $time = 30*60;
    $start_time_int = strtotime($start_time);
    $end_time_int = strtotime($end_time);
    for($i = $start_time_int ; $i < $end_time_int; $i = $i+$time){

        $start_time = date("Y-m-d H:i:s",$i);
        $end_time = date("Y-m-d H:i:s",$i+$time);
        if(($i+$time) > $end_time_int){
            $end_time = date("Y-m-d H:i:s",$end_time_int);
        }

        $sql = "select facility_id, shipping_id, tracking_number from mailnos where thermal_type = 'PDD' and created_time > '{$start_time}' and created_time <= '{$end_time}'";
        $mailnos = $db->getAll($sql);
        $values = array();
        foreach ($mailnos as $key=>$item) {
            $values[] = " ({$item['facility_id']}, {$item['shipping_id']}, '{$item['tracking_number']}') ";
            if ($key != 0 && $key % 100 == 0) {
                $values_sql = implode(',', $values);
                $insert_sql = "insert ignore into pdd_mailnos(facility_id, shipping_id, tracking_number) values {$values_sql}";
                $route_drds_db->query($insert_sql);
                $values = array();
            }
        }
        if (!empty($values)) {
            $values_sql = implode(',', $values);
            $insert_sql = "insert ignore into pdd_mailnos(facility_id, shipping_id, tracking_number) values {$values_sql}";
            $route_drds_db->query($insert_sql);
        }

        $sql = "update datasource_send_time set last_send_time = '{$end_time}' where datasource_name = '{$datasource_name}' ";
        $route_drds_db->query($sql);
    }
}

