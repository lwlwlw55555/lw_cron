<?php

date_default_timezone_set("Asia/Shanghai");
require("includes/ClsPdo.php");

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

echo date("Y-m-d H:i:s", time()) . " move mailnos to history " . PHP_EOL;
$end_time = date('Y-m-d 00:00:00', time());
$start_time = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($end_time)));
for ($i = 0; $i < 256; $i++) {
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);

    // 插入import_center
    $origin_file_name = "move_mailnos_data_to_history_db ".date("Y-m-d", strtotime($start_time))." ".$erp_ddyun_history_db_conf['name'];
    $import_center_msg = $erp_ddyun_user_db->getOne("select status from import_center where origin_file_name = '{$origin_file_name}'");
    if( empty($import_center_msg) || $import_center_msg != 'DONE') {
        $import_sql = "insert ignore into import_center (type, origin_file_name, status, result) 
            value ('move_mailnos_data_to_history_db', '{$origin_file_name}', 'TODO', '{$erp_ddyun_db_conf['name']}')
            ON DUPLICATE KEY UPDATE id = VALUES(id), type = VALUES(type), origin_file_name = VALUES(origin_file_name), status = VALUES(status), result = VALUES(result)";
        echo $erp_ddyun_db_conf['name'] . " " . $import_sql . PHP_EOL;
        $erp_ddyun_user_db->query($import_sql);
    }

    // 判断每个库当天迁移数量是否对的上
    $count_sql = "select count(*) from mailnos where created_time >= '{$start_time}' and created_time < '{$end_time}' ";
    $count_update_sql = "select count(*) from mailnos where created_time < '{$start_time}' and last_update_time >= '{$start_time}' and last_update_time < '{$end_time}'";
    if($erp_ddyun_db->getOne($count_sql) == $erp_ddyun_history_db->getOne($count_sql)){
        echo $erp_ddyun_db_conf['name']."  "."prod与his ".date("Y-m-d", strtotime($start_time))." mailnos 订单数量一致 ".PHP_EOL;
        if($erp_ddyun_db->getOne($count_update_sql) <= $erp_ddyun_history_db->getOne($count_update_sql)){
            $update_sql = "update import_center set status = 'DONE' where type = 'move_mailnos_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            $update_sql = "update import_center set status = 'UPDATE ERROR' where type = 'move_mailnos_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
            move_update_mailnos($start_time, $end_time);
        }
        echo $update_sql.PHP_EOL;
        $erp_ddyun_user_db->query($update_sql);
        continue;
    }

    move_normal_mailnos($start_time, $end_time);
    move_update_mailnos($start_time, $end_time);

    if($erp_ddyun_db->getOne($count_sql) == $erp_ddyun_history_db->getOne($count_sql)){
        if($erp_ddyun_db->getOne($count_update_sql) <= $erp_ddyun_history_db->getOne($count_update_sql)){
            $update_sql = "update import_center set status = 'DONE' where type = 'move_mailnos_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            $update_sql = "update import_center set status = 'UPDATE ERROR' where type = 'move_mailnos_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
            move_update_mailnos($start_time, $end_time);
        }
    }else{
        $update_sql = "update import_center set status = 'MOVE ERROR' where type = 'move_mailnos_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
    }
    echo $update_sql.PHP_EOL;
    $erp_ddyun_user_db->query($update_sql);
}
echo date("Y-m-d H:i:s", time())."  ".$start_time . " to" . $end_time . " move finished" . PHP_EOL;


function move_normal_mailnos($start_time, $end_time){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit = 0;
    while(true){
        $select_sql = "select m.* from mailnos m  where m.created_time >= '{$start_time}' and m.created_time < '{$end_time}' limit {$limit}, 200";
        echo $erp_ddyun_db_conf['name'] . "   " . $select_sql . PHP_EOL;
        $limit += 200;
        $mailnos_list = $erp_ddyun_db->getAll($select_sql);
        if(empty($mailnos_list)){
            break;
        }
        move_mailnos($mailnos_list);
    }
}

function move_update_mailnos($start_time, $end_time){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit = 0;
    while (true){
        $select_update_sql = "select m.* from mailnos m where m.created_time <= '{$start_time}' and m.last_update_time > '{$start_time}' limit {$limit}, 200";
        echo $erp_ddyun_db_conf['name'] . "   " . $select_update_sql . PHP_EOL;
        $limit += 200;
        $mailnos_list = $erp_ddyun_db->getAll($select_update_sql);
        if(empty($mailnos_list)){
            break;
        }
        move_mailnos($mailnos_list);
    }
}

