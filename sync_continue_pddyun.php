<?php
require("includes/init.php");

global $db,$db_conf;
// global $db_conf,$erp_ddyun_db_conf;
// $db_conf = array(
//         "host" => "100.65.1.0:32053",
//         "name" => "erp_test",
//         "user" => "erp_test",
//         "pass" => "zgsdi67f8PhQ",
//         "charset" => "utf8",
//         "pconnect" => "1",
// );

global $abandon_tables;
$abandon_tables = ['facility_template_back',
'finance_bill_goods_back',
'finance_bill_order_back',
'jwang_erp_report',
'jwang_temp_facility',
'jwang_temp_finance',
'jwang_temp_flag',
'jwang_temp_id',
'jwang_temp_tacits',
'pay_goods_back_20191028_jwang',
'pay_oauth_copy_20191019_jwang',
'pay_oauth_jwang',
'pay_order_back_20191029_jwang',
'pay_order_back_20191030_jwang',
'pay_order_jwang',
'pdd_return_order',
'region_broadcast',
'shipping_template_copy1',
'shipping_template_copy2',
'shop_back_dddd_appkey_jwang',
'user_copy',
'ddyun_back_admin_menu',
'ddyun_back_admin_permission',
'ddyun_back_admin_role',
'ddyun_back_admin_session',
'ddyun_back_admin_user',
'ddyun_back_banner',
'ddyun_back_child_user',
'ddyun_back_cronjob_report',
'ddyun_back_datasource_send_time',
'ddyun_back_export_center',
'ddyun_back_failed_jobs',
'ddyun_back_finance_update_log',
'ddyun_back_ignore_action_history',
'ddyun_back_import_center',
'ddyun_back_inventory_change_job',
'ddyun_back_last_sync_time',
'ddyun_back_marquee',
'ddyun_back_menu',
'ddyun_back_notice',
'ddyun_back_oauth_share',
'ddyun_back_oauth_share_history',
'ddyun_back_outer_oauth',
'ddyun_back_party',
'ddyun_back_pay_goods',
'ddyun_back_pay_oauth',
'ddyun_back_pay_oauth_update_log',
'ddyun_back_pay_order',
'ddyun_back_pay_transaction',
'ddyun_back_pdd_white_shop',
'ddyun_back_permission',
'ddyun_back_platform_goods_onsale_job',
'ddyun_back_platform_pay_order',
'ddyun_back_role',
'ddyun_back_role_permission',
'ddyun_back_session',
'ddyun_back_session_date',
'ddyun_back_shipping_bill',
'ddyun_back_shipping_logistic_service',
'ddyun_back_shop_data',
'ddyun_back_shop_data_region',
'ddyun_back_system_feedback',
'ddyun_back_system_function',
'ddyun_back_system_help',
'ddyun_back_system_help_version_diff',
'ddyun_back_system_help_video',
'ddyun_back_system_promotion',
'ddyun_back_system_version_log',
'ddyun_back_taobao_vas_orders',
'ddyun_back_user',
'ddyun_back_user_role',
'ddyun_back_ws_queue_failed_jobs',
'ddyun_delete_back_facility_template_back',
'ddyun_delete_back_finance_bill_goods_back',
'ddyun_delete_back_finance_bill_order_back',
'ddyun_delete_back_jwang_erp_report',
'ddyun_delete_back_jwang_temp_facility',
'ddyun_delete_back_jwang_temp_finance',
'ddyun_delete_back_jwang_temp_flag',
'ddyun_delete_back_jwang_temp_id',
'ddyun_delete_back_jwang_temp_tacits',
'ddyun_delete_back_pay_goods_back_20191028_jwang',
'ddyun_delete_back_pay_oauth_copy_20191019_jwang',
'ddyun_delete_back_pay_oauth_jwang',
'ddyun_delete_back_pay_order_back_20191029_jwang',
'ddyun_delete_back_pay_order_back_20191030_jwang',
'ddyun_delete_back_pay_order_jwang',
'ddyun_delete_back_pdd_return_order',
'ddyun_delete_back_region_broadcast',
'ddyun_delete_back_shipping_template_copy1',
'ddyun_delete_back_shipping_template_copy2',
'ddyun_delete_back_shop_back_dddd_appkey_jwang',
'ddyun_delete_back_user_copy'
];

$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);


