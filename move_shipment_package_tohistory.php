<?php

date_default_timezone_set("Asia/Shanghai");
require ("includes/ClsPdo.php");

global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;

$erp_ddyun_db_user_conf = array(
    "host" => "100.65.1.0:32053",
    "name" => "erpuser",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

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

$erp_ddyun_user_db = ClsPdo::getInstance($erp_ddyun_db_user_conf);

echo date("Y-m-d H:i:s", time())." move print_log to history ".PHP_EOL;
$end_time = date('Y-m-d 00:00:00', time());
$start_time = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($end_time)));

for ($i = 0; $i < 256 ; $i++){
    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_history_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);

    $origin_file_name = "move_shipment_package_data_to_history_db ".date("Y-m-d", strtotime($start_time))." ".$erp_ddyun_history_db_conf['name'];
    $import_center_msg = $erp_ddyun_user_db->getOne("select status from import_center where origin_file_name = '{$origin_file_name}'");
    if( empty($import_center_msg) || $import_center_msg != 'DONE') {
        $import_sql = "insert ignore into import_center (type, origin_file_name, status, result) 
        value ('move_shipment_package_data_to_history_db', '{$origin_file_name}', 'TODO', '{$erp_ddyun_db_conf['name']}')
        ON DUPLICATE KEY UPDATE id = VALUES(id), type = VALUES(type), origin_file_name = VALUES(origin_file_name), status = VALUES(status), result = VALUES(result)";
        echo $erp_ddyun_db_conf['name'] . " " . $import_sql . PHP_EOL;
        $erp_ddyun_user_db->query($import_sql);
    }

    /**
    * prod 与 his 库的数量对比
    */
    $count_sql = "select count(*) from shipment_package where created_time >= '{$start_time}' and created_time < '{$end_time}'";
    $count_update_sql = "select count(*) from shipment_package where created_time < '{$start_time}' and last_updated_time >= '{$start_time}' and last_updated_time < '{$end_time}'";
    if($erp_ddyun_db->getOne($count_sql) == $erp_ddyun_history_db->getOne($count_sql)){
        echo $erp_ddyun_db_conf['name']."  "."prod与his ".date("Y-m-d", strtotime($start_time))." shipment package 订单数量一致 ".PHP_EOL;
        if($erp_ddyun_db->getOne($count_update_sql) <= $erp_ddyun_history_db->getOne($count_update_sql)){
            $update_sql = "update import_center set status = 'DONE' where type = 'move_shipment_package_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            $update_sql = "update import_center set status = 'UPDATE ERROR' where type = 'move_shipment_package_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
            move_update_mailnos($start_time, $end_time);
        }
        echo $update_sql.PHP_EOL;
        $erp_ddyun_user_db->query($update_sql);
        continue;
    }

    move_normal_shipment_package($start_time, $end_time);
    move_update_shipment_package($start_time, $end_time);

    if($erp_ddyun_db->getOne($count_sql) == $erp_ddyun_history_db->getOne($count_sql)){
        if($erp_ddyun_db->getOne($count_update_sql) <= $erp_ddyun_history_db->getOne($count_update_sql)){
            $update_sql = "update import_center set status = 'DONE' where type = 'move_shipment_package_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            $update_sql = "update import_center set status = 'UPDATE ERROR' where type = 'move_shipment_package_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
            move_update_shipment_package($start_time, $end_time);
        }
    }else{
        $update_sql = "update import_center set status = 'MOVE ERROR' where type = 'move_shipment_package_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
    }
    echo $update_sql.PHP_EOL;
    $erp_ddyun_user_db->query($update_sql);
}
echo date("Y-m-d H:i:s", time())."  ".$start_time." to ".$end_time." move finished".PHP_EOL;


function move_normal_shipment_package($start_time, $end_time){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit = 0;
    while(true){
        $select_sql = "select * from shipment_package where created_time >= '{$start_time}' and created_time < '{$end_time}' limit {$limit}, 200";
        echo $erp_ddyun_db_conf['name']."  ".$select_sql.PHP_EOL;
        $limit += 200;
        $shipment_package_list = $erp_ddyun_db->getAll($select_sql);
        if(empty($shipment_package_list)){
            break;
        }
        move_shipment_package($shipment_package_list);
    }
}

function move_update_shipment_package($start_time, $end_time){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit = 0;
    while(true){
        $select_sql = "select * from shipment_package where created_time < '{$start_time}' and last_updated_time > '{$start_time}' limit {$limit}, 200";
        echo $erp_ddyun_db_conf['name']."  ".$select_sql.PHP_EOL;
        $limit += 200;
        $shipment_package_list = $erp_ddyun_db->getAll($select_sql);
        if(empty($shipment_package_list)){
            break;
        }
        move_shipment_package($shipment_package_list);
    }
}

function move_shipment_package($shipment_package_list){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $insert_sql = "insert ignore into shipment_package(
                id, 
                package_id, 
                shipment_id, 
                status, 
                is_print_tracking, 
                print_time, 
                facility_id, 
                created_time, 
                last_updated_time) 
                values 
        ";
    if(empty($shipment_package_list)){
        return;
    }
    foreach ($shipment_package_list as $shipment_package){
        $insert_sql .= "(".
                checkNull($shipment_package['id']).",".
                checkNull($shipment_package['package_id']).",".
                checkNull($shipment_package['shipment_id']).",".
                checkNull($shipment_package['status']).",".
                checkNull($shipment_package['is_print_tracking']).",".
                checkNull($shipment_package['print_time']).",".
                checkNull($shipment_package['facility_id']).",".
                checkNull($shipment_package['created_time']).",".
                checkNull($shipment_package['last_updated_time'])."),";
    }
    $insert_sql = substr($insert_sql, 0, -1);
    $insert_sql .= "ON DUPLICATE KEY UPDATE
                id = VALUES(id), 
                package_id = VALUES(package_id), 
                shipment_id = VALUES(shipment_id), 
                status = VALUES(status), 
                is_print_tracking = VALUES(is_print_tracking), 
                print_time = VALUES(print_time), 
                facility_id = VALUES(facility_id), 
                created_time = VALUES(created_time), 
                last_updated_time = VALUES(last_updated_time)";
    $erp_ddyun_history_db->query($insert_sql);
    echo date("Y-m-d H:i:s", time())."  ".$erp_ddyun_db_conf['name']." insert ".count($shipment_package_list)." shipment_package to history".PHP_EOL;
}

function checkNull($temp){
    if(!is_null($temp)){
        $temp = addslashes($temp);
        $temp = "'{$temp}'";
    }else{
        $temp = 'null';
    }
    return $temp;
}