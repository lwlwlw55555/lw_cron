<?php

date_default_timezone_set("Asia/Shanghai");

global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;

require ("includes/ClsPdo.php");

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

$now = date('Y-m-d H:i:s', time());
if($now < date('Y-m-d 21:00:00', time())){
    $type = "2nd";
    $end_time = date('Y-m-d 00:00:00', time());
    $start_time = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($end_time)));
}else{
    $type = "1st";
    $end_time = date('Y-m-d 22:00:00', time());
    $start_time = date('Y-m-d 00:00:00', time());
}

for ($i = 0; $i <= 255; $i++){
    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_history_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);

    $origin_file_name = "move_print_log_data_to_history_db ".date("Y-m-d", strtotime($start_time))." ".$erp_ddyun_history_db_conf['name'];
    $import_center_msg = $erp_ddyun_user_db->getOne("select status from import_center where origin_file_name = '{$origin_file_name}'");
    if( empty($import_center_msg) || $import_center_msg != 'DONE') {
        $import_sql = "insert ignore into import_center (type, origin_file_name, status, result) 
        value ('move_print_log_data_to_history_db', '{$origin_file_name}', 'TODO', '{$erp_ddyun_db_conf['name']}')
        ON DUPLICATE KEY UPDATE id = VALUES(id), type = VALUES(type), origin_file_name = VALUES(origin_file_name), status = VALUES(status), result = VALUES(result)";
        echo $erp_ddyun_db_conf['name'] . " " . $import_sql . PHP_EOL;
        $erp_ddyun_user_db->query($import_sql);
    }

    $count_sql = "select count(*) from print_log where created_time >= '{$start_time}' and created_time < '{$end_time}'";
    $count_encrypt_sql = "select count(*) from print_log l inner join print_log_extension_encrypt e on l.batch_sn = e.batch_sn and l.batch_order = e.batch_order and l.facility_id = e.facility_id where l.created_time >= '{$start_time}' and l.created_time <= '{$end_time}'";
    if($type != '1st' && ($erp_ddyun_db->getOne($count_sql) == $erp_ddyun_history_db->getOne($count_sql))){
        echo $erp_ddyun_db_conf['name']."  "."prod与his ".date("Y-m-d", strtotime($start_time)). " 打印日志数量一致 ".PHP_EOL;
        if($erp_ddyun_db->getOne($count_encrypt_sql) == $erp_ddyun_history_db->getOne($count_encrypt_sql)){
            $update_sql = "update import_center set status = 'DONE' where type = 'move_print_log_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            move_encrypt_print_log($start_time, $end_time);
            $update_sql = "update import_center set status = 'ENCRYPT ERROR' where type = 'move_print_log_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }
        echo $update_sql.PHP_EOL;
        $erp_ddyun_user_db->query($update_sql);
        continue;
    }

    move_print_log($start_time, $end_time);
    move_encrypt_print_log($start_time, $end_time);
    $update_sql = "update import_center set status = '1st DONE' where type = 'move_print_log_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
    if($type != '1st'){
        if($erp_ddyun_db->getOne($count_sql) == $erp_ddyun_history_db->getOne($count_sql)){
            echo $erp_ddyun_db_conf['name']."  "."prod与his ".date("Y-m-d", strtotime($start_time)). " 打印日志数量一致 ".PHP_EOL;
            if($erp_ddyun_db->getOne($count_encrypt_sql) == $erp_ddyun_history_db->getOne($count_encrypt_sql)){
                $update_sql = "update import_center set status = 'DONE' where type = 'move_print_log_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
            }else{
                move_encrypt_print_log($start_time, $end_time);
                $update_sql = "update import_center set status = 'ENCRYPT ERROR' where type = 'move_print_log_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
            }
        }else{
            $update_sql = "update import_center set status = 'MOVE ERROR' where type = 'move_print_log_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }
    }
    echo $update_sql.PHP_EOL;
    $erp_ddyun_user_db->query($update_sql);
}
echo date("Y-m-d H:i:s", time())." print_log data move finish".PHP_EOL;

function move_print_log($start_time, $end_time){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit = 0;
    while (true){
        $select_sql = "select * from print_log where created_time <= '{$end_time}' and created_time >= '{$start_time}' limit {$limit}, 200";
        echo "select sql db : ".$erp_ddyun_db_conf['name']." sql : ".$select_sql.PHP_EOL;
        $select_result = $erp_ddyun_db->getAll($select_sql);
        $limit += 200;
        if(empty($select_result)){
            break;
        }
        $sql = "insert ignore into print_log(id, order_id, shipment_id, platform_order_sn, platform_name, print_type, shipping_id, facility_shipping_template_id, shipping_name, tracking_number, mailnos_id, province_id, province_name, city_id, city_name, district_id
, district_name, shipping_address, receive_name, mobile, print_user, created_time, facility_id, warehouse_id, shop_id, batch_sn, thermal_type, batch_order, tactics_id, tactics_name, print_data, print_source) values ";
        foreach ($select_result as $print_log){
            $sql .= "(".
                checkNull($print_log['id']).",".
                checkNull($print_log['order_id']).",".
                checkNull($print_log['shipment_id']).",".
                checkNull($print_log['platform_order_sn']).",".
                checkNull($print_log['platform_name']).",".
                checkNull($print_log['print_type']).",".
                checkNull($print_log['shipping_id']).",".
                checkNull($print_log['facility_shipping_template_id']).",".
                checkNull($print_log['shipping_name']).",".
                checkNull($print_log['tracking_number']).",".
                checkNull($print_log['mailnos_id']).",".
                checkNull($print_log['province_id']).",".
                checkNull($print_log['province_name']).",".
                checkNull($print_log['city_id']).",".
                checkNull($print_log['city_name']).",".
                checkNull($print_log['district_id']).",".
                checkNull($print_log['district_name']).",".
                checkNull($print_log['shipping_address']).",".
                checkNull($print_log['receive_name']).",".
                checkNull($print_log['mobile']).",".
                checkNull($print_log['print_user']).",".
                checkNull($print_log['created_time']).",".
                checkNull($print_log['facility_id']).",".
                checkNull($print_log['warehouse_id']).",".
                checkNull($print_log['shop_id']).",".
                checkNull($print_log['batch_sn']).",".
                checkNull($print_log['thermal_type']).",".
                checkNull($print_log['batch_order']).",".
                checkNull($print_log['tactics_id']).",".
                checkNull($print_log['tactics_name']).",".
                checkNull($print_log['print_data']).",".
                checkNull($print_log['print_source'])."),";
        }
        $sql = substr($sql, 0, -1);
        echo date("Y-m-d H:i:s")." insert ".count($select_result). " print_log into history db [] db : ".$erp_ddyun_db_conf['name'].PHP_EOL;
        $erp_ddyun_history_db->query($sql);
    }
}

