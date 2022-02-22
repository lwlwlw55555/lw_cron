<?php
require("includes/init.php");
global $db,$db_user,$redis;
$sql = "
    select 
        f.facility_id 
    from 
        facility f 
        left join jwang_temp_finance ff on f.facility_id = ff.facility_id 
    where 
        ff.facility_id is null 
";
$facility_list = $db->getCol($sql);
foreach ($facility_list as $key => $facility_id){
    try {
        $sql = " UPDATE sku SET weight_is_set = 1 WHERE facility_id = {$facility_id} and weight >  0 ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $sql . "\n";$db->query($sql);

        $sql = " UPDATE sku SET package_fee_is_set = 1 WHERE facility_id = {$facility_id} and package_fee >  0 ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $sql . "\n";$db->query($sql);

        $sql = " UPDATE inventory SET purchase_price_is_set = 1 WHERE facility_id = {$facility_id} and purchase_price >  0 ";
        echo date("Y-m-d H:i:s") . " {$key} " .  $sql . "\n\n";$db->query($sql);

        $db->query("insert into jwang_temp_flag values ({$facility_id})");
    } catch(Exception $e) {
        var_dump($e->getMessage());
        die("woca");
    }

}