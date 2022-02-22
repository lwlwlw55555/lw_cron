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

for ($i = 0; $i < 256 ; $i ++){
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
    $limit = 0 ;
    while(true){
        $select_sql = "select * from facility_shipping limit {$limit}, 200";
        $limit += 200;
        $facility_shipping_list = $erp_ddyun_db->getAll($select_sql);
        if(empty($facility_shipping_list)){
            break;
        }
        $insert_sql = "insert ignore into facility_shipping(
             facility_shipping_id,
             facility_id,
             warehouse_id,
             shipping_id,
             is_cainiao_thermal,
             is_pdd_thermal,
             is_kuaidi_thermal,
             default_thermal_type,
             enable,
             send_addr_code,
             sf_account,
             facility_shipping_name,
             facility_shipping_user,
             facility_shipping_password,
             facility_shipping_site,
             facility_shipping_account,
             logistic_service_id,
             pay_method,
             service_type,
             service_type_code,
             sort,
             cainiao_oauth_id,
             cainiao_branch_code,
             cainiao_branch_name,
             cainiao_branch_address,
             cainiao_template_id,
             pdd_oauth_id,
             pdd_branch_code,
             pdd_branch_name,
             pdd_branch_address,
             pdd_template_id,
             kuaidi_template_id,
             shipping_fee_template_id,
             created_time,
             last_updated_time,
             logistic_service) VALUES ";
        foreach ($facility_shipping_list as $facility_shipping){
            $insert_sql .= "(".
            checkNull($facility_shipping['facility_shipping_id']).",".
            checkNull($facility_shipping['facility_id']).",".
            checkNull($facility_shipping['warehouse_id']).",".
            checkNull($facility_shipping['shipping_id']).",".
            checkNull($facility_shipping['is_cainiao_thermal']).",".
            checkNull($facility_shipping['is_pdd_thermal']).",".
            checkNull($facility_shipping['is_kuaidi_thermal']).",".
            checkNull($facility_shipping['default_thermal_type']).",".
            checkNull($facility_shipping['enable']).",".
            checkNull($facility_shipping['send_addr_code']).",".
            checkNull($facility_shipping['sf_account']).",".
            checkNull($facility_shipping['facility_shipping_name']).",".
            checkNull($facility_shipping['facility_shipping_user']).",".
            checkNull($facility_shipping['facility_shipping_password']).",".
            checkNull($facility_shipping['facility_shipping_site']).",".
            checkNull($facility_shipping['facility_shipping_account']).",".
            checkNull($facility_shipping['logistic_service_id']).",".
            checkNull($facility_shipping['pay_method']).",".
            checkNull($facility_shipping['service_type']).",".
            checkNull($facility_shipping['service_type_code']).",".
            checkNull($facility_shipping['sort']).",".
            checkNull($facility_shipping['cainiao_oauth_id']).",".
            checkNull($facility_shipping['cainiao_branch_code']).",".
            checkNull($facility_shipping['cainiao_branch_name']).",".
            checkNull($facility_shipping['cainiao_branch_address']).",".
            checkNull($facility_shipping['cainiao_template_id']).",".
            checkNull($facility_shipping['pdd_oauth_id']).",".
            checkNull($facility_shipping['pdd_branch_code']).",".
            checkNull($facility_shipping['pdd_branch_name']).",".
            checkNull($facility_shipping['pdd_branch_address']).",".
            checkNull($facility_shipping['pdd_template_id']).",".
            checkNull($facility_shipping['kuaidi_template_id']).",".
            checkNull($facility_shipping['shipping_fee_template_id']).",".
            checkNull($facility_shipping['created_time']).",".
            checkNull($facility_shipping['last_updated_time']).",".
            checkNull($facility_shipping['logistic_service'])."),";
        }
        $insert_sql = substr($insert_sql, 0, -1);
        echo $erp_ddyun_history_db_conf['name']. " move facility_shipping ".count($facility_shipping_list).PHP_EOL;
        $erp_ddyun_history_db->query($insert_sql);
    }
    echo $erp_ddyun_history_db_conf['name']. " move facility_shipping finish".PHP_EOL;

    $limit = 0;
    while(true){
        $select_sql = "select * from shipping_fee_template limit {$limit}, 200";
        $shipping_fee_template_list = $erp_ddyun_db->getAll($select_sql);
        $limit += 200;
        if(empty($shipping_fee_template_list)){
            break;
        }
        $insert_sql = "insert into shipping_fee_template(
             shipping_fee_template_id,
             facility_id,
             template_name,
             template_type,
             is_default,
             created_time,
             last_updated_time) VALUES ";
        foreach ($shipping_fee_template_list as $shipping_fee_template){
            $insert_sql .= "(".
            checkNull($shipping_fee_template['shipping_fee_template_id']).",".
            checkNull($shipping_fee_template['facility_id']).",".
            checkNull($shipping_fee_template['template_name']).",".
            checkNull($shipping_fee_template['template_type']).",".
            checkNull($shipping_fee_template['is_default']).",".
            checkNull($shipping_fee_template['created_time']).",".
            checkNull($shipping_fee_template['last_updated_time'])."),";
        }
        $insert_sql = substr($insert_sql, 0, -1);
        echo $erp_ddyun_history_db_conf['name']. " move shipping_fee_template ".count($shipping_fee_template_list).PHP_EOL;
        $erp_ddyun_history_db->query($insert_sql);
    }

    $limit = 0;
    while (true){
        $select_sql = "select * from shipping_fee_template_detail limit {$limit}, 200";
        $limit += 200;
        $shipping_fee_template_detail_list = $erp_ddyun_db->getAll($select_sql);
        if(empty($shipping_fee_template_detail_list)){
            break;
        }
        $insert_sql = "insert into shipping_fee_template_detail(
            shipping_fee_template_detail_id,
             shipping_fee_template_id,
             facility_id,
             area_name,
             province_id,
             first,
             first_fee,
             first1,
             first_fee1,
             first2,
             first_fee2,
             first3,
             first_fee3,
             first4,
             first_fee4,
             second,
             second_fee,
             second_end,
             second1,
             second_fee1,
             second_end1,
             second2,
             second_fee2,
             second_end2,
             second3,
             second_fee3,
             second_end3,
             second4,
             second_fee4,
             second_end4,
             created_time,
             last_updated_time) VALUES";
        foreach ($shipping_fee_template_detail_list as $shipping_fee_template_detail){
            $insert_sql .= "(".
            checkNull($shipping_fee_template_detail['shipping_fee_template_detail_id']).",".
            checkNull($shipping_fee_template_detail['shipping_fee_template_id']).",".
            checkNull($shipping_fee_template_detail['facility_id']).",".
            checkNull($shipping_fee_template_detail['area_name']).",".
            checkNull($shipping_fee_template_detail['province_id']).",".
            checkNull($shipping_fee_template_detail['first']).",".
            checkNull($shipping_fee_template_detail['first_fee']).",".
            checkNull($shipping_fee_template_detail['first1']).",".
            checkNull($shipping_fee_template_detail['first_fee1']).",".
            checkNull($shipping_fee_template_detail['first2']).",".
            checkNull($shipping_fee_template_detail['first_fee2']).",".
            checkNull($shipping_fee_template_detail['first3']).",".
            checkNull($shipping_fee_template_detail['first_fee3']).",".
            checkNull($shipping_fee_template_detail['first4']).",".
            checkNull($shipping_fee_template_detail['first_fee4']).",".
            checkNull($shipping_fee_template_detail['second']).",".
            checkNull($shipping_fee_template_detail['second_fee']).",".
            checkNull($shipping_fee_template_detail['second_end']).",".
            checkNull($shipping_fee_template_detail['second1']).",".
            checkNull($shipping_fee_template_detail['second_fee1']).",".
            checkNull($shipping_fee_template_detail['second_end1']).",".
            checkNull($shipping_fee_template_detail['second2']).",".
            checkNull($shipping_fee_template_detail['second_fee2']).",".
            checkNull($shipping_fee_template_detail['second_end2']).",".
            checkNull($shipping_fee_template_detail['second3']).",".
            checkNull($shipping_fee_template_detail['second_fee3']).",".
            checkNull($shipping_fee_template_detail['second_end3']).",".
            checkNull($shipping_fee_template_detail['second4']).",".
            checkNull($shipping_fee_template_detail['second_fee4']).",".
            checkNull($shipping_fee_template_detail['second_end4']).",".
            checkNull($shipping_fee_template_detail['created_time']).",".
            checkNull($shipping_fee_template_detail['last_updated_time'])."),";
        }
        $insert_sql = substr($insert_sql, 0, -1);
        echo $erp_ddyun_history_db_conf['name']. " move shipping_fee_template_detail ".count($shipping_fee_template_list).PHP_EOL;
        $erp_ddyun_history_db->query($insert_sql);
    }
}