<?php
require("includes/init.php");
echo(date("Y-m-d H:i:s") . " updateBigShop  begin \r\n");

$sql = "update shop_extension set is_big_shop = 1 where is_big_shop not in (1, 2) and mod(shop_id, 2) = 1";
$sync_db->query($sql);
$sql = "update shop_extension set is_big_shop = 2 where is_big_shop not in (1, 2) and mod(shop_id, 2) = 0";
$sync_db->query($sql);

$sql = "select shop_id, is_big_shop from shop_extension where shop_mod = 0  and is_big_shop in (1, 2) order by is_big_shop, shop_id";
$shop_list = $sync_db->getAll($sql);

foreach ($shop_list as $shop) {

    $sql = "
        select 
            shop_mod, count(1) count
        from 
            shop_extension 
        where 
            is_big_shop = {$shop['is_big_shop']} and 
            shop_mod > 0 
        group by
            shop_mod 
        order by 
            count(1) desc, shop_mod
    ";
    $shop_mod = 0;
    $temp_list = $sync_db->getAll($sql);
    echo json_encode($temp_list) . "\n";
    if (! $temp_list || count($temp_list) < 199) {
        $shop_mod = count($temp_list) + 1;
    } else {
        $max_count = $temp_list[0]['count'];
        foreach ($temp_list as $temp)  {
            if ($temp['count'] < $max_count) {
                $shop_mod = $temp['shop_mod'];
                break;
            }
        }
    }
    if ($shop_mod == 0) {
        $shop_mod = 1;
    }
        
    echo date("Y-m-d H:i:s") . " update shop_extension set shop_mod = {$shop_mod} where shop_id = {$shop['shop_id']} and is_big_shop =  {$shop['is_big_shop']}\n";
    $sync_db->query("update shop_extension set shop_mod = {$shop_mod} where shop_id =  {$shop['shop_id']} and is_big_shop =  {$shop['is_big_shop']}");
}

echo(date("Y-m-d H:i:s") . " updateBigShop end \r\n");
