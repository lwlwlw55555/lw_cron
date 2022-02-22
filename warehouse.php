<?php
require("includes/init.php");
global $db,$db_user,$redis;
$redis = getRedis();

$sql_select = "
    select 
        warehouse_id, facility_id, warehouse_name
    from 
        warehouse 
    where 
        warehouse_name = '默认仓库'
";
$result_id = $db->getAll($sql_select);
echo count($result_id)." 个warehouse";
foreach ($result_id as $key =>$item){
    echo PHP_EOL .PHP_EOL .$key ."开始";
    // $db->beginTransaction();
    try {
        $sql = "
            INSERT INTO warehouse 
                (`warehouse_id`, `facility_id`, `province_id`, `province_name`, `city_id`,`city_name`, `district_id`, 
                `district_name`, `shipping_address`,`postcode`, `sender_mobile`, `sender_name`, `sender_company`,
                `best_shipping`, `best_shipping_refresh_time`)
            SELECT 
                w.warehouse_id, f.`facility_id`, f.`province_id`, f.`province_name`, f.`city_id`,f.`city_name`, f.`district_id`, 
                f.`district_name`, f.`shipping_address`, f.`postcode`, f.`sender_mobile`, f.`sender_name`, f.`sender_company`,
                f.`best_shipping`, f.`best_shipping_refresh_time` 
            from 
                facility f 
                inner join warehouse w on f.facility_id = w.facility_id  and f.default_warehouse_id = w.warehouse_id 
            where f.facility_id = '{$item['facility_id']}'
        
            ON DUPLICATE KEY UPDATE 
                province_id = values(province_id),
                province_name = values(province_name),
                city_id = values(city_id),
                city_name = values(city_name),
                district_id = values(district_id),
                district_name = values(district_name),
                shipping_address = values(shipping_address),
                postcode = values(postcode),
                sender_mobile = values(sender_mobile),
                sender_name= values(sender_name),
                sender_company = values(sender_company),
                best_shipping = values(best_shipping),
                best_shipping_refresh_time = values(best_shipping_refresh_time)
        ";
            
        $db->query($sql);
        $update_facility = " update  facility_address set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  multi_goods_shipment set  warehouse_id = {$item['warehouse_id']},warehouse_name = '{$item['warehouse_name']}' where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  print_log set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  mailnos set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility_shipping set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility_shipping_template set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility_bill_template set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility_best_shipping_goods set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);
        hsetRedisBestShippingGoods($item['facility_id'],$item['warehouse_id']);

        $update_facility = " update  facility_best_shipping_region set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility_best_shipping_goods_history set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility_best_shipping_region_history set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  inventory set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  inventory_detail set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  inventory_detail_order set  warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and warehouse_id is null";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        $update_facility = " update  facility set  default_warehouse_id = {$item['warehouse_id']} where facility_id =  {$item['facility_id']} and default_warehouse_id is null";
        echo date("Y-m-d H:i:s") . " {$key} " .  $update_facility . "\n";$result = $db->query($update_facility);

        //$db->commit();
    } catch(Exception $e) {
        var_dump($e->getMessage());
        // $db->rollback();
        die("woca");
    }   


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