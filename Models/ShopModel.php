<?php
namespace Models;
class ShopModel{
    public static function getShopListGroup($start_shop_id,$end_shop_id){
        global $db;
        $sql = "SELECT
                        s.*
                FROM
                        shop s
                inner join facility_cainiao fc on s.default_facility_id = fc.facility_id
                WHERE
                        s.enabled = 1 and fc.expire_time > now()
                        and s.shop_id > {$start_shop_id} and s.shop_id <= {$end_shop_id}
                ";
        return $db->getAll($sql);
    }
    public static function getShopList(){
        global $db;
        $sql = "SELECT
                        s.*
                FROM
                        shop s
                inner join facility_cainiao fc on s.default_facility_id = fc.facility_id
                WHERE
                        s.enabled = 1 and fc.expire_time > now()";
        return $db->getAll($sql);
    }
    public static function getNewShopList($minutes=6){
        global $sync_db;
        $sql = "SELECT
                        s.shop_id, s.platform_name ,s.default_facility_id
                FROM
                        shop s 
                        inner join shop_extension se on s.shop_id = se.shop_id 
                WHERE
                        s.created_time >  DATE_SUB(now(),INTERVAL {$minutes} minute) and 
                        s.platform_name <> 'taobao' and 
                        s.enabled = 1 and 
                        se.is_big_shop < 20 
                ";
        $shop_list = $sync_db->getAll($sql);
        return $shop_list;
    }
    public static function updateShopSyncTime($shop_id,$last_plan_sync_time,$order_type=''){
        global $db, $sync_db;
        $column = 'last_plan_sync_time';
        if ($order_type == "WAIT_SHIP") {
            $column = 'last_plan_sync_wait_ship_time';
        } else if ($order_type == "SHIPPED"){
            $column = 'last_plan_sync_shipped_time';
        }
        $sql = "UPDATE shop_extension 
                SET ".$column." = '{$last_plan_sync_time}' 
                WHERE shop_id = {$shop_id} and {$column} < '{$last_plan_sync_time}'";
        $sync_db->query($sql);
    }
    public static function enableShop($shop_id){
        global $db, $sync_db;
        $sql = "UPDATE 
                    shop
                SET enabled = 0
                WHERE
                    shop_id = {$shop_id}";
        $db->query($sql);
        $sync_db->query($sql);
        $sql = "UPDATE 
                    shop_extension
                SET enabled = 0,
                    is_big_shop = 0
                WHERE
                    shop_id = {$shop_id}";
        $sync_db->query($sql);
    }

}
?>