$facility_ids = [];
if (empty($argv[1])) {
    $facility_ids = get_facility_ids();
} else {
    $facility_ids[] = $argv[1];
}

$number = 0;
if (!empty($argv[2])) {
    $number = $argv[2];
}

foreach ($facility_ids as $facility_id) {
$sync_facility = $db->getRow("select * from sync_facilitys where facility_id = {$facility_id}");
if(empty($sync_facility) || $sync_facility['sync_status'] != "DOING") {
    echo date("Y-m-d H:i:s")." facility_id:{$facility_id} sync_status:{$sync_facility['sync_status']} continue ".PHP_EOL;
    continue;
}
    // $db->query("delete from sync_facilitys where facility_id = {$facility_id}");


$erp_ddyun_db_conf['name'] = $sync_facility['sync_rds'];
echo date("Y-m-d H:i:s")." facility_id:{$facility_id} db:{$erp_ddyun_db_conf['name']} sync_continue".PHP_EOL.PHP_EOL.PHP_EOL;

// if ($sync_status = $db->getOne("select sync_status from sync_facilitys where facility_id = {$facility_id}")) {
//     if($sync_status == "DONE") {
//         echo date("Y-m-d H:i:s")." facility_id:{$facility_id} sync_status:{$sync_status} continue ".PHP_EOL;
//         continue;
//     }
//     $db->query("delete from sync_facilitys where facility_id = {$facility_id}");
// }
// $sql1 = "insert into sync_facilitys (facility_id,sync_rds) VALUES ({$facility_id},'{$erp_ddyun_db_conf['name']}')";
// $db->query($sql1);
// echo date("Y-m-d H:i:s")." facility_id:{$facility_id} db:{$erp_ddyun_db_conf['name']} sync_continue {$sql1}".PHP_EOL;
$sql_get_0 = "select s.shop_id from shop s 
        inner join shop_extension se on s.shop_id = se.shop_id 
        where 
        s.default_facility_id = {$facility_id} and 
        se.is_big_shop in (5)";
$get_0 = $sync_db->getCol($sql_get_0);
echo date("Y-m-d H:i:s")." facility_id:{$facility_id} sql_get_0:{$sql_get_0} ".json_encode($get_0).PHP_EOL.PHP_EOL.PHP_EOL;   

if (!empty($get_0)) {
    $sql = "
        update 
            shop_extension
        set 
            is_big_shop = 0,shop_mod = mod(shop_id,100)
        where 
            shop_id in (".implode(",", $get_0).")
    ";
    echo date("Y-m-d H:i:s")." facility_id:{$facility_id} {$sql}".PHP_EOL.PHP_EOL.PHP_EOL;
    $sync_db->query($sql);
}


$sql_get_1 = "select s.shop_id from shop s 
        inner join shop_extension se on s.shop_id = se.shop_id 
        where 
        s.default_facility_id = {$facility_id} and 
        se.is_big_shop in (0 ,1)";
$get_1 = $sync_db->getCol($sql_get_1);
echo date("Y-m-d H:i:s")." facility_id:{$facility_id} sql_get_1:{$sql_get_1} ".json_encode($get_1).PHP_EOL.PHP_EOL.PHP_EOL;   

if (!empty($get_1)) {
    $sql = "
        update 
            shop_extension
        set 
            is_big_shop = 41,shop_mod = mod(shop_id,100)
        where 
            shop_id in (".implode(",", $get_1).")
    ";
    echo date("Y-m-d H:i:s")." facility_id:{$facility_id} {$sql}".PHP_EOL.PHP_EOL.PHP_EOL;
    $sync_db->query($sql);
}

$sql_get_2 = "select s.shop_id from shop s 
        inner join shop_extension se on s.shop_id = se.shop_id 
        where 
        s.default_facility_id = {$facility_id} and 
        se.is_big_shop in (2)";
$get_2 = $sync_db->getCol($sql_get_2);
echo date("Y-m-d H:i:s")." facility_id:{$facility_id} sql_get_2:{$sql_get_2} ".json_encode($get_2).PHP_EOL.PHP_EOL.PHP_EOL;   

if (!empty($get_2)) {
    $sql = "
        update 
            shop_extension
        set 
            is_big_shop = 42,shop_mod = mod(shop_id,100)
        where 
            shop_id in (".implode(",", $get_2).")
    ";
    echo date("Y-m-d H:i:s")." facility_id:{$facility_id} {$sql}".PHP_EOL.PHP_EOL.PHP_EOL;
    $sync_db->query($sql);
}

$sql = "
    select 
        count(1) 
    from 
        shop s 
        inner join shop_extension se on s.shop_id = se.shop_id 
    where   
        s.default_facility_id = {$facility_id} and 
        se.enabled = 1 and 
        (
            (se.is_big_shop >= 35 and se.is_big_shop < 50 )
        )
";
do {
    $syncing_count = $sync_db->getOne($sql);
    if (! $syncing_count) {
        echo date("Y-m-d H:i:s")." facility_id:{$facility_id} syncing_count:{$syncing_count} ready move ".PHP_EOL;
        break;
    }
    echo date("Y-m-d H:i:s")." facility_id:{$facility_id} syncing_count:{$syncing_count} sleep ... ".PHP_EOL;
    sleep(10);
} while(1);


$erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

$column_map = array();
$column_map['sync'] = array();
// insert data by facility
$now_table = $sync_facility['now_table'];
moveDataByFacility($facility_id,$now_table,$number);
}

