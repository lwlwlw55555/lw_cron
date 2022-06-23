<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

// global $db;

// $dbs = LeqeeDbService::getStropsDbs('sync');

$dbs = LeqeeDbService::getLeqeeDbs();

// $res = LeqeeDbService::getLeqeeToken();
// var_dump($ss);
// $sql = "show databases";


// foreach ($dbs as $d) {
// 	$res = LeqeeDbService::query($d,"show databases");
// 	var_dump($res);
// }
// die;

// foreach ($ as $key => $value) {
	# code...
// }

$shops = [];
$is_start = true;
$start = 0;
$limit = 30;
while($is_start || $count == 30) {
	$is_start = false;
	$sql = "select * from omssync.shop where is_delete = 'N' limit {$start},{$limit}";
	$results = LeqeeDbService::query($dbs['oms_v2_sync'],$sql);
	if (!empty($results)) {
		$shops = array_merge($shops,$results);
	}
	// var_dump($shops);die;
	$count = count($results);
	// echo $count;die;
	$start += $limit;
}

// 

// var_dump($shops);die;
foreach ($shops as $shop) {
	// var_export($shop);die;

	$sql = "replace into omssync.shop (shop_id, party_id, warehouse_id, shipping_id, platform, shop_platform, shop_name, shop_abbreviation, shop_type, shop_nick, shop_region, shop_brand, shop_group, shop_category_1, shop_butt, currency, payment_type, cooperation_mode, channel_type, main_company, status, is_delete, operation_start_time, project_name, package_category, after_sale_phone, is_auto_cancel, is_reject_custom_order, auto_exchange_conf, is_auto_exchange, is_auto_refund_check, is_auto_shipping_oas, is_auto_return_create, is_auto_return_check, is_auto_modify_taobo_address, modify_taobao_address_conf, is_auto_refund_split, is_gift_recalculate, is_auto_refund_check_first, is_sync_return_status, stock_sync, full_sync, stock_warehouse_ids, stock_ratio, create_user_id, update_user_id, create_time, last_update_time, stock_ajust_number, goods_group_stock_way, transfer_status, order_sync, min_pay_time, min_order_time, shop_web_url, shop_contacter, shop_contacter_phone, shop_contacter_mobile, shop_province, shop_city, shop_area, shop_address, oms_sync_shop_conf_id, licenses_end_time, refund_config, presell_allow, gift_merge_delay, shop_warn_allow, shop_warn_phone, shop_warn_email, shop_price_warn_allow, shop_refund_warn_allow, shop_inventory_warn_allow, shop_platfrom_inventory_warn_allow, shop_prepack_unpack_warn_allow, seller_id, invoice_allow, is_constantly_stock, suning_sale_contract, disable_reason, delivery_address, code, is_disregard_stock, is_provider_cooperation, provider_cooperation_code, supply_chain_channel, open_latest_collection_time, normal_collection_time, presell_collection_time, auto_receipt_day, is_auto_receipt, shop_bd, presale_consign_allow, statistics_platform, business_mode, platform_account, is_supply_delivery_feedback, operation_end_time, shop_order_deliver_warn_allow, order_exchange_modify_goods_allow, gift_merge_condition, lvmh_normal_latest_send_time, lvmh_persell_latest_send_time, lvmh_normal_latest_collect_time, lvmh_persell_latest_collect_time, get_zero_payment, open_order_stock_out_time, normal_order_stock_out_time, presell_order_stock_out_time, stock_out_time_interval, system_type, biz_system, version, belong_branch, management_status, shop_price_change_warn_allow, xhs_fls_supplier_code, xhs_fls_warehouse_codes, normal_cooperative_code, normal_cooperative_name, top_n_gift_open, top_n_gift_delay_time, extend_field, customer_service_division, platform_contracting_company, platform_cooperation_mode, shop_manager, pm, receivable_feedback_bd, bd_director, open_shop_apply_id, customer_service_shop_abbreviation, customer_service_shop_group, customer_service_business_category, customer_service_shop_type, service_type, pre_sale_supervisor_id, after_sale_supervisor_id, party_inventory_organization_id, sync_order_type)
values (".checkNull($shop['shop_id']).", ".checkNull($shop['party_id']).", ".checkNull($shop['warehouse_id']).", ".checkNull($shop['shipping_id']).", ".checkNull($shop['platform']).", ".checkNull($shop['shop_platform']).", ".checkNull($shop['shop_name']).", ".checkNull($shop['shop_abbreviation']).", ".checkNull($shop['shop_type']).", ".checkNull($shop['shop_nick']).", ".checkNull($shop['shop_region']).", ".checkNull($shop['shop_brand']).", ".checkNull($shop['shop_group']).", ".checkNull($shop['shop_category_1']).", ".checkNull($shop['shop_butt']).", ".checkNull($shop['currency']).", ".checkNull($shop['payment_type']).", ".checkNull($shop['cooperation_mode']).", ".checkNull($shop['channel_type']).", ".checkNull($shop['main_company']).", ".checkNull($shop['status']).", ".checkNull($shop['is_delete']).", ".checkNull($shop['operation_start_time']).", ".checkNull($shop['project_name']).", ".checkNull($shop['package_category']).", ".checkNull($shop['after_sale_phone']).", ".checkNull($shop['is_auto_cancel']).", ".checkNull($shop['is_reject_custom_order']).", ".checkNull($shop['auto_exchange_conf']).", ".checkNull($shop['is_auto_exchange']).", ".checkNull($shop['is_auto_refund_check']).", ".checkNull($shop['is_auto_shipping_oas']).", ".checkNull($shop['is_auto_return_create']).", ".checkNull($shop['is_auto_return_check']).", ".checkNull($shop['is_auto_modify_taobo_address']).", ".checkNull($shop['modify_taobao_address_conf']).", ".checkNull($shop['is_auto_refund_split']).", ".checkNull($shop['is_gift_recalculate']).", ".checkNull($shop['is_auto_refund_check_first']).", ".checkNull($shop['is_sync_return_status']).", ".checkNull($shop['stock_sync']).", ".checkNull($shop['full_sync']).", ".checkNull($shop['stock_warehouse_ids']).", ".checkNull($shop['stock_ratio']).", ".checkNull($shop['create_user_id']).", ".checkNull($shop['update_user_id']).", ".checkNull($shop['create_time']).", ".checkNull($shop['last_update_time']).", ".checkNull($shop['stock_ajust_number']).", ".checkNull($shop['goods_group_stock_way']).", ".checkNull($shop['transfer_status']).", ".checkNull($shop['order_sync']).", ".checkNull($shop['min_pay_time']).", ".checkNull($shop['min_order_time']).", ".checkNull($shop['shop_web_url']).", ".checkNull($shop['shop_contacter']).", ".checkNull($shop['shop_contacter_phone']).", ".checkNull($shop['shop_contacter_mobile']).", ".checkNull($shop['shop_province']).", ".checkNull($shop['shop_city']).", ".checkNull($shop['shop_area']).", ".checkNull($shop['shop_address']).", ".checkNull($shop['oms_sync_shop_conf_id']).", ".checkNull($shop['licenses_end_time']).", ".checkNull($shop['refund_config']).", ".checkNull($shop['presell_allow']).", ".checkNull($shop['gift_merge_delay']).", ".checkNull($shop['shop_warn_allow']).", ".checkNull($shop['shop_warn_phone']).", ".checkNull($shop['shop_warn_email']).", ".checkNull($shop['shop_price_warn_allow']).", ".checkNull($shop['shop_refund_warn_allow']).", ".checkNull($shop['shop_inventory_warn_allow']).", ".checkNull($shop['shop_platfrom_inventory_warn_allow']).", ".checkNull($shop['shop_prepack_unpack_warn_allow']).", ".checkNull($shop['seller_id']).", ".checkNull($shop['invoice_allow']).", ".checkNull($shop['is_constantly_stock']).", ".checkNull($shop['suning_sale_contract']).", ".checkNull($shop['disable_reason']).", ".checkNull($shop['delivery_address']).", ".checkNull($shop['code']).", ".checkNull($shop['is_disregard_stock']).", ".checkNull($shop['is_provider_cooperation']).", ".checkNull($shop['provider_cooperation_code']).", ".checkNull($shop['supply_chain_channel']).", ".checkNull($shop['open_latest_collection_time']).", ".checkNull($shop['normal_collection_time']).", ".checkNull($shop['presell_collection_time']).", ".checkNull($shop['auto_receipt_day']).", ".checkNull($shop['is_auto_receipt']).", ".checkNull($shop['shop_bd']).", ".checkNull($shop['presale_consign_allow']).", ".checkNull($shop['statistics_platform']).", ".checkNull($shop['business_mode']).", ".checkNull($shop['platform_account']).", ".checkNull($shop['is_supply_delivery_feedback']).", ".checkNull($shop['operation_end_time']).", ".checkNull($shop['shop_order_deliver_warn_allow']).", ".checkNull($shop['order_exchange_modify_goods_allow']).", ".checkNull($shop['gift_merge_condition']).", ".checkNull($shop['lvmh_normal_latest_send_time']).", ".checkNull($shop['lvmh_persell_latest_send_time']).", ".checkNull($shop['lvmh_normal_latest_collect_time']).", ".checkNull($shop['lvmh_persell_latest_collect_time']).", ".checkNull($shop['get_zero_payment']).", ".checkNull($shop['open_order_stock_out_time']).", ".checkNull($shop['normal_order_stock_out_time']).", ".checkNull($shop['presell_order_stock_out_time']).", ".checkNull($shop['stock_out_time_interval']).", ".checkNull($shop['system_type']).", ".checkNull($shop['biz_system']).", ".checkNull($shop['version']).", ".checkNull($shop['belong_branch']).", ".checkNull($shop['management_status']).", ".checkNull($shop['shop_price_change_warn_allow']).", ".checkNull($shop['xhs_fls_supplier_code']).", ".checkNull($shop['xhs_fls_warehouse_codes']).", ".checkNull($shop['normal_cooperative_code']).", ".checkNull($shop['normal_cooperative_name']).", ".checkNull($shop['top_n_gift_open']).", ".checkNull($shop['top_n_gift_delay_time']).", ".checkNull($shop['extend_field']).", ".checkNull($shop['customer_service_division']).", ".checkNull($shop['platform_contracting_company']).", ".checkNull($shop['platform_cooperation_mode']).", ".checkNull($shop['shop_manager']).", ".checkNull($shop['pm']).", ".checkNull($shop['receivable_feedback_bd']).", ".checkNull($shop['bd_director']).", ".checkNull($shop['open_shop_apply_id']).", ".checkNull($shop['customer_service_shop_abbreviation']).", ".checkNull($shop['customer_service_shop_group']).", ".checkNull($shop['customer_service_business_category']).", ".checkNull($shop['customer_service_shop_type']).", ".checkNull($shop['service_type']).", ".checkNull($shop['pre_sale_supervisor_id']).", ".checkNull($shop['after_sale_supervisor_id']).", ".checkNull($shop['party_inventory_organization_id']).", ".checkNull($shop['sync_order_type']).")";
echo $sql.PHP_EOL;
	global $db;
	$db->query($sql);
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