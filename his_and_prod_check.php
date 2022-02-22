<?php

date_default_timezone_set("Asia/Shanghai");
require ("includes/ClsPdo.php");

$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_history_db_conf = array(
    "host" => "100.65.2.183:32058",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$start_time = date("Y-m-d 00:00:00", strtotime("2020-07-01"));
$end_time = date("Y-m-d 00:00:00", strtotime("2020-09-01"));
$facility_id = 100492;
$begin = 140;
$end = $begin + 1;

for ($i = $begin ;$i < $end ;$i++){
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
    $count_sql = "select count(*) from mailnos where created_time >= '{$start_time}' and created_time < '{$end_time}' and facility_id = {$facility_id}";
    $his_count = $erp_ddyun_history_db->getOne($count_sql);
    $prod_count = $erp_ddyun_db->getOne($count_sql);
    if($his_count == $prod_count){
        echo $erp_ddyun_db_conf['name']."   mailnos yes".PHP_EOL;
    }else{
        echo $erp_ddyun_db_conf['name']."  his : ".$his_count."  prod : ".$prod_count.PHP_EOL;
    }
}

for ($i = $begin;$i < $end;$i++){
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
    $count_sql = "select count(*) from shipment_package where created_time >= '{$start_time}' and created_time < '{$end_time}'  and facility_id = {$facility_id}";
    $his_count = $erp_ddyun_history_db->getOne($count_sql);
    $prod_count = $erp_ddyun_db->getOne($count_sql);
    if($his_count == $prod_count){
        echo $erp_ddyun_db_conf['name']."   shipment_package yes".PHP_EOL;
    }else{
        echo $erp_ddyun_db_conf['name']."  his : ".$his_count."  prod : ".$prod_count.PHP_EOL;
    }
}

for ($i = $begin;$i < $end ;$i++){
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
    $count_sql = "select count(*)
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time < '{$end_time}' and mgs.shipping_time >= '{$start_time}'  and mgs.facility_id = {$facility_id}";
    $his_count = $erp_ddyun_history_db->getOne($count_sql);
    $prod_count = $erp_ddyun_db->getOne($count_sql);
    if($his_count == $prod_count){
        echo $erp_ddyun_db_conf['name']."  shipment yes".PHP_EOL;
    }else{
        echo $erp_ddyun_db_conf['name']."  his : ".$his_count."  prod : ".$prod_count.PHP_EOL;
    }
}