function move_encrypt_print_log($start_time, $end_time){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit_ecrypt = 0;
    while(true) {
        $select_sql = "
            select
                e.* 
            from 
                print_log l 
                inner join print_log_extension_encrypt e on l.batch_sn = e.batch_sn and l.batch_order = e.batch_order and l.facility_id = e.facility_id 
            where 
                l.created_time >= '{$start_time}' and 
                l.created_time <= '{$end_time}'
            limit
                {$limit_ecrypt}, 200
        ";
        echo "select sql db : " . $erp_ddyun_db_conf['name'] . " sql : " . $select_sql . PHP_EOL;
        $select_encrypt_result = $erp_ddyun_db->getAll($select_sql);
        $limit_ecrypt += 200;
        if (empty($select_encrypt_result)) {
            break;
        }
        $insert_sql = "
            insert into print_log_extension_encrypt(
                batch_sn,
                batch_order,
                facility_id,
                encrypt_shop_id,
                encrypt_platform_order_sn,
                mobile_encrypt,
                mobile_search_text,
                receive_name_encrypt,
                receive_name_search_text,
                shipping_address_encrypt,
                shipping_address_search_text,   
                aid,       
                created_time,
                last_updated_time,
                receive_name_mask,
                mobile_mask,
                shipping_address_mask)
            values ";
        foreach ($select_encrypt_result as $print_log_encrypt) {
            $insert_sql .= " (" .
                checkNull($print_log_encrypt['batch_sn']) . "," .
                checkNull($print_log_encrypt['batch_order']) . "," .
                checkNull($print_log_encrypt['facility_id']) . "," .
                checkNull($print_log_encrypt['encrypt_shop_id']) . "," .
                checkNull($print_log_encrypt['encrypt_platform_order_sn']) . "," .
                checkNull($print_log_encrypt['mobile_encrypt']) . "," .
                checkNull($print_log_encrypt['mobile_search_text']) . "," .
                checkNull($print_log_encrypt['receive_name_encrypt']) . "," .
                checkNull($print_log_encrypt['receive_name_search_text']) . "," .
                checkNull($print_log_encrypt['shipping_address_encrypt']) . "," .
                checkNull($print_log_encrypt['shipping_address_search_text']) . "," .
                checkNull($print_log_encrypt['aid']). "," .
                checkNull($print_log_encrypt['created_time']) . "," .
                checkNull($print_log_encrypt['last_updated_time']) . "," .
                checkNull($print_log_encrypt['receive_name_mask']) . "," .
                checkNull($print_log_encrypt['mobile_mask']) . "," .
                checkNull($print_log_encrypt['shipping_address_mask']) . "),";
        }
        $insert_sql = substr($insert_sql, 0, -1);
        $insert_sql .= "
            ON DUPLICATE KEY UPDATE
                batch_sn = VALUES(batch_sn),
                batch_order = VALUES(batch_order),
                facility_id = VALUES(facility_id),
                encrypt_shop_id = VALUES(encrypt_shop_id),
                encrypt_platform_order_sn = VALUES(encrypt_platform_order_sn),
                mobile_encrypt = VALUES(mobile_encrypt),
                mobile_search_text = VALUES(mobile_search_text),
                receive_name_encrypt = VALUES(receive_name_encrypt),
                receive_name_search_text = VALUES(receive_name_search_text),
                shipping_address_encrypt = VALUES(shipping_address_encrypt),
                shipping_address_search_text = VALUES(shipping_address_search_text),
                aid = VALUES(aid),
                created_time = VALUES(created_time),
                last_updated_time = VALUES(last_updated_time),
                receive_name_mask = VALUES(receive_name_mask),
                mobile_mask = VALUES(mobile_mask),
                shipping_address_mask = VALUES(shipping_address_mask)
        ";
        echo date("Y-m-d H:i:s") . " insert " . count($select_encrypt_result) . " print_log_extension_encrypt into history db [] db : " . $erp_ddyun_db_conf['name'] . PHP_EOL;
        $erp_ddyun_history_db->query($insert_sql);
    }
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