function move_mailnos($mailnos_list){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
        $insert_sql = "insert into mailnos(
                    id, 
                    shop_id,
                    tracking_number, 
                    status,                         
                    print_type,     
                    shipping_id, 
                    station,
                    station_no, 
                    sender_branch_no, 
                    sender_branch, 
                    package_no, 
                    package_name, 
                    lattice_mouth_no, 
                    express_type, 
                    pay_method, 
                    origin_code, 
                    dest_code, 
                    package_id, 
                    created_time, 
                    last_update_time, 
                    thermal_type, 
                    facility_id, 
                    warehouse_id, 
                    oauth_id, 
                    branch_share_id,
                    oauth_share_id, 
                    facility_shipping_template_id,
                    encrypted_data, 
                    signature)
                    values ";
        if (empty($mailnos_list)) {
            return;
        }
        foreach ($mailnos_list as $mailnos) {
            $insert_sql .= "(" .
                checkNull($mailnos['id']) . "," .
                checkNull($mailnos['shop_id']) . "," .
                checkNull($mailnos['tracking_number']) . "," .
                checkNull($mailnos['status']) . "," .
                checkNull($mailnos['print_type']) . "," .
                checkNull($mailnos['shipping_id']) . "," .
                checkNull($mailnos['station']) . "," .
                checkNull($mailnos['station_no']) . "," .
                checkNull($mailnos['sender_branch_no']) . "," .
                checkNull($mailnos['sender_branch']) . "," .
                checkNull($mailnos['package_no']) . "," .
                checkNull($mailnos['package_name']) . "," .
                checkNull($mailnos['lattice_mouth_no']) . "," .
                checkNull($mailnos['express_type']) . "," .
                checkNull($mailnos['pay_method']) . "," .
                checkNull($mailnos['origin_code']) . "," .
                checkNull($mailnos['dest_code']) . "," .
                checkNull($mailnos['package_id']) . "," .
                checkNull($mailnos['created_time']) . "," .
                checkNull($mailnos['last_update_time']) . "," .
                checkNull($mailnos['thermal_type']) . "," .
                checkNull($mailnos['facility_id']) . "," .
                checkNull($mailnos['warehouse_id']) . "," .
                checkNull($mailnos['oauth_id']) . "," .
                checkNull($mailnos['branch_share_id']) . "," .
                checkNull($mailnos['oauth_share_id']) . "," .
                checkNull($mailnos['facility_shipping_template_id']) . "," .
                checkNull($mailnos['encrypted_data']) . "," .
                checkNull($mailnos['signature']) . "),";
        }
        $insert_sql = substr($insert_sql, 0, -1);
        $insert_sql .= "ON DUPLICATE KEY UPDATE
                id = VALUES(id), 
                shop_id = VALUES(shop_id),
                tracking_number = VALUES(tracking_number), 
                status = VALUES(status),                         
                print_type = VALUES(print_type),     
                shipping_id = VALUES(shipping_id), 
                station = VALUES(station),
                station_no = VALUES(station_no), 
                sender_branch_no = VALUES(sender_branch_no), 
                sender_branch = VALUES(sender_branch), 
                package_no = VALUES(package_no), 
                package_name = VALUES(package_name), 
                lattice_mouth_no = VALUES(lattice_mouth_no), 
                express_type = VALUES(express_type), 
                pay_method = VALUES(pay_method), 
                origin_code = VALUES(origin_code), 
                dest_code = VALUES(dest_code), 
                package_id = VALUES(package_id), 
                created_time = VALUES(created_time), 
                last_update_time = VALUES(last_update_time), 
                thermal_type = VALUES(thermal_type), 
                facility_id = VALUES(facility_id), 
                warehouse_id = VALUES(warehouse_id), 
                oauth_id = VALUES(oauth_id), 
                branch_share_id = VALUES(branch_share_id),
                oauth_share_id = VALUES(oauth_share_id), 
                facility_shipping_template_id = VALUES(facility_shipping_template_id),
                encrypted_data = VALUES(encrypted_data), 
                signature = VALUES(signature)
        ";
        $erp_ddyun_history_db->query($insert_sql);
        $ids = array_column($mailnos_list, 'id');
        if (!empty($ids)) {
            $ids = implode(",", $ids);
            $sql = "select * from mailnos_extension where id in ({$ids})";
            $mailnos_extension_list = $erp_ddyun_db->getAll($sql);
            $insert_me_sql = "insert into mailnos_extension (
                id,
                facility_id,
                encrypted_data,
                signature
            ) VALUES ";
            foreach ($mailnos_extension_list as $mailnos_extension) {
                $insert_me_sql .= "(" .
                    checkNull($mailnos_extension['id']) . "," .
                    checkNull($mailnos_extension['facility_id']) . "," .
                    checkNull($mailnos_extension['encrypted_data']) . "," .
                    checkNull($mailnos_extension['signature']) . "),";
            }
            $insert_me_sql = substr($insert_me_sql, 0, -1);
            $insert_me_sql .= " ON DUPLICATE KEY UPDATE
                id = VALUES(id), 
                facility_id = VALUES(facility_id),
                encrypted_data = VALUES(encrypted_data),
                signature = VALUES(signature) ";
            echo "insert " . count($mailnos_extension_list) . " into mailnos_extension".PHP_EOL;
            $erp_ddyun_history_db->query($insert_me_sql);
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