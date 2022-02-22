<?php
require("includes/init.php");

$sss = [
    'order_info',
'multi_goods_shipment',
'multi_goods_shipment_goods',
'finance_detail',
'shipment',
'order_goods',
'inventory_detail',
'sku',
'finance_bill_goods',
'inventory',
'mailnos',
'shipment_package',
'package',
'inventory_detail_order',
'print_log',
'finance_bill_order',
'sku_mapping',
'platform_sku',
'sku_back',
'order_action',
'task',
'region',
'region_mapping',
'finance_estimate_shipping_fee',
'goods',
'goods_mapping',
'platform_goods',
'sku_mapping_history',
'sync_platform_shipping_mapping',
'shipping',
'goods_back',
'oauth',
'facility_best_shipping_region',
'shop',
'shipment_exception_flag',
'shipping_fee_template_detail',
'shop_back',
'shipping_template',
'facility_shipping_template',
'facility_shipping_back_20200422',
'facility_shipping',
'warehouse',
'facility',
'facility_bill_template',
'facility_address',
'facility_shipment_flag',
'goods_mapping_history',
'facility_best_shipping_goods_history',
'region_lable',
'shipping_fee_template',
'app',
'config',
'group_sku_mapping',
'finance_setting',
'facility_shiping_back_20200422',
'facility_oauth',
'manu_task',
'manu_order_package',
'facility_copy1',
'pdd_refund',
'manu_order',
'facility_best_shipping_region_history',
'finance_ad_fee_setting',
'inventory_location_setting',
'inventory_import_history',
'facility_best_shipping_goods',
'oauth_share_mailnos',
'gift_tactics_detail',
'region_key',
'facility_shipment_tactics',
'gift_tactics'
];
//$sss = array_reverse($sss);
foreach ($sss as $s) {
    $argv[1] = $s;
for($i = 0; $i < 256; $i++){
    if (date('H') >= 8 && date('H') < 18) {
        die("hour:8 die\n");
    }
    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );

    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $sql = "optimize table {$argv[1]}";

    global $db_user;
    $optimize_table_list = $db_user->getAll("select db, table_name from optimize_table where table_name = '{$argv[1]}' and db = '{$erp_ddyun_db_conf['name']}'");

    $is_optimize = count($optimize_table_list) > 0 ? true : false;

    if(!$is_optimize){
        echo date("Y-m-d H:i:s"). "erp_{$i} sql:{$sql}\n";
        $erp_ddyun_db->query($sql);

        $insert_sql = "insert into optimize_table (db, table_name) values('{$erp_ddyun_db_conf['name']}', '{$argv[1]}')";
        $db_user->query($insert_sql);
    }
}
}