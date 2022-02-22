<?php

date_default_timezone_set("Asia/Shanghai");
require ("includes/ClsPdo.php");

global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf, $sync_db;

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

$sync_db_conf = array(
    "host" => "100.65.1.244:32052",
    "user" => "erp_sync",
    "name" => "erpsync",
    "pass" => "Titanerpsync2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_user_db = ClsPdo::getInstance($erp_ddyun_db_user_conf);
$sync_db = ClsPdo::getInstance($sync_db_conf);

echo date("Y-m-d H:i:s", time())." move shipment to history ".PHP_EOL;
$now = date('Y-m-d H:i:s', time());
$end_hour = 0;
$type = ""; //判断哪一次跑的脚本
if($now < date('Y-m-d 21:00:00', time())){
    if($type < date("Y-m-d 03:00:00")){
        $type = "2nd";
    }else{
        $type = "3rd";
    }
    $end_hour = 24;
    $end_time = date('Y-m-d 00:00:00', time());
    $start_time = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($end_time)));
}else{
    $type = "1st";
    $end_hour = 22;
    $end_time = date('Y-m-d 22:00:00', time());
    $start_time = date('Y-m-d 00:00:00', time());
}

for ($i = 0; $i < 256 ; $i++){
    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_history_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);

    /**
     * 插import_center
     */
    $origin_file_name = "move_shipment_data_to_history_db ".date("Y-m-d", strtotime($start_time))." ".$erp_ddyun_history_db_conf['name'];
    $import_center_msg = $erp_ddyun_user_db->getOne("select status from import_center where origin_file_name = '{$origin_file_name}'");
    if( empty($import_center_msg) || $import_center_msg != 'DONE') {
        update_his_data($start_time);

        $status = $type . " TODO";
        $import_sql = "insert into import_center (
            type, 
            origin_file_name, 
            status,
            result
            ) values (
            'move_shipment_data_to_history_db', 
            '{$origin_file_name}', 
            '{$status}',
            '{$erp_ddyun_history_db_conf['name']}'
            )
            ON DUPLICATE KEY UPDATE
            id = VALUES(id),
            type = VALUES(type),
            origin_file_name = VALUES(origin_file_name),
            status = VALUES(status),
            result = VALUES(result)
            ";
        echo $erp_ddyun_db_conf['name'] . " " . $import_sql . PHP_EOL;
        $erp_ddyun_user_db->query($import_sql);
    }
    /**
     * 当天某个库的his数据与prod数据量一致以及更新数据量一致
     * 修改 import_center
     */
    $count_shipping_sql = "select count(*) from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time < '{$end_time}' and mgs.shipping_time >= '{$start_time}' and mgs.platform_name <> 'manu' ";
    $count_shipping_update_sql = "select count(*) from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time < '{$start_time}' and mgs.last_updated_time >= '{$start_time}' and mgs.last_updated_time < '{$end_time}' and mgs.platform_name <> 'manu' ";

    if($type != '1st' && $erp_ddyun_db->getOne($count_shipping_sql) == $erp_ddyun_history_db->getOne($count_shipping_sql)){
        echo $erp_ddyun_db_conf['name']."  "."prod与his 今日已发货订单数量一致 ".PHP_EOL;
        if($erp_ddyun_db->getOne($count_shipping_update_sql) <= $erp_ddyun_history_db->getOne($count_shipping_update_sql)){
            $update_import_center = "update import_center set status = 'DONE' where type = 'move_shipment_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            $update_import_center = "update import_center set status = 'PART ERROR' where type = 'move_shipment_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }
        echo $update_import_center.PHP_EOL;
        $erp_ddyun_user_db->query($update_import_center);
        continue;
    }

    for($hour = 0; $hour < $end_hour; $hour += 2){
        $select_start_time = date("Y-m-d H:i:s", strtotime($start_time) + 60 * 60 * $hour);
        $select_end_time = date("Y-m-d H:i:s", strtotime($start_time) + 60 * 60 * ($hour + 2 ));

        $limit = 0;
        while(true){
            $id_sql = "select mgs.shipment_id, mgsg.order_goods_id
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time >= '{$select_start_time}' and mgs.shipping_time < '{$select_end_time}' limit {$limit}, 200";
            $limit += 200;

            echo $id_sql.PHP_EOL;
            $ids = $erp_ddyun_db->getAll($id_sql);
            if(empty($ids)){
                break;
            }
            $shipment_ids = implode(",", array_column($ids, 'shipment_id'));
            $order_goods_ids = implode(",", array_column($ids, 'order_goods_id'));
            move_shipment($shipment_ids);
            move_mgse($shipment_ids);
            move_mgsg($order_goods_ids);
            move_finance_detail($order_goods_ids);
        }
    }

    $update_import_center = "update import_center set status = '1st DONE' where type = 'move_shipment_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
    if($type != '1st' && $erp_ddyun_db->getOne($count_shipping_sql) != $erp_ddyun_history_db->getOne($count_shipping_sql)){
        echo $erp_ddyun_db_conf['name']."  "."prod与his数量不一致 ".PHP_EOL;
        $update_import_center = "update import_center set status = 'NORMAL ERROR' where type = 'move_shipment_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
    }
    if($type != '1st' && $erp_ddyun_db->getOne($count_shipping_sql) == $erp_ddyun_history_db->getOne($count_shipping_sql)){
        if($erp_ddyun_db->getOne($count_shipping_update_sql) <= $erp_ddyun_history_db->getOne($count_shipping_update_sql)){
            $update_import_center = "update import_center set status = 'DONE' where type = 'move_shipment_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }else{
            $update_import_center = "update import_center set status = 'UPDATE ERROR' where type = 'move_shipment_data_to_history_db' and origin_file_name = '{$origin_file_name}'";
        }
    }
    echo $erp_ddyun_history_db_conf['name']."  ".$update_import_center.PHP_EOL;
    $erp_ddyun_user_db->query($update_import_center);
    echo $start_time." to ".$end_time."   ".$erp_ddyun_db_conf['name']." shipment move finish".PHP_EOL;
}