function moveDataByFacility($facility_id,$now_table=null,$number=0){
    $tables = ['warehouse','facility','facility_address','facility_best_shipping_goods','facility_best_shipping_goods_history','facility_best_shipping_region',
        'facility_best_shipping_region_history','facility_bill_template','facility_oauth','facility_shipment_tactics','facility_shipping',
    'facility_shipping_template','finance_setting','finance_bill_goods','finance_bill_goods_back','finance_bill_order','finance_bill_order_back','finance_estimate_shipping_fee',
    'gift_tactics','gift_tactics_detail','goods','goods_back','goods_mapping','goods_mapping_history','group_sku_mapping','inventory','inventory_detail','inventory_detail_order','inventory_import_history',
    'inventory_location_setting','mailnos','manu_order','manu_order_package','manu_task','multi_goods_shipment','multi_goods_shipment_goods','oauth',
    'order_action','order_goods','order_info','package','pdd_refund','platform_goods','platform_sku','print_log','region_key','region_lable','shipment','shipment_exception_flag',
    'shipment_package','shipping_fee_template','shipping_fee_template_detail','shop','shop_back','sku','sku_back','sku_mapping','sku_mapping_history','task','oauth_share_mailnos','facility_shipment_flag'];
    global $abandon_tables;
    $tables = array_diff($tables, $abandon_tables);
    
    global $db,$erp_ddyun_db;
    global $db_conf,$erp_ddyun_db_conf;
    $count = -1;
    echo "[insert into ddyun, facility_id: {$facility_id}]" .date("Y-m-d H:i:s"). " sync facility start" .PHP_EOL;
    $is_continue = true;
    foreach ($tables as $table){
        if ((!empty($now_table) && $now_table == $table)) {
            $is_continue = false;
        }
        if ($is_continue) {
            continue;
        }
        if (!empty($number)) {
            $count = $count+1;
            echo 'count:'.$count.' number'.$number.PHP_EOL;
            if ($count < $number) {
                continue;
            }   
            if ($count > $number) {
                break;
            }
            
	}
	if ($table == 'facility_shipment_flag') {
		            $sql3 = "update sync_facilitys set now_table = '{$table}' where facility_id = {$facility_id}";
			                $db->query($sql3);
			                echo date("Y-m-d H:i:s")." facility_id:{$facility_id} db:{$erp_ddyun_db_conf['name']} sync_continue {$sql3}".PHP_EOL;
					        }
        // if ($table == '') {
        //     # code...
        // }
        // $sql3 = "update sync_facilitys set now_table = '{$table}' where facility_id = {$facility_id}";
        // $db->query($sql3);
        // echo date("Y-m-d H:i:s")." facility_id:{$facility_id} db:{$erp_ddyun_db_conf['name']} sync_continue {$sql3}".PHP_EOL;

        echo date("Y-m-d H:i:s")." facility_id:{$facility_id} db:{$erp_ddyun_db_conf['name']} table {$table} begin".PHP_EOL;

        
        moveData($facility_id, $table, $db, $erp_ddyun_db, $db_conf, $erp_ddyun_db_conf, 'sync');
        $now_table = '';
    }

    // echo "[insert into ddyun, facility_id: {$facility_id}]" .date("Y-m-d H:i:s"). " sync facility start" .PHP_EOL;
    // $sql4 = "update sync_facilitys set sync_status = 'DONE',end_sync_time=now() where facility_id = {$facility_id}";
    // $db->query($sql4);
    // echo date("Y-m-d H:i:s")." facility_id:{$facility_id} db:{$erp_ddyun_db_conf['name']} sync_continue {$sql4}".PHP_EOL;

    echo "[insert into ddyun, facility_id: {$facility_id}]" .date("Y-m-d H:i:s"). " sync facility {$table} end" .PHP_EOL;

}

