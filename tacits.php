<?php
require("includes/init.php");
global $db,$db_user;


$sql_select = "
    select 
        distinct w.warehouse_id, f.facility_id
    from 
        facility f 
        inner join warehouse w on f.facility_id = w.facility_id and w.warehouse_name ='默认仓库' and f.default_warehouse_id = w.warehouse_id
        inner join facility_shipment_tactics fst on fst.facility_id = f.facility_id 
        left join jwang_temp_tacits t on f.facility_id = t.facility_id 
    where 
        t.facility_id is null 
";
$result_id = $db->getAll($sql_select);
echo count($result_id)." 个warehouse";
$color = array('#e4f7d2', '#f7e9bc', '#c4e4f5', '#e8e8e8', '#fbd3d0', '#e8d5cb', '#c0ebe5', '#dbcef5');
foreach ($result_id as $key =>$item){
    echo PHP_EOL .PHP_EOL .$key ."开始";
    $sql_tactics = "SELECT * FROM facility_shipment_tactics  where facility_id =  {$item['facility_id']}";
    echo date("Y-m-d H:i:s") . " {$key} ".  $sql_tactics . "\n";
    $tactics = $db->getAll($sql_tactics);
    foreach ($tactics as $tactic_key => $tactic){
        $child_sql = "SELECT  child_user_id  FROM child_user  where  tactics_id = {$tactic['facility_shipment_tactics_id']}";
        echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $child_sql . "\n";$child_tactic = $db_user->getOne($child_sql);
        if($child_tactic){
            //一个策略一个仓
            $insert_sql = "INSERT INTO warehouse (`facility_id`,`warehouse_name`, `province_id`, `province_name`, `city_id`,`city_name`, `district_id`,
            `district_name`, `shipping_address`,`postcode`, `sender_mobile`, `sender_name`, `sender_company`,
            `best_shipping`, `best_shipping_refresh_time`) 
            SELECT `facility_id`,'{$tactic['facility_shipment_tactics_name']}', `province_id`, `province_name`, `city_id`,`city_name`, `district_id`, 
            `district_name`, `shipping_address`,`postcode`, `sender_mobile`, `sender_name`, `sender_company`,
            `best_shipping`, `best_shipping_refresh_time` FROM facility where facility_id = {$item['facility_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $insert_sql . "\n";$result = $db->query($insert_sql);

            //获得新建的仓库id
            $warehouse_id = $db->getOne("select last_insert_id()");
            //复制仓库地址
            $insert_sql = "INSERT INTO facility_address (`facility_id`,`warehouse_id`, `province_id`, `province_name`, `city_id`,`city_name`, `district_id`, 
            `district_name`, `shipping_address`,`postcode`, `sender_mobile`, `sender_name`, `sender_company`,`is_default` ) 
            SELECT `facility_id`,{$warehouse_id}, `province_id`, `province_name`, `city_id`,`city_name`, `district_id`, 
            `district_name`, `shipping_address`,`postcode`, `sender_mobile`, `sender_name`, `sender_company`,`is_default` 
            FROM facility_address where facility_id = {$item['facility_id']} and warehouse_id = {$item['warehouse_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $insert_sql . "\n";$result = $db->query($insert_sql);
            //复制facility_bill_template
            $insert_sql = "INSERT INTO facility_bill_template (`facility_id`, `warehouse_id`, `is_show_order_sn`, `is_show_confirm_time`, `is_show_seller_note`, `is_show_buyer_note`, `is_show_receive_name`, `is_show_mobile`, `is_show_address`, `is_show_goods_name`, `is_show_style_value`, `is_show_goods_price`, `is_show_goods_number`, `is_show_sender_mobile`, `is_show_facility_address`, `normal_front_size`, `is_show_shop_name`) 
            SELECT `facility_id`, {$warehouse_id}, `is_show_order_sn`, `is_show_confirm_time`, `is_show_seller_note`, `is_show_buyer_note`, `is_show_receive_name`, `is_show_mobile`, `is_show_address`, `is_show_goods_name`, `is_show_style_value`, `is_show_goods_price`, `is_show_goods_number`, `is_show_sender_mobile`, `is_show_facility_address`, `normal_front_size`,  `is_show_shop_name` 
            FROM facility_bill_template where facility_id = {$item['facility_id']} and warehouse_id = {$item['warehouse_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} " ."-"."{$tactic_key}".  $insert_sql . "\n";$result = $db->query($insert_sql);

            //复制快递
            $insert_sql = "INSERT INTO facility_shipping (`facility_id`, `warehouse_id`, `shipping_id`, `is_cainiao_thermal`, `is_pdd_thermal`, `is_kuaidi_thermal`, `default_thermal_type`, `enable`, `send_addr_code`, `sf_account`, `facility_shipping_name`, `facility_shipping_user`, `facility_shipping_password`, `facility_shipping_site`, `facility_shipping_account`, `logistic_service_id`, `pay_method`, `service_type`, `service_type_code`, `sort`, `cainiao_oauth_id`, `cainiao_branch_code`, `cainiao_branch_name`, `cainiao_branch_address`, `cainiao_template_id`, `pdd_oauth_id`, `pdd_branch_code`, `pdd_branch_name`, `pdd_branch_address`, `pdd_template_id`, `kuaidi_template_id`, `shipping_fee_template_id`, `logistic_service`) 
            SELECT `facility_id`, {$warehouse_id}, `shipping_id`, `is_cainiao_thermal`, `is_pdd_thermal`, `is_kuaidi_thermal`, `default_thermal_type`, `enable`, `send_addr_code`, `sf_account`, `facility_shipping_name`, `facility_shipping_user`, `facility_shipping_password`, `facility_shipping_site`, `facility_shipping_account`, `logistic_service_id`, `pay_method`, `service_type`, `service_type_code`, `sort`, `cainiao_oauth_id`, `cainiao_branch_code`, `cainiao_branch_name`, `cainiao_branch_address`, `cainiao_template_id`, `pdd_oauth_id`, `pdd_branch_code`, `pdd_branch_name`, `pdd_branch_address`, `pdd_template_id`, `kuaidi_template_id`, `shipping_fee_template_id`, `logistic_service`
            FROM facility_shipping where facility_id = {$item['facility_id']} and warehouse_id = {$item['warehouse_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $insert_sql . "\n";$result = $db->query($insert_sql);

            //复制快递面单模板
            $insert_sql = "INSERT INTO facility_shipping_template (`facility_id`, `warehouse_id`, `shipping_id`, `custom_locale_start_point`, `custom_locale_fontsize`, `custom_locale_text`, `custom_locale_start_point2`, `custom_locale_fontsize2`, `custom_locale_text2`, `is_show_goods_name`, `is_show_outer_id`, `is_show_goods_alias`, `is_show_style_value`, `is_show_sku_name`, `is_show_goods_number`, `is_show_buyer_note`, `is_show_seller_note`, `is_show_order_sn`, `is_show_goods_amount`, `is_show_pay_amount`, `is_show_weight`, `is_split_group_goods`, `is_show_confirm_time`, `is_show_shop_name`, `is_show_qrcode`, `is_show_multi_goods`, `is_show_location`, `is_show_top_logo`, `is_show_bottom_logo`, `mark`, `front_size`, `hor_offset`, `ver_offset`) 
            SELECT `facility_id`, {$warehouse_id}, `shipping_id`, `custom_locale_start_point`, `custom_locale_fontsize`, `custom_locale_text`, `custom_locale_start_point2`, `custom_locale_fontsize2`, `custom_locale_text2`, `is_show_goods_name`, `is_show_outer_id`, `is_show_goods_alias`, `is_show_style_value`, `is_show_sku_name`, `is_show_goods_number`, `is_show_buyer_note`, `is_show_seller_note`, `is_show_order_sn`, `is_show_goods_amount`, `is_show_pay_amount`, `is_show_weight`, `is_split_group_goods`, `is_show_confirm_time`, `is_show_shop_name`, `is_show_qrcode`, `is_show_multi_goods`, `is_show_location`, `is_show_top_logo`, `is_show_bottom_logo`, `mark`, `front_size`, `hor_offset`, `ver_offset`
            FROM facility_shipping_template where facility_id = {$item['facility_id']} and warehouse_id = {$item['warehouse_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} " ."-"."{$tactic_key}".  $insert_sql . "\n";$result = $db->query($insert_sql);

            //复制facility_best_shipping_region
            $insert_sql = "INSERT INTO facility_best_shipping_region (`facility_id`, `warehouse_id`, `province_id`, `city_id`, `district_id`, `shipping_id`) 
            SELECT `facility_id`, {$warehouse_id}, `province_id`, `city_id`, `district_id`, `shipping_id`
            FROM facility_best_shipping_region where facility_id = {$item['facility_id']} and warehouse_id = {$item['warehouse_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $insert_sql . "\n";$result = $db->query($insert_sql);

            //复制facility_best_shipping_goods
            $insert_sql = "INSERT INTO facility_best_shipping_goods (`facility_id`, `warehouse_id`, `platform_goods_id`, `platform_sku_id`, `shipping_id`) 
            SELECT `facility_id`, {$warehouse_id}, `platform_goods_id`, `platform_sku_id`, `shipping_id`
            FROM facility_best_shipping_goods where facility_id = {$item['facility_id']} and warehouse_id = {$item['warehouse_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $insert_sql . "\n";$result = $db->query($insert_sql);
            //插入redis
            hsetRedisBestShippingGoods($item['facility_id'],$warehouse_id);

            //维护以下策略为仓库
            $update_sql = "update  facility_shipment_tactics set  warehouse_id = {$warehouse_id} where facility_id =  {$item['facility_id']} and facility_shipment_tactics_id = {$tactic['facility_shipment_tactics_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db->query($update_sql);

            $update_sql = "update  child_user set  warehouse_ids = {$warehouse_id} where  tactics_id = {$tactic['facility_shipment_tactics_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db_user->query($update_sql);

            $update_sql = "update  multi_goods_shipment set  warehouse_id = {$warehouse_id} , warehouse_name = '{$tactic['facility_shipment_tactics_name']}' where facility_id =  {$item['facility_id']} and  tactics_id = {$tactic['facility_shipment_tactics_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db->query($update_sql);

            $update_sql = "update  print_log  set  warehouse_id = {$warehouse_id} where facility_id =  {$item['facility_id']} and tactics_id = {$tactic['facility_shipment_tactics_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db->query($update_sql);

            $sql_package  = "select sp.package_id from shipment_package sp 
                inner join multi_goods_shipment mgs on mgs.facility_id = sp.facility_id and mgs.shipment_id = sp.shipment_id
                inner join mailnos m on m.facility_id = sp.facility_id and sp.package_id = m.package_id
                where sp.facility_id =  {$item['facility_id']} 
                      and mgs.tactics_id = {$tactic['facility_shipment_tactics_id']}
                      and ( m.warehouse_id != {$warehouse_id} or m.warehouse_id is null)
                limit 10000";
            do{
                echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $sql_package . "\n";$package_ids = $db->getCol($sql_package);
                if(! is_array($package_ids) || count($package_ids) == 0){
                    break;
                }
                $package_ids = implode("','", $package_ids);
                $update_sql = "update  mailnos set  warehouse_id = {$warehouse_id} where facility_id =  {$item['facility_id']} and  package_id in ('{$package_ids}') and thermal_type = 'PDD' ";
                echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db->query($update_sql);
            }while(true);
        }else{
            $insert_sql = "INSERT INTO facility_shipment_flag (`facility_shipment_flag_name`, `facility_id`, `is_sync_flag`, `shop_ids`, `region_ids`, `platform_sku_ids`, `is_like`, `sort`, `color`) 
                 SELECT '{$tactic['facility_shipment_tactics_name']}', `facility_id`,1, `shop_ids`, `region_ids`,`platform_sku_ids`, `is_like`, `sort`,'{$color[$tactic_key]}'
                 FROM facility_shipment_tactics where facility_id = {$item['facility_id']} and facility_shipment_tactics_id = {$tactic['facility_shipment_tactics_id']}";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $insert_sql . "\n";$result = $db->query($insert_sql);
            $facility_shipment_flag_id = $db->getOne("select last_insert_id()");

            $update_sql = " update facility_shipment_tactics set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and facility_shipment_tactics_id = {$tactic['facility_shipment_tactics_id']}";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db->query($update_sql);

            $update_sql = "update  multi_goods_shipment set  order_flag = {$facility_shipment_flag_id} , order_flag_name = '{$tactic['facility_shipment_tactics_name']}' 
                where facility_id =  {$item['facility_id']} and  tactics_id = {$tactic['facility_shipment_tactics_id']} ";
            echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $update_sql . "\n";$result = $db->query($update_sql);
        }
    }
    $facilityShipmentFlag = "SELECT `facility_shipment_flag_id`, `facility_shipment_flag_name`,`is_sync_flag`,`is_auto_cancel`, `shop_ids`, `region_ids`, `platform_sku_ids`, `is_like`, `sort` 
              FROM facility_shipment_flag where facility_id = {$item['facility_id']} and is_sync_flag = 1";
    echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $facilityShipmentFlag . "\n"; $facilityShipmentFlagList = $db->getAll($facilityShipmentFlag);
    if (!empty($facilityShipmentFlagList)){
        $redis->hset("facility_shipment_flag", $item['facility_id'] ,json_encode( array( 'facilityShipmentFlagList' => $facilityShipmentFlagList)));
    }
    $facilityShipmentTactics = "SELECT 
                fst.facility_shipment_tactics_id,fst.facility_shipment_tactics_name,
                fst.shop_ids,fst.region_ids,fst.platform_sku_ids,
                fst.is_like,fst.sort,w.warehouse_id,w.warehouse_name
            from
                facility_shipment_tactics  fst
                inner join warehouse w on fst.facility_id = w.facility_id and fst.warehouse_id = w.warehouse_id
            where
                fst.facility_id = {$item['facility_id']}
            order by  fst.sort  ";
     echo date("Y-m-d H:i:s") . " {$key} "."-"."{$tactic_key}" .  $facilityShipmentFlag . "\n"; $facilityShipmentTacticsList = $db->getAll($facilityShipmentTactics);
    if (!empty($facilityShipmentTacticsList)){
        $redis->hset("facility_shipment_tactics", $item['facility_id'], json_encode(array('facilityShipmentTacticsList' => $facilityShipmentTacticsList)));
    }
    $db->query("insert into jwang_temp_tacits values ({$item['facility_id']})");
    echo PHP_EOL . $key ."完成";
}


function hsetRedisBestShippingGoods($facility_id, $warehouse_id){
    global $db;
    $redis = getRedis();
    $sql = "
            select
                facility_best_shipping_goods_id as facilityBestShippingGoodsId,
                facility_id as facilityId,
                platform_goods_id as platformGoodsId,
                platform_sku_id as platformSkuId,
                shipping_id as shippingId,
                created_time as createdTime,
                last_updated_time as lastUpdatedTime
            from
                facility_best_shipping_goods
            where
                facility_id = {$facility_id}
                and warehouse_id = {$warehouse_id}
        ";
    echo date("Y-m-d H:i:s") .  $sql . "\n";
    $result = $db->getAll($sql);
    if (empty($result)){
        return ;
    }
    $platform_goods_list = array();
    foreach ($result as $item){
        $platform_goods_list[$item['platformGoodsId']][] = $item;
    }
    foreach ($platform_goods_list as $platform_goods_id => $goods_shipping_list){
        $redis->hset("facility_best_shipping_goods", $facility_id."_".$warehouse_id."_".$platform_goods_id ,json_encode( array( 'facilityBestShippingGoodsList'  => $goods_shipping_list)));
    }
}
