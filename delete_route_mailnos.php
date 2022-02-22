<?php

date_default_timezone_set("Asia/Shanghai");
require("includes/ClsPdo.php");

$erp_route_db_conf = array(
    "host" => "100.65.1.118:32054",
    "user" => "erp_route",
    "pass" => "Titanerproute2020",
    "name" => "erp_route_stat",
    "charset" => "utf8",
    "pconnect" => "1",
);
echo date("Y-m-d H:i:s")." begin delete route malnos ".PHP_EOL;

$erp_route_db = ClsPdo::getInstance($erp_route_db_conf);
$end_time = date("Y-m-d 00:00:00", strtotime("-15 day", time()));
$sql = "select mailno_id from mailnos where last_update_time <=  '{$end_time}' limit 1000";
while (true){
    echo $sql.PHP_EOL;
    $mailnos_ids = $erp_route_db->getCol($sql);
    if(empty($mailnos_ids)){
        break;
    }
    $mailnos_ids_str = implode(",", $mailnos_ids );
    $erp_route_db->query("delete from mailnos where mailno_id in ({$mailnos_ids_str})");
    echo date("Y-m-d H:i:s")." delete stat mailnos ".count($mailnos_ids).PHP_EOL;
}

$erp_route_db_conf['name'] = 'erp_route';
$erp_route_db = ClsPdo::getInstance($erp_route_db_conf);
while(true){
    echo $sql.PHP_EOL;
    $mailnos_ids = $erp_route_db->getCol($sql);
    if(empty($mailnos_ids)){
        break;
    }
    $mailnos_ids_str = implode(",", $mailnos_ids );
    $erp_route_db->query("delete from mailnos where mailno_id in ({$mailnos_ids_str})");
    echo date("Y-m-d H:i:s")." delete route mailnos ".count($mailnos_ids).PHP_EOL;
}
echo  date("Y-m-d H:i:s")." delete route finish to ". $end_time.PHP_EOL;

$end_time = date("Y-m-d 00:00:00", strtotime("-30 day", time()));
$sql = "select mailno_id from mailnos_facility where last_update_time <=  '{$end_time}' limit 1000";
while(true){
    echo $sql.PHP_EOL;
    $mailnos_ids = $erp_route_db->getCol($sql);
    if(empty($mailnos_ids)){
        break;
    }
    $mailnos_ids_str = implode(",", $mailnos_ids );
    $erp_route_db->query("delete from mailnos_facility where mailno_id in ({$mailnos_ids_str})");
    echo date("Y-m-d H:i:s")." delete route mailnos_facility ".count($mailnos_ids).PHP_EOL;
}

echo  date("Y-m-d H:i:s")." route mailnos facility delete finish to ". $end_time.PHP_EOL;