function moveData($facility_id, $table, $from_db, $to_db, $from_conf, $to_conf, $type){
    global  $column_map;
    $order_by_key_map = [
        'warehouse'         => 'warehouse_id',
        'facility'          => 'facility_id',
        'facility_address'  => 'facility_address_id',
        'facility_best_shipping_goods' => 'facility_best_shipping_goods_id',
        'facility_best_shipping_goods_history' => 'facility_best_shipping_goods_history_id',
        'facility_best_shipping_region' => 'facility_best_shipping_region_id',
        'facility_best_shipping_region_history' => 'facility_best_shipping_region_history_id',
        'facility_bill_template' => 'facility_id',
        'facility_oauth'    => 'facility_oauth_id',
        'facility_shipment_tactics' => 'facility_shipment_tactics_id',
        'facility_shipping' => 'facility_shipping_id',
        'facility_shipping_template' => 'id',
        'facility_template' => 'facility_id',
        'finance_bill_goods'=> 'finance_bill_goods_id',
        'finance_bill_goods_back' => 'finance_bill_goods_id',
        'finance_bill_order'=> 'finance_bill_order_id',
        'finance_bill_order_back' => 'finance_bill_order_id',
        'finance_estimate_shipping_fee' => 'finance_estimate_shipping_fee_id',
        'finance_setting'   => 'finance_setting_id',
        'gift_tactics'      => 'gift_tactics_id',
        'gift_tactics_detail' => 'gift_tactics_detail_id',
        'goods'             => 'goods_id',
        'goods_back'        => 'goods_id',
        'goods_mapping'     => 'goods_mapping_id',
        'goods_mapping_history' => 'goods_mapping_history_id',
        'group_sku_mapping' => 'group_sku_mapping_id',
        'inventory'         => 'inventory_id',
        'inventory_detail'  => 'inventory_detail_id',
        'inventory_detail_order' => 'inventory_detail_order_id',
        'inventory_import_history' => 'inventory_import_history_id',
        'inventory_location_setting' => 'id',
        'mailnos'           => 'id',
        'manu_order'        => 'manu_order_id',
        'manu_order_package' => 'id',
        'manu_task'         => 'task_id',
        'multi_goods_shipment' => 'shipment_id',
        'multi_goods_shipment_goods' => 'order_goods_id',
        'oauth'             => 'oauth_id',
        'order_action'      => 'order_action_id',
        'order_goods'       => 'order_goods_id',
        'order_info'        => 'order_id',
        'package'           => 'package_id',
        'pdd_refund'        => 'id',
        'platform_goods'    => 'id',
        'platform_sku'      => 'id',
        'print_log'         => 'id',
        'region_key'        => 'region_key_id',
        'region_lable'      => 'region_lable_id',
        'shipment'          => 'shipment_id',
        'shipment_exception_flag' => 'id',
        'shipment_package'  => 'id',
        'shipping_fee_template' => 'shipping_fee_template_id',
        'shipping_fee_template_detail' => 'shipping_fee_template_detail_id',
        'shop'              => 'shop_id',
        'shop_back'         => 'shop_id',
        'sku'               => 'sku_id',
        'sku_back'          => 'sku_id',
        'sku_mapping'       => 'sku_mapping_id',
        'sku_mapping_history' => 'sku_mapping_history_id',
        'task'              => 'task_id',
        'oauth_share_mailnos' => 'oauth_share_mailnos_id',
        'facility_shipment_flag' => 'facility_shipment_flag_id'
    ];
    $columns = isset($column_map[$type][$table])?$column_map[$type][$table]:array();
    if(empty($columns)){
        $columns_infos = execRetry($from_db, $from_conf, "show COLUMNS from {$table}", 'getAll');
        foreach ($columns_infos as $columns_info) {
            $column = $columns_info['Field'];
            $columns[] = $column;
        }
        $column_map[$type][$table] = $columns;
    }
    $sql_columns = implode('`,`',$columns);
    $start = 0;
    $limit = 5000;
    if( $table == 'print_log'){
        $limit = 100;
    }
    if( $table == 'mailnos'){
        $limit = 500;
    }
    while (true){
        if($table == 'shop' || $table == 'shop_back'){
            $sql = "select `{$sql_columns}` from {$table} where default_facility_id = {$facility_id} and {$order_by_key_map[$table]} >= {$start} order by {$order_by_key_map[$table]} limit {$limit}";
        }else {
            $sql = "select `{$sql_columns}` from {$table} where facility_id = {$facility_id} and {$order_by_key_map[$table]} >= {$start} order by {$order_by_key_map[$table]} limit {$limit}";
        }
        $values = execRetry($from_db, $from_conf, $sql, 'getAll');
        $insert_sql_head = "insert ignore into {$table} (`".implode('`,`',$columns)."`) values";
        $insert_sql_body = "";
        if(!empty($values) && count($values) > 0){
            foreach ($values as $value){
                $insert_sql_body .= " (";
                foreach ($columns as $column){
                    if(is_string($value[$column])) {
                        $insert_sql_body .= "'".addslashes($value[$column])."',";
                    }else{
                        if(empty($value[$column])){
                            $value[$column] = 'null';
                        }
                        $insert_sql_body .= $value[$column].",";
                    }
                }
                $insert_sql_body = substr($insert_sql_body, 0, strlen($insert_sql_body) -1);
                $insert_sql_body .= "),";
            }
            $insert_sql_body = substr($insert_sql_body, 0,strlen($insert_sql_body)-1);
            $insert_sql = $insert_sql_head.$insert_sql_body.";";
            execRetry($to_db, $to_conf, $insert_sql, 'query', $insert_sql_head);
            $start = $values[count($values) - 1][$order_by_key_map[$table]];
        }else{
            break;
        }
        if(count($values) < $limit){
            break;
        }
    }
}

