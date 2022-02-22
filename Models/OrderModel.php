<?php
namespace Models;
class OrderModel{
	public static function getUnShippedOrderSn($shop_id,$platform_name,$facility_id){
		global $db;
        selectRdsByShopId($shop_id);
        $sql_wait_ship = "
            SELECT
                order_sn AS order_sn
            FROM
                multi_goods_shipment
            WHERE
                shop_id = {$shop_id}
            AND shipment_status = 'WAIT_SHIP'
            AND STATUS = 'CONFIRM'
            and facility_id = {$facility_id}
            and created_user = 'OPENAPI'
                ";  

        $order_wait_ship = $db->getCol($sql_wait_ship);

        $sql_pre_ship = "
            SELECT
                order_sn AS order_sn
            FROM
                multi_goods_shipment
            WHERE
                shop_id = {$shop_id}
            AND shipment_status = 'PRE_SHIP'
            AND STATUS = 'CONFIRM'
            and created_user = 'OPENAPI'
            and facility_id = {$facility_id}
                ";  
        $order_pre_ship = $db->getCol($sql_pre_ship);

        return array_merge($order_wait_ship,$order_pre_ship);
    }


	public static function updateOrderRefund($order_sn,$platform_shop_id){
		global $db;
		$sql = "UPDATE order_info 
				SET pay_status = 'PS_REFUND_APPLY' 
				where order_sn = '{$order_sn}'
				and shop_id = {$platform_shop_id}";
		$db->query($sql);
		echo(date("Y-m-d H:i:s") . " {$sql} \r\n");
		$sql = "UPDATE shipment 
				set  status = 'CANCEL' 
				where order_sn = '{$order_sn}'
				and shop_id = {$platform_shop_id}";
		$db->query($sql);
		echo(date("Y-m-d H:i:s") . " {$sql} \r\n");
	}

}
?>
