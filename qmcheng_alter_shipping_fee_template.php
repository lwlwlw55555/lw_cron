<?php
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

for ($i = 0; $i <= 255; $i++) {

    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_history_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);

    $sql = "
        update
            facility_shipping_template fst
            inner join facility_shipping fs on fst.facility_id = fst.facility_id and fst.warehouse_id = fs.warehouse_id and fst.shipping_id = fs.shipping_id
            inner join shipping s on s.shipping_id = fst.shipping_id  
            inner join erpuser.user u on u.facility_id = fst.facility_id            
        set
            fst.facility_shipping_template_name = s.shipping_name,
            fst.thermal_type = 'PDD',
            fst.oauth_id = fs.pdd_oauth_id,
            fst.branch_code = fs.pdd_branch_code,
            fst.branch_name = fs.pdd_branch_name,
            fst.branch_address = fs.pdd_branch_address,
            fst.logistic_service = fs.logistic_service,
            fst.shipping_template_id = fs.pdd_template_id,
            fst.enabled = fs.is_pdd_thermal,
            fst.sort = fs.sort 
        where         
            u.user_env  = 'production'                                              
    ";
    $num = $erp_ddyun_db->query($sql);
    echo $erp_ddyun_db_conf['name']." update facility_shipping_template".$num.PHP_EOL;
    $sql = "
        replace into  
            facility_shipping_fee_template_mapping
            (
                facility_id,
                warehouse_id,
                shipping_id,
                shipping_fee_template_id                
            ) select
                fs.facility_id,
                fs.warehouse_id,
                fs.shipping_id,
                fs.shipping_fee_template_id
            from
                facility_shipping fs
                inner join erpuser.user u on u.facility_id = fs.facility_id  
            where 
                fs.shipping_fee_template_id > 0
                and u.user_env  = 'production'                  
    ";
    $num = $erp_ddyun_db->query($sql);
    echo $erp_ddyun_db_conf['name']." insert facility_shipping_fee_template_mapping".$num.PHP_EOL;


    $select_sql = "select fs.* from facility_shipping_fee_template_mapping fs inner join erpuser.user u on u.facility_id = fs.facility_id   where  u.user_env  = 'production'  ";

    $facility_shipping_list = $erp_ddyun_db->getAll($select_sql);
    if(empty($facility_shipping_list)){
        echo $erp_ddyun_history_db_conf['name']. " move facility_shipping_fee_template_mapping to history ".count($facility_shipping_list).PHP_EOL;
        continue;
    }
    $insert_sql = "replace into facility_shipping_fee_template_mapping(
             id,
             facility_id,
             warehouse_id,
             shipping_id,
             shipping_fee_template_id,            
             created_time,
             last_updated_time             
         ) VALUES ";
    foreach ($facility_shipping_list as $facility_shipping){
        $insert_sql .= "(
            '{$facility_shipping['id']}',
            '{$facility_shipping['facility_id']}',
            '{$facility_shipping['warehouse_id']}',
            '{$facility_shipping['shipping_id']}',
            '{$facility_shipping['shipping_fee_template_id']}',
            '{$facility_shipping['created_time']}',
            '{$facility_shipping['last_updated_time']}' ),";
    }
    $insert_sql = substr($insert_sql, 0, -1);
    $num = $erp_ddyun_history_db->query($insert_sql);
    echo $insert_sql.PHP_EOL;
    echo $erp_ddyun_history_db_conf['name']. " move facility_shipping_fee_template_mapping to history ".$num.PHP_EOL;
    echo "_______________________________________________".PHP_EOL;
}