function execRetry($db, $db_conf, $sql, $type, $log_sql = null){
    global $facility_id;
    $start_time = microtime(true);
    $result = null;
    if (! $log_sql) {
        $log_sql = $sql;
    }
    try{
        switch ($type){
	case 'query':
	    echo 'query_lw'.PHP_EOL;	
            $db = ClsPdo::getInstanceRetry($db_conf);
                $result = $db->query($sql);
                break;
            case 'getAll':
                $result = $db->getAll($sql);
                break;
            case 'getOne':
                $result = $db->getOne($sql);
                break;
            case 'getCol':
                $result = $db->getCol($sql);
                break;
        }
        echo "[insert data to ddyun, facility_id :{$facility_id}] cost: ". ((microtime(true) - $start_time )* 1000) . "ms, " . date("Y-m-d H:i:s"). $log_sql .PHP_EOL;
        if(!empty($result)){
            return $result;
        }
    }catch (Exception $e){
        sleep(2);
        try {
            $db = ClsPdo::getInstanceRetry($db_conf);
            switch ($type) {
                case 'query':
                    $db->query($sql);
                    break;
                case 'getAll':
                    $result =  $db->getAll($sql);
                    break;
                case 'getOne':
                    $result = $db->getOne($sql);
                    break;
                case 'getCol':
                    $result = $db->getCol($sql);
                    break;
            }
            echo "[insert data to ddyun, facility_id: {$facility_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry success try1 " . $e->getMessage() . $log_sql .  PHP_EOL;
            if (!empty($result)) {
                return $result;
            }
        }catch (Exception $e2){
            echo "[insert data to ddyun, facility_id: {$facility_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try1 " . $e->getMessage() . $log_sql .  PHP_EOL;
            echo "[insert data to ddyun, facility_id: {$facility_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try2 " . $e2->getMessage() . $log_sql .  PHP_EOL;
        }
    }
}


function get_facility_ids() {
    global $db_user;
    $sql = "
        select 
            distinct u.facility_id
        from 
            user u 
            inner join pay_oauth po on u.user_id = po.user_id 
        where 
            po.expire_time < '2020'
    ";
    return $db_user->getCol($sql);
}
