<?php

date_default_timezone_set("Asia/Shanghai");
require("includes/ClsPdo.php");


$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);


echo date("Y-m-d H:i:s", time()) . " move mailnos to history " . PHP_EOL;

$end_time = date("Y-m-d 00:00:00", strtotime("-7 day", time()));

$mailnos_end_time = date("Y-m-d 00:00:00", strtotime("-20 day", time()));

echo date("Y-m-d H:i:s")."  delete shipment ".PHP_EOL;
for ($i = 0; $i < 256; $i++){
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

    $sql = "select mgs.shipment_id, mgsg.order_goods_id
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where mgs.shipping_time < '{$end_time}' and mgs.last_updated_time < '{$end_time}'
and mgs.platform_name = 'pinduoduo'
and mgs.shipment_status = 'SHIPPED'
and mgs.status = 'CONFIRM'
order by shipment_id limit 500";
    do {
        $ids = $erp_ddyun_db->getAll($sql);
        if(empty($ids)){
            break;
        }
        $shipment_ids = implode(",", array_column($ids, 'shipment_id'));
        $order_goods_ids = implode(",", array_column($ids, 'order_goods_id'));

        if(!empty($shipment_ids)){
            echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete shipment count : ".count($ids).PHP_EOL;
            $delete_shipment_sql = "delete from multi_goods_shipment where shipment_id in ({$shipment_ids})";
            $erp_ddyun_db->query($delete_shipment_sql);

            echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete shipment extension : ".count($ids).PHP_EOL;
            $delete_shipment_extension_sql = "delete from multi_goods_shipment_extension where shipment_id in ({$shipment_ids})";
            $erp_ddyun_db->query($delete_shipment_extension_sql);

            echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete shipment package : ".count($ids).PHP_EOL;
            $delete_shipment_package_sql = "delete from shipment_package where shipment_id in ({$shipment_ids})";
            $erp_ddyun_db->query($delete_shipment_package_sql);
        }

        if(!empty($order_goods_ids)){
            echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete shipment goods count : ".count($ids).PHP_EOL;
            $delete_shipment_goods_sql = "delete from multi_goods_shipment_goods where order_goods_id in ({$order_goods_ids})";
            $erp_ddyun_db->query($delete_shipment_goods_sql);

            echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete finance detail count : ".count($ids).PHP_EOL;
            $delete_finance_detail_sql = "delete from finance_detail where order_goods_id in ({$order_goods_ids})";
            $erp_ddyun_db->query($delete_finance_detail_sql);
        }
    }while(true);
    echo date("Y-m-d H:i:s").$erp_ddyun_db_conf['name']." shipment delete finish ".PHP_EOL;

    /**
     * 删除mailnos_extension
     */
    $limit = 0;
    do{
        $sql = "select id from mailnos where last_update_time < '{$mailnos_end_time}' limit {$limit}, 200";
        echo date("Y-m-d H:i:s")." ".$sql.PHP_EOL;
        $mailnos_ids = $erp_ddyun_db->getCol($sql);
        if(empty($mailnos_ids)){
            break;
        }
        $limit += 200;
        $ids_str = implode(",", $mailnos_ids);
        echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete mailnos_extension detail count : ".count($mailnos_ids).PHP_EOL;
        $delete_mailnos_extension_sql = "delete from mailnos_extension where id in ({$ids_str})";
        $erp_ddyun_db->query($delete_mailnos_extension_sql);
    }while(true);
    echo date("Y-m-d H:i:s").$erp_ddyun_db_conf['name']." mailnos_extension delete finish ".PHP_EOL;

    /**
     * 删除mailnos 保留oauth_share_id > 0 的数据
     */
    $sql = "select id from mailnos where last_update_time < '{$mailnos_end_time}' and oauth_share_id = 0 limit 200";
    do{
        echo date("Y-m-d H:i:s")." ".$sql.PHP_EOL;
        $mailnos_ids = $erp_ddyun_db->getCol($sql);
        if(empty($mailnos_ids)){
            break;
        }
        $ids_str = implode(",", $mailnos_ids);
        echo date("Y-m-d H:i:s")."  ".$erp_ddyun_db_conf['name']." delete mailnos detail count : ".count($mailnos_ids).PHP_EOL;
        $delete_mailnos_extension_sql = "delete from mailnos where id in ({$ids_str})";
        $erp_ddyun_db->query($delete_mailnos_extension_sql);
    }while(true);
    echo date("Y-m-d H:i:s").$erp_ddyun_db_conf['name']." mailnos delete finish ".PHP_EOL;

}
echo date("Y-m-d H:i:s")." shipment delete finish ".PHP_EOL;