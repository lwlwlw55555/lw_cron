<?php

require("includes/init.php");

global $erp_prod_db,$erp_aliyun_db;
global $erp_prod_db_conf,$erp_aliyun_db_conf;

$facility_id = $argv[1];

$column_map = array();
$column_map['sync'] = array();
// insert data by facility
$move_facility_id = moveDataByFacility($facility_id);

function moveDataByFacility($facility_id){
    $tables = ['facility','facility_address','facility_best_shipping_goods','facility_best_shipping_goods_history','facility_best_shipping_region',
        'facility_best_shipping_region_history','facility_bill_template','facility_oauth','facility_shipment_tactics','facility_shipping',
    'facility_shipping_template','facility_template','finance_setting','finance_bill_goods','finance_bill_goods_back','finance_bill_order','finance_bill_order_back','finance_estimate_shipping_fee',
    'gift_tactics','gift_tactics_detail','goods','goods_back','goods_mapping','goods_mapping_history','group_sku_mapping','inventory','inventory_detail','inventory_detail_order','inventory_import_history',
    'inventory_location_setting','mailnos','manu_order','manu_order_package','manu_task','multi_goods_shipment','multi_goods_shipment_goods','oauth',
    'order_action','order_goods','order_info','package','pdd_refund','platform_goods','platform_sku','print_log','region_key','region_lable','shipment','shipment_exception_flag',
    'shipment_package','shipping_fee_template','shipping_fee_template_detail','shop','shop_back','sku','sku_back','sku_mapping','sku_mapping_history','task'];

    global $erp_prod_db,$erp_aliyun_db;
    global $erp_prod_db_conf,$erp_aliyun_db_conf;

    echo "[insert into aliyun, facility_id: {$facility_id}]" .date("Y-m-d H:i:s"). " sync facility start" .PHP_EOL;
    foreach ($tables as $table){
        moveData($facility_id, $table, $erp_prod_db, $erp_aliyun_db, $erp_prod_db_conf, $erp_aliyun_db_conf, 'sync');
    }
    echo "[insert into aliyun, facility_id: {$facility_id}]" .date("Y-m-d H:i:s"). " sync facility end" .PHP_EOL;

}

function moveData($facility_id, $table, &$from_db, $to_db, $from_conf, $to_conf, $type){
    global  $column_map;
    $order_by_key_map = [
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
        'task'              => 'task_id'
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
    $limit = 1000;
    if( $table == 'print_log'){
        $limit = 100;
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
                    if(is_string($values[$column])) {
                        $insert_sql_body .= "'".addslashes($value[$column])."',";
                    }else{
                        if(empty($value[$column])){
                            $value[$column] = 'null';
                        }
                        $insert_sql_body .= $values[$column].",";
                    }
                }
                $insert_sql_body = substr($insert_sql_body, 0, strlen($insert_sql_body) -1);
                $insert_sql_body .= "),";
            }
            $insert_sql_body = substr($insert_sql_body, 0,strlen($insert_sql_body)-1);
            $insert_sql = $insert_sql_head.$insert_sql_body.";";
            execRetry($to_db, $to_conf, $insert_sql, 'query');
            $start = $values[count($values) - 1][$order_by_key_map[$table]];
        }else{
            break;
        }
        if(count($values) < $limit){
            break;
        }
    }
}

function execRetry($db, $db_conf, $sql, $type){
    global $facility_id;
    $start_time = microtime(true);
    $result = null;
    try{
        switch ($type){
            case 'query':
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
        echo "[insert data to aliyun, facility_id :{$facility_id}] cost: ". ((microtime(true) - $start_time )* 1000) . "ms, " . date("Y-m-d H:i:s"). $sql .PHP_EOL;
        if(!empty($result)){
            return $result;
        }
    }catch (Exception $e){
        sleep(30);
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
            echo "[insert data to aliyun, facility_id: {$facility_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry success try1 " . $e->getMessage() . $sql .  PHP_EOL;
            if (!empty($result)) {
                return $result;
            }
        }catch (Exception $e2){
            echo "[insert data to aliyun, facility_id: {$facility_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try1 " . $e->getMessage() . $sql .  PHP_EOL;
            echo "[insert data to aliyun, facility_id: {$facility_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try2 " . $e2->getMessage() . $sql .  PHP_EOL;
        }
    }
}