if($type == '3rd'){
    $time = 3;
    while (true){
        $time--;
        $select_sql = "select * from import_center where type = 'move_shipment_data_to_history_db' and created_time > '{$start_time}'";
        $import_center_msg_list = $erp_ddyun_user_db->getAll($select_sql);
        $num = 0;
        foreach ($import_center_msg_list as $import_center_msg){
            if($import_center_msg['status'] == 'DONE'){
                $num ++;
            }
            if($import_center_msg['status'] == 'UPDATE ERROR'){
                $erp_ddyun_db_conf['name'] = $import_center_msg['result'];
                $erp_ddyun_history_db_conf['name'] = $import_center_msg['result'];
                $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
                $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
                echo $erp_ddyun_history_db_conf['name']." UPDATE ERROR  Continue Update".PHP_EOL;
                update_his_data($start_time);
            }
            if($import_center_msg['status'] == 'NORMAL ERROR'){
                send_sms("{$import_center_msg['result']} 当天已发货数据迁移失败 ", '13567177855,15272027675');
            }
        }
        if($num == 256){
            break;
        }
        if($time < 0){
            send_sms("当天历史库增量修改失败 ", '13567177855,15272027675');
            break;
        }
    }
}

function move_shipment($shipment_ids){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
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
                                    group_status,
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
                                    is_add_price_sf,
                                    extra_delivery_list)  values ";
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
            checkNull($shipment['group_status']).",".
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
            checkNull($shipment['is_add_price_sf']).",".
            checkNull($shipment['extra_delivery_list'])."),";
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
                group_status = VALUES(group_status),
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
                is_add_price_sf = VALUES(is_add_price_sf),
                extra_delivery_list = VALUES(extra_delivery_list)";
    if(!empty($shipment_list)){
        $erp_ddyun_history_db->query($insert_shipment_sql);
        echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($shipment_list) ." shipment into history".PHP_EOL;
    }
}

function move_mgsg($order_goods_ids){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
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
                    outer_goods_id,
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
            checkNull($mgsg['outer_goods_id']).",".
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
        echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($mgsg_list) ." multi_goods_shipment_goods into history".PHP_EOL;
    }
}

function move_mgse($shipment_ids){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
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
                                    aid,
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
            checkNull($shipment_extension['aid']).",".
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
                aid = VALUES(aid),
                created_time = VALUES(created_time),
                last_updated_time = VALUES(last_updated_time),
                receive_name_mask = VALUES(receive_name_mask),
                mobile_mask = VALUES(mobile_mask),
                shipping_address_mask = VALUES(shipping_address_mask),
                address_id = VALUES(address_id)
            ";
    if(!empty($shipment_extension_list)){
        $erp_ddyun_history_db->query($insert_shipment_extension_sql);
        echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($shipment_extension_list) ." shipment_extension into history".PHP_EOL;
    }
}

function move_finance_detail($order_goods_ids){
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
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
        echo  date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']."  insert ".count($finance_detail_list) ." finance_detail into history".PHP_EOL;
    }
}

function update_his_data($start_time){
    /**
     * 修改 start_time 以前发货 ，start_time之后修改的订单历史库同步更新
     */
    global $erp_ddyun_db, $erp_ddyun_history_db, $erp_ddyun_db_conf, $erp_ddyun_history_db_conf;
    $limit = 0;
    while(true){
        $sql = "select mgs.shipment_id, mgsg.order_goods_id
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.last_updated_time >= '{$start_time}' and mgs.shipping_time < '{$start_time}' limit {$limit}, 200";
        $limit += 200;

        echo $sql.PHP_EOL;
        $ids = $erp_ddyun_db->getAll($sql);
        if(empty($ids)){
            break;
        }
        $shipment_ids = implode(",", array_column($ids, 'shipment_id'));
        $order_goods_ids = implode(",", array_column($ids, 'order_goods_id'));
        move_shipment($shipment_ids);
        move_mgse($shipment_ids);
        move_mgsg($order_goods_ids);
        move_finance_detail($order_goods_ids);
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

function send_sms($msg, $mobile = '13567177855,13615811405') {
    global $sync_db;
    $sql = "INSERT into send_msg (receiver_mobiles, msg) VALUES ('{$mobile}','{$msg}');";
    $sync_db->query($sql);
    echo "[] " . date("Y-m-d H:i:s")." send_msg sql:{$sql}".PHP_EOL;
}