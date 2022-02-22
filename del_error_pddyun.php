<?php
require("includes/init.php");
$url = 'http://localhost:8080/erp_syncinner_prod';

echo date("Y-m-d H:i:s").PHP_EOL;

global $sync_db,$db;

$facilitys = [];
if (isset($opt_params['mod_index']) || isset($opt_params['facility_id'])) {
    if (isset($opt_params['mod_index'])) {
        $facilitys = $db_user->getCol("
            select 
                distinct u.facility_id
            from 
                user u 
                inner join session_date sd on u.user_id = sd.user_id
            where 
                sd.session_date > date_sub(now(), interval 14 day) and 
                mod(u.facility_id,10) = {$opt_params['mod_index']} and
                u.is_force_manage_goods = 0
        ");
    }else{
        $facilitys = $db->getCol("select facility_id from facility where facility_id = {$opt_params['facility_id']}");
    }
}else{
        $facilitys = $db_user->getCol("
            select 
                distinct u.facility_id
            from 
                user u
                inner join session_date sd on u.user_id = sd.user_id
            where 
                sd.session_date > date_sub(now(), interval 14 day) and 
                u.is_force_manage_goods = 0
        ");
}
foreach ($facilitys as $facility_id) {
    selectRdsByFacilityId($facility_id);
    $sql = "select order_sn from
              multi_goods_shipment where  facility_id = {$facility_id} and last_updated_time >= '2020-04-13' group by order_sn
            having count(1) > 1";
	$orders = $db->getCol($sql);
	var_dump($orders);
    foreach ($orders as $order) {
        $shipment_id = $db->getOne("select shipment_id from order_info where order_sn = {$order}");
        $need_del_shipment_ids = array_diff($db->getCol("select shipment_id from multi_goods_shipment where order_sn = {$order}"), [$shipment_id]);
        foreach ($need_del_shipment_ids as $shipment_id) {
            $sql1 = "delete from shipment where shipment_id = {$shipment_id}";
            $sql2 = "delete from multi_goods_shipment where shipment_id = {$shipment_id}";
            $sql3 = "delete from multi_goods_shipment_goods where original_shipment_id = {$shipment_id}";
            $sql4 = "delete from shipment_package where shipment_id = {$shipment_id}";
            $sql5 = "delete from print_log where shipment_id = {$shipment_id}";
            $sql5 = "delete from inventory_detail_order where shipment_id = {$shipment_id}";
            $package_ids = $db->getCol("select package_id from shipment_package where shipment_id = {$shipment_id}");
            $sql6 = $sql7 = '';
            if (!empty($package_ids)) {
                $sql6 = "delete from package where package_id in (".implode(",", $package_ids).")";
                $sql7 = "delete from mailnos where package_id in (".implode(",", $package_ids).")";
            }
            echo $sql1.' '.$sql2.' '.$sql3.' '.$sql4.' '.$sql5.' '.$sql6.' '.$sql7.PHP_EOL;
            // $db->query($sql1);
            // $db->query($sql2);
            // $db->query($sql3);
            // $db->query($sql4);
            // $db->query($sql5);
            // $db->query($sql6);
            // $db->query($sql7);
        }
    }
}



