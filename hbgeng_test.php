<?php

date_default_timezone_set("Asia/Shanghai");
require ("includes/ClsPdo.php");

global $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;

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


echo date("Y-m-d H:i:s", time())." move shipment to history ".PHP_EOL;

$end_time = date('Y-m-d 00:00:00', strtotime("2020-09-02"));
$start_time = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($end_time)));
while (true) {
    for ($i = 0; $i < 256; $i++) {
        $erp_ddyun_db_conf['name'] = 'erp_' . $i;
        $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
        $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
        $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
        $count_sql = "select count(*) from mailnos where created_time >= '{$start_time}' and created_time < '{$end_time}' ";
        $his_count = $erp_ddyun_history_db->getOne($count_sql);
        $prod_count = $erp_ddyun_db->getOne($count_sql);
        if ($his_count < $prod_count) {
            move_mailnos();
            echo $erp_ddyun_db_conf['name'] . "   mailnos yes" . PHP_EOL;
        } else {
            echo $erp_ddyun_db_conf['name'] . "  mailnos  : his " . $his_count . "  prod " . $prod_count . PHP_EOL;
        }

        $count_sql = "select count(*) from shipment_package where created_time >= '{$start_time}' and created_time < '{$end_time}' ";
        $his_count = $erp_ddyun_history_db->getOne($count_sql);
        $prod_count = $erp_ddyun_db->getOne($count_sql);
        if ($his_count < $prod_count) {
            move_shipment_package();
            echo $erp_ddyun_db_conf['name'] . "   shipment_package move finish" . PHP_EOL;
        } else {
            echo $erp_ddyun_db_conf['name'] . " shipment package his : " . $his_count . "  prod : " . $prod_count . PHP_EOL;
        }

        $count_sql = "select count(*)
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time < '{$end_time}' and mgs.shipping_time >= '{$start_time}' ";
        $his_count = $erp_ddyun_history_db->getOne($count_sql);
        $prod_count = $erp_ddyun_db->getOne($count_sql);
        if ($his_count < $prod_count) {
            move_shipment();
            echo $erp_ddyun_db_conf['name'] . "  shipment  his < prod " . PHP_EOL;
        } else {
            echo $erp_ddyun_db_conf['name'] . " shipment his : " . $his_count . "  prod : " . $prod_count . PHP_EOL;
        }
    }
    if($end_time > '2020-09-11'){
        break;
    }
    $start_time = $end_time;
    $end_time = date("Y-m-d 00:00:00", strtotime("+1 day", strtotime($start_time)));
}
echo  "check finish".PHP_EOL;

function move_mailnos(){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_history_db_conf, $erp_ddyun_db_conf, $start_time, $end_time;
    $limit = 0;
    while (true) {
        $select_sql = "select m.* from mailnos m  where m.created_time >= '{$start_time}' and m.created_time < '{$end_time}' order by m.id limit {$limit}, 200";
        echo $erp_ddyun_db_conf['name'] . "   " . $select_sql . PHP_EOL;
        $limit += 200;
        $mailnos_list = $erp_ddyun_db->getAll($select_sql);
        $insert_sql = "insert into mailnos(
                    id, 
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
                    oauth_share_id, 
                    facility_shipping_template_id,
                    encrypted_data, 
                    signature)
                    values ";
        if (empty($mailnos_list)) {
            break;
        }
        foreach ($mailnos_list as $mailnos) {
            $insert_sql .= "(" .
                checkNull($mailnos['id']) . "," .
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
                checkNull($mailnos['oauth_share_id']) . "," .
                checkNull($mailnos['facility_shipping_template_id']).",".
                checkNull($mailnos['encrypted_data']) . "," .
                checkNull($mailnos['signature']) . "),";
        }
        $insert_sql = substr($insert_sql, 0, -1);
        $insert_sql .= "ON DUPLICATE KEY UPDATE
                id = VALUES(id), 
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
                oauth_share_id = VALUES(oauth_share_id), 
                facility_shipping_template_id = VALUES(facility_shipping_template_id),
                encrypted_data = VALUES(encrypted_data), 
                signature = VALUES(signature)
        ";
        $erp_ddyun_history_db->query($insert_sql);
        echo date("Y-m-d H:i:s", time())."  ".$erp_ddyun_db_conf['name'] . " insert " . count($mailnos_list) . " mailnos to prod" . PHP_EOL;
    }

    $insert_sql = "INSERT into 
            mailnos_extension(
                id,
                facility_id,
                encrypted_data,
                signature
            ) SELECT
                m.id,
                m.facility_id,
                m.encrypted_data,
                m.signature
            FROM
                mailnos m 
                left join mailnos_extension me on m.id = me.id and m.facility_id = me.facility_id
            WHERE
                me.id is null
                and m.created_time > '{$start_time}'";
    $erp_ddyun_history_db->query($insert_sql);
    echo date("Y-m-d H:i:s", time())."  ".$start_time . " to" . $end_time . "  " . $erp_ddyun_db_conf['name'] . "  insert finish" . PHP_EOL;
}


function move_shipment_package(){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_history_db_conf, $erp_ddyun_db_conf, $start_time, $end_time;
    $limit = 0;
    while(true){
        $select_sql = "select * from shipment_package where created_time >= '{$start_time}' and created_time < '{$end_time}' order by id limit {$limit}, 200";
        echo date("Y-m-d H:i:s", time())."  ".$erp_ddyun_db_conf['name']."   ".$select_sql.PHP_EOL;
        $limit += 200;
        $shipment_package_list = $erp_ddyun_db->getAll($select_sql);
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
            break;
        }
        foreach ($shipment_package_list as $shipment_package){
            $insert_sql .= "(
                    '{$shipment_package['id']}',
                    '{$shipment_package['package_id']}',
                    '{$shipment_package['shipment_id']}',
                    '{$shipment_package['status']}',
                    '{$shipment_package['is_print_tracking']}',
                    '{$shipment_package['print_time']}',
                    '{$shipment_package['facility_id']}',
                    '{$shipment_package['created_time']}',
                    '{$shipment_package['last_updated_time']}'),";
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
        echo date("Y-m-d H:i:s", time())."  ".$erp_ddyun_db_conf['name']." insert ".count($shipment_package_list)." shipment_package to prod".PHP_EOL;
    }
    echo date("Y-m-d H:i:s", time())."  ".$start_time." to ".$end_time.$erp_ddyun_db_conf['name']."  insert finished".PHP_EOL;
}


function move_shipment(){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_history_db_conf, $erp_ddyun_db_conf, $start_time, $end_time;
    $limit = 0;
    while(true){
        $id_sql = "select mgs.shipment_id, mgsg.order_goods_id
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time < '{$start_time}' and mgs.shipping_time >= '{$end_time}' order by shipment_id limit {$limit}, 200";
        $limit += 200;
        echo $id_sql.PHP_EOL;
        $ids = $erp_ddyun_db->getAll($id_sql);
        if(empty($ids)){
            break;
        }
        $shipment_ids = implode(",", array_column($ids, 'shipment_id'));
        $order_goods_ids = implode(",", array_column($ids, 'order_goods_id'));

        $select_shipment_sql = "select  * from multi_goods_shipment where shipment_id in ({$shipment_ids})";
        $shipment_list = $erp_ddyun_db->getAll($select_shipment_sql);
        $insert_shipment_sql = "insert into 
                                multi_goods_shipment(
                                    shipment_id, 
                                    order_sn, 
                                    platform_order_sn, 
                                    created_user, 
                                    platform_name, 
                                    shop_id, 
                                    facility_id,
                                    warehouse_id, 
                                    warehouse_name, 
                                    is_print_tracking, 
                                    is_print_waybill, 
                                    sku_number, 
                                    goods_number, 
                                    shipment_status, 
                                    status, 
                                    order_flag, 
                                    tactics_id, 
                                    tactics_name, 
                                    order_flag_name, 
                                    address_id, 
                                    province_id, 
                                    province_name, 
                                    city_id, 
                                    city_name, 
                                    district_id, 
                                    district_name, 
                                    shipping_address, 
                                    receive_name, 
                                    mobile, 
                                    shipping_time, 
                                    order_created_time, 
                                    confirm_time, 
                                    shipping_due_time, 
                                    print_time, 
                                    shipping_user, 
                                    goods_amount, 
                                    shipping_amount, 
                                    order_amount, 
                                    discount_amount, 
                                    pay_amount, 
                                    is_note, 
                                    order_type, 
                                    buyer_note, 
                                    seller_note, 
                                    buyer_id, 
                                    buyer_nick, 
                                    seller_flag, 
                                    shipping_type, 
                                    shipping_id, 
                                    facility_shipping_template_id,
                                    shipping_name, 
                                    tracking_number, 
                                    sortation_name, 
                                    sortation_code, 
                                    origin_code, 
                                    origin_name, 
                                    consolidation_code, 
                                    consolidation_name, 
                                    route_code, 
                                    created_time, 
                                    last_updated_time, 
                                    sku_number_type, 
                                    is_buyer_note, 
                                    is_seller_note, 
                                    thermal_type, 
                                    is_cod, 
                                    is_merged, 
                                    is_main_shipment, 
                                    merged_main_shipment_id, 
                                    buyer_rate, 
                                    seller_rate, 
                                    pre_shipping_time, 
                                    logistic_status,
                                    last_route_context, 
                                    is_pdd_cancel_send, 
                                    weight, 
                                    is_add_price_sf)  values ";
        foreach ($shipment_list as $shipment){
            $insert_shipment_sql .= "(".
                checkNull($shipment['shipment_id']).",".
                checkNull($shipment['order_sn']).",".
                checkNull($shipment['platform_order_sn']).",".
                checkNull($shipment['created_user']).",".
                checkNull($shipment['platform_name']).",".
                checkNull($shipment['shop_id']).",".
                checkNull($shipment['facility_id']).",".
                checkNull($shipment['warehouse_id']).",".
                checkNull($shipment['warehouse_name']).",".
                checkNull($shipment['is_print_tracking']).",".
                checkNull($shipment['is_print_waybill']).",".
                checkNull($shipment['sku_number']).",".
                checkNull($shipment['goods_number']).",".
                checkNull($shipment['shipment_status']).",".
                checkNull($shipment['status']).",".
                checkNull($shipment['order_flag']).",".
                checkNull($shipment['tactics_id']).",".
                checkNull($shipment['tactics_name']).",".
                checkNull($shipment['order_flag_name']).",".
                checkNull($shipment['address_id']).",".
                checkNull($shipment['province_id']).",".
                checkNull($shipment['province_name']).",".
                checkNull($shipment['city_id']).",".
                checkNull($shipment['city_name']).",".
                checkNull($shipment['district_id']).",".
                checkNull($shipment['district_name']).",".
                checkNull($shipment['shipping_address']).",".
                checkNull($shipment['receive_name']).",".
                checkNull($shipment['mobile']).",".
                checkNull($shipment['shipping_time']).",".
                checkNull($shipment['order_created_time']).",".
                checkNull($shipment['confirm_time']).",".
                checkNull($shipment['shipping_due_time']).",".
                checkNull($shipment['print_time']).",".
                checkNull($shipment['shipping_user']).",".
                checkNull($shipment['goods_amount']).",".
                checkNull($shipment['shipping_amount']).",".
                checkNull($shipment['order_amount']).",".
                checkNull($shipment['discount_amount']).",".
                checkNull($shipment['pay_amount']).",".
                checkNull($shipment['is_note']).",".
                checkNull($shipment['order_type']).",".
                checkNull($shipment['buyer_note']).",".
                checkNull($shipment['seller_note']).",".
                checkNull($shipment['buyer_id']).",".
                checkNull($shipment['buyer_nick']).",".
                checkNull($shipment['seller_flag']).",".
                checkNull($shipment['shipping_type']).",".
                checkNull($shipment['shipping_id']).",".
                checkNull($shipment['facility_shipping_template_id']).",".
                checkNull($shipment['shipping_name']).",".
                checkNull($shipment['tracking_number']).",".
                checkNull($shipment['sortation_name']).",".
                checkNull($shipment['sortation_code']).",".
                checkNull($shipment['origin_code']).",".
                checkNull($shipment['origin_name']).",".
                checkNull($shipment['consolidation_code']).",".
                checkNull($shipment['consolidation_name']).",".
                checkNull($shipment['route_code']).",".
                checkNull($shipment['created_time']).",".
                checkNull($shipment['last_updated_time']).",".
                checkNull($shipment['sku_number_type']).",".
                checkNull($shipment['is_buyer_note']).",".
                checkNull($shipment['is_seller_note']).",".
                checkNull($shipment['thermal_type']).",".
                checkNull($shipment['is_cod']).",".
                checkNull($shipment['is_merged']).",".
                checkNull($shipment['is_main_shipment']).",".
                checkNull($shipment['merged_main_shipment_id']).",".
                checkNull($shipment['buyer_rate']).",".
                checkNull($shipment['seller_rate']).",".
                checkNull($shipment['pre_shipping_time']).",".
                checkNull($shipment['logistic_status']).",".
                checkNull($shipment['last_route_context']).",".
                checkNull($shipment['is_pdd_cancel_send']).",".
                checkNull($shipment['weight']).",".
                checkNull($shipment['is_add_price_sf'])."),";
        }
        $insert_shipment_sql = substr($insert_shipment_sql, 0, -1);
        $insert_shipment_sql .= " ON DUPLICATE KEY UPDATE
                shipment_id = VALUES(shipment_id), 
                order_sn = VALUES(order_sn), 
                platform_order_sn = VALUES(platform_order_sn), 
                created_user = VALUES(created_user), 
                platform_name = VALUES(platform_name), 
                shop_id = VALUES(shop_id), 
                facility_id = VALUES(facility_id), 
                warehouse_id = VALUES(warehouse_id), 
                warehouse_name = VALUES(warehouse_name), 
                is_print_tracking = VALUES(is_print_tracking), 
                is_print_waybill = VALUES(is_print_waybill), 
                sku_number = VALUES(sku_number), 
                goods_number = VALUES(goods_number), 
                shipment_status = VALUES(shipment_status), 
                status = VALUES(status), 
                order_flag = VALUES(order_flag), 
                tactics_id = VALUES(tactics_id), 
                tactics_name = VALUES(tactics_name), 
                order_flag_name = VALUES(order_flag_name), 
                address_id = VALUES(address_id), 
                province_id = VALUES(province_id), 
                province_name = VALUES(province_name), 
                city_id = VALUES(city_id), 
                city_name = VALUES(city_name), 
                district_id = VALUES(district_id), 
                district_name = VALUES(district_name), 
                shipping_address = VALUES(shipping_address), 
                receive_name = VALUES(receive_name), 
                mobile = VALUES(mobile), 
                shipping_time = VALUES(shipping_time), 
                order_created_time = VALUES(order_created_time), 
                confirm_time = VALUES(confirm_time), 
                shipping_due_time = VALUES(shipping_due_time), 
                print_time = VALUES(print_time), 
                shipping_user = VALUES(shipping_user), 
                goods_amount = VALUES(goods_amount), 
                shipping_amount = VALUES(shipping_amount), 
                order_amount = VALUES(order_amount), 
                discount_amount = VALUES(discount_amount), 
                pay_amount = VALUES(pay_amount), 
                is_note = VALUES(is_note), 
                order_type = VALUES(order_type), 
                buyer_note = VALUES(buyer_note), 
                seller_note = VALUES(seller_note), 
                buyer_id = VALUES(buyer_id), 
                buyer_nick = VALUES(buyer_nick), 
                seller_flag = VALUES(seller_flag), 
                shipping_type = VALUES(shipping_type), 
                shipping_id = VALUES(shipping_id), 
                facility_shipping_template_id = VALUES(facility_shipping_template_id),
                shipping_name = VALUES(shipping_name), 
                tracking_number = VALUES(tracking_number), 
                sortation_name = VALUES(sortation_name), 
                sortation_code = VALUES(sortation_code), 
                origin_code = VALUES(origin_code), 
                origin_name = VALUES(origin_name), 
                consolidation_code = VALUES(consolidation_code), 
                consolidation_name = VALUES(consolidation_name), 
                route_code = VALUES(route_code), 
                created_time = VALUES(created_time), 
                last_updated_time = VALUES(last_updated_time), 
                sku_number_type = VALUES(sku_number_type), 
                is_buyer_note = VALUES(is_buyer_note), 
                is_seller_note = VALUES(is_seller_note), 
                thermal_type = VALUES(thermal_type), 
                is_cod = VALUES(is_cod), 
                is_merged = VALUES(is_merged), 
                is_main_shipment = VALUES(is_main_shipment), 
                merged_main_shipment_id = VALUES(merged_main_shipment_id), 
                buyer_rate = VALUES(buyer_rate), 
                seller_rate = VALUES(seller_rate), 
                pre_shipping_time = VALUES(pre_shipping_time), 
                logistic_status = VALUES(logistic_status), 
                last_route_context = VALUES(last_route_context), 
                is_pdd_cancel_send = VALUES(is_pdd_cancel_send), 
                weight = VALUES(weight), 
                is_add_price_sf = VALUES(is_add_price_sf)";
        if(!empty($shipment_list)){
            $erp_ddyun_history_db->query($insert_shipment_sql);
            echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($shipment_list) ." shipment into his".PHP_EOL;
        }


        $select_shipment_extension_sql = "select * from multi_goods_shipment_extension where shipment_id in ({$shipment_ids})";
        $shipment_extension_list = $erp_ddyun_db->getAll($select_shipment_extension_sql);
        $insert_shipment_extension_sql = "insert into 
                multi_goods_shipment_extension(
                                    shipment_id,
                                    facility_id,
                                    encrypt_shop_id,
                                    mobile_encrypt,
                                    mobile_search_text,
                                    receive_name_encrypt,
                                    receive_name_search_text,
                                    shipping_address_encrypt,
                                    shipping_address_search_text,
                                    created_time,
                                    last_updated_time,
                                    receive_name_mask,
                                    mobile_mask,
                                    shipping_address_mask,
                                    address_id) values ";
        foreach ($shipment_extension_list as $shipment_extension){
            $insert_shipment_extension_sql .= "(".
                checkNull($shipment_extension['shipment_id']).",".
                checkNull($shipment_extension['facility_id']).",".
                checkNull($shipment_extension['encrypt_shop_id']).",".
                checkNull($shipment_extension['mobile_encrypt']).",".
                checkNull($shipment_extension['mobile_search_text']).",".
                checkNull($shipment_extension['receive_name_encrypt']).",".
                checkNull($shipment_extension['receive_name_search_text']).",".
                checkNull($shipment_extension['shipping_address_encrypt']).",".
                checkNull($shipment_extension['shipping_address_search_text']).",".
                checkNull($shipment_extension['created_time']).",".
                checkNull($shipment_extension['last_updated_time']).",".
                checkNull($shipment_extension['receive_name_mask']).",".
                checkNull($shipment_extension['mobile_mask']).",".
                checkNull($shipment_extension['shipping_address_mask']).",".
                checkNull($shipment_extension['address_id'])."),";
        }
        $insert_shipment_extension_sql = substr($insert_shipment_extension_sql, 0, -1);
        $insert_shipment_extension_sql .= " ON DUPLICATE KEY UPDATE
                shipment_id = VALUES(shipment_id),
                facility_id = VALUES(facility_id),
                encrypt_shop_id = VALUES(encrypt_shop_id),
                mobile_encrypt = VALUES(mobile_encrypt),
                mobile_search_text = VALUES(mobile_search_text),
                receive_name_encrypt = VALUES(receive_name_encrypt),
                receive_name_search_text = VALUES(receive_name_search_text),
                shipping_address_encrypt = VALUES(shipping_address_encrypt),
                shipping_address_search_text = VALUES(shipping_address_search_text),
                created_time = VALUES(created_time),
                last_updated_time = VALUES(last_updated_time),
                receive_name_mask = VALUES(receive_name_mask),
                mobile_mask = VALUES(mobile_mask),
                shipping_address_mask = VALUES(shipping_address_mask),
                address_id = VALUES(address_id)
            ";
        if(!empty($shipment_extension_list)){
            $erp_ddyun_history_db->query($insert_shipment_extension_sql);
            echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($shipment_extension_list) ." shipment_extension into his".PHP_EOL;
        }

        $select_mgsg_sql = "select * from multi_goods_shipment_goods where order_goods_id in ({$order_goods_ids})";
        $mgsg_list = $erp_ddyun_db->getAll($select_mgsg_sql);
        $insert_mgsg_sql = "insert into multi_goods_shipment_goods(
                    order_goods_id,
                    shipment_id,
                    shipment_status,
                    order_sn,
                    shop_id,
                    facility_id,
                    order_id,
                    platform_sku_id,
                    sku_id,
                    outer_id,
                    platform_goods_id,
                    goods_id,
                    goods_name,
                    goods_alias,
                    sku_alias,
                    style_value,
                    goods_number,
                    image_url,
                    created_time,
                    last_updated_time,
                    price_dot,
                    discount_amount,
                    goods_amount,
                    original_shipment_id,
                    original_order_goods_id,
                    platform_order_goods_sn,
                    gift_tactics_id,
                    gift_tactics_detail_id) values ";
        foreach ($mgsg_list as $mgsg){
            $insert_mgsg_sql .= "(".
                checkNull($mgsg['order_goods_id']).",".
                checkNull($mgsg['shipment_id']).",".
                checkNull($mgsg['shipment_status']).",".
                checkNull($mgsg['order_sn']).",".
                checkNull($mgsg['shop_id']).",".
                checkNull($mgsg['facility_id']).",".
                checkNull($mgsg['order_id']).",".
                checkNull($mgsg['platform_sku_id']).",".
                checkNull($mgsg['sku_id']).",".
                checkNull($mgsg['outer_id']).",".
                checkNull($mgsg['platform_goods_id']).",".
                checkNull($mgsg['goods_id']).",".
                checkNull($mgsg['goods_name']).",".
                checkNull($mgsg['goods_alias']).",".
                checkNull($mgsg['sku_alias']).",".
                checkNull($mgsg['style_value']).",".
                checkNull($mgsg['goods_number']).",".
                checkNull($mgsg['image_url']).",".
                checkNull($mgsg['created_time']).",".
                checkNull($mgsg['last_updated_time']).",".
                checkNull($mgsg['price_dot']).",".
                checkNull($mgsg['discount_amount']).",".
                checkNull($mgsg['goods_amount']).",".
                checkNull($mgsg['original_shipment_id']).",".
                checkNull($mgsg['original_order_goods_id']).",".
                checkNull($mgsg['platform_order_goods_sn']).",".
                checkNull($mgsg['gift_tactics_id']).",".
                checkNull($mgsg['gift_tactics_detail_id'])."),";
        }
        $insert_mgsg_sql = substr($insert_mgsg_sql, 0, -1);
        $insert_mgsg_sql .= " ON DUPLICATE KEY UPDATE
                order_goods_id = VALUES(order_goods_id),
                shipment_id = VALUES(shipment_id),
                shipment_status = VALUES(shipment_status),
                order_sn = VALUES(order_sn),
                shop_id = VALUES(shop_id),
                facility_id = VALUES(facility_id),
                order_id = VALUES(order_id),
                platform_sku_id = VALUES(platform_sku_id),
                sku_id = VALUES(sku_id),
                outer_id = VALUES(outer_id),
                platform_goods_id = VALUES(platform_goods_id),
                goods_id = VALUES(goods_id),
                goods_name = VALUES(goods_name),
                goods_alias = VALUES(goods_alias),
                sku_alias = VALUES(sku_alias),
                style_value = VALUES(style_value),
                goods_number = VALUES(goods_number),
                image_url = VALUES(image_url),
                created_time = VALUES(created_time),
                last_updated_time = VALUES(last_updated_time),
                price_dot = VALUES(price_dot),
                discount_amount = VALUES(discount_amount),
                goods_amount = VALUES(goods_amount),
                original_shipment_id = VALUES(original_shipment_id),
                original_order_goods_id = VALUES(original_order_goods_id),
                platform_order_goods_sn = VALUES(platform_order_goods_sn),
                gift_tactics_id = VALUES(gift_tactics_id),
                gift_tactics_detail_id = VALUES(gift_tactics_detail_id)
            ";
        if(!empty($mgsg_list)){
            $erp_ddyun_history_db->query($insert_mgsg_sql);
            echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($mgsg_list) ." multi_goods_shipment_goods into his".PHP_EOL;
        }

        $select_finance_detail_sql = "select * from finance_detail where order_goods_id in ({$order_goods_ids})";
        $finance_detail_list = $erp_ddyun_db->getAll($select_finance_detail_sql);
        $insert_finance_detail = "insert into finance_detail(
                order_goods_id,
                order_sn,
                shop_id,
                facility_id,
                pay_status,
                refund_time,
                shipping_fee,
                package_fee,
                purchase_price,
                small_transfer_amount,
                sync_update_status,
                created_time,
                last_updated_time) value ";
        foreach ($finance_detail_list as $finance_detail){
            $insert_finance_detail .= "(".
                checkNull($finance_detail['order_goods_id']).",".
                checkNull($finance_detail['order_sn']).",".
                checkNull($finance_detail['shop_id']).",".
                checkNull($finance_detail['facility_id']).",".
                checkNull($finance_detail['pay_status']).",".
                checkNull($finance_detail['refund_time']).",".
                checkNull($finance_detail['shipping_fee']).",".
                checkNull($finance_detail['package_fee']).",".
                checkNull($finance_detail['purchase_price']).",".
                checkNull($finance_detail['small_transfer_amount']).",".
                checkNull($finance_detail['sync_update_status']).",".
                checkNull($finance_detail['created_time']).",".
                checkNull($finance_detail['last_updated_time'])."),";
        }
        $insert_finance_detail = substr($insert_finance_detail, 0, -1);
        $insert_finance_detail .= " ON DUPLICATE KEY UPDATE
                order_goods_id = VALUES(order_goods_id),
                order_sn = VALUES(order_sn),
                shop_id = VALUES(shop_id),
                facility_id = VALUES(facility_id),
                pay_status = VALUES(pay_status),
                refund_time = VALUES(refund_time),
                shipping_fee = VALUES(shipping_fee),
                package_fee = VALUES(package_fee),
                purchase_price = VALUES(purchase_price),
                small_transfer_amount = VALUES(small_transfer_amount),
                sync_update_status = VALUES(sync_update_status),
                created_time = VALUES(created_time),
                last_updated_time = VALUES(last_updated_time)
            ";
        if(!empty($finance_detail_list)){
            $erp_ddyun_history_db->query($insert_finance_detail);
            echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($finance_detail_list) ." finance_detail into his".PHP_EOL;
        }
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