<?php
date_default_timezone_set("Asia/Shanghai");
require ("includes/ClsPdo.php");

$erp_ddyun_history_db_conf = array(
    "host" => "100.65.2.183:32058",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

echo date("Y-m-d H:i:s")."  delete history 3 month ago data begin".PHP_EOL;
for ($i = 0; $i < 256; $i++){
    $erp_ddyun_history_db_conf['name'] = "erp_".$i;
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);

    /**
     * 删除3个月前的 print_log
     */
    $sql = "select id from print_log where created_time < SUBDATE(CURDATE(), interval 3 month ) limit 500 ";
    do{
        echo $sql.PHP_EOL;
        $print_log_list = $erp_ddyun_history_db->getCol($sql);
        if(! $print_log_list){
            break;
        }
        $ids = implode(",", $print_log_list);
        echo $erp_ddyun_history_db_conf['name']."  delete print_log ".count($print_log_list)." ".PHP_EOL;
        $erp_ddyun_history_db->query("delete from print_log where id in ($ids)");
    }while(true);
    echo $erp_ddyun_history_db_conf['name']."delete print_log data finish".PHP_EOL;

    $sql = "delete from print_log_extension_encrypt where created_time < SUBDATE(CURDATE(), interval 3 month ) ";
    $erp_ddyun_history_db->query( $sql );
    echo $erp_ddyun_history_db_conf['name']."delete print_log_extension_encrypt data finish".PHP_EOL;

    /**
     * 删除三个月前订单相关数据
     */
    $sql = "select mgs.shipment_id, mgsg.order_goods_id
from multi_goods_shipment mgs
         inner join multi_goods_shipment_goods mgsg
                    on mgs.facility_id = mgsg.facility_id and mgs.shipment_id = mgsg.original_shipment_id
where ( mgs.last_updated_time < SUBDATE(CURDATE(), interval 3 month ) 
    or mgs.created_time < SUBDATE(CURDATE(), interval 6 month ) ) limit 200 ";
    do{
        echo $sql.PHP_EOL;
        $shipment = $erp_ddyun_history_db->getAll($sql);
        if( !$shipment){
            break;
        }
        $shipment_ids = implode(",", array_column($shipment, 'shipment_id'));
        $order_goods_ids = implode(",", array_column($shipment, 'order_goods_id'));

        echo $erp_ddyun_history_db_conf['name']." delete multi_goods_shipment".count($shipment).PHP_EOL;
        echo $erp_ddyun_history_db_conf['name']." delete multi_goods_shipment_goods".count($shipment).PHP_EOL;
        echo $erp_ddyun_history_db_conf['name']." delete multi_goods_shipment_extension".count($shipment).PHP_EOL;
        echo $erp_ddyun_history_db_conf['name']." delete finance_detail".count($shipment).PHP_EOL;

        $erp_ddyun_history_db->query("delete from multi_goods_shipment where shipment_id in ({$shipment_ids})");
        $erp_ddyun_history_db->query("delete from multi_goods_shipment_goods where order_goods_id in ({$order_goods_ids})");
        $erp_ddyun_history_db->query("delete from multi_goods_shipment_extension where shipment_id in ({$shipment_ids})");
        $erp_ddyun_history_db->query("delete from finance_detail where order_goods_id in ({$order_goods_ids})");
    }while(true);
    echo $erp_ddyun_history_db_conf['name']."delete mgs, mgsg, mgse, fd data finish".PHP_EOL;

    /**
     * 删除三个月前的mailnos
     */
    $sql = "select id from mailnos where created_time < SUBDATE(CURDATE(), interval 3 month ) limit 200";
    do{
        echo $sql.PHP_EOL;
        $ids = $erp_ddyun_history_db->getCol($sql);
        if(! $ids){
            break;
        }
        echo $erp_ddyun_history_db_conf['name']."  delete mailnos and mailnos_extension ".count($ids)." ".PHP_EOL;
        $ids = implode(",", $ids);
        $erp_ddyun_history_db->query("delete from mailnos where id in ({$ids})");
        $erp_ddyun_history_db->query("delete from mailnos_extension where id in ({$ids})");
    }while(true);
    echo $erp_ddyun_history_db_conf['name']."delete mailnos, mailnos_extension data finish".PHP_EOL;

    /**
     * 删除三个月前的 shipment_package
     */
    $sql = "select id from shipment_package where created_time < SUBDATE(CURDATE(), interval 3 month ) limit 200";
    do{
        echo $sql.PHP_EOL;
        $ids = $erp_ddyun_history_db->getCol($sql);
        if(! $ids){
            break;
        }
        echo $erp_ddyun_history_db_conf['name']."  delete shipment_package".count($ids)." ".PHP_EOL;
        $ids = implode(",", $ids);
        $erp_ddyun_history_db->query("delete from shipment_package where id in ({$ids})");
    }while(true);
    echo $erp_ddyun_history_db_conf['name']."delete shipment_package data finish".PHP_EOL;

    /**
     * 删除15天前的 mailnos_extension
     */
    $limit = 0;
    while(true){
        $sql = "select id from mailnos where created_time <= SUBDATE(CURDATE(), interval 15 day) and created_time >= SUBDATE(CURDATE(), interval 16 day) limit {$limit}, 200";
        echo $sql.PHP_EOL;
        $limit += 200;
        $ids = $erp_ddyun_history_db->getCol($sql);
        if(empty($ids)){
            break;
        }
        echo $erp_ddyun_history_db_conf['name']." delete mailnos_extension limit ".$limit.PHP_EOL;
        $ids = implode(",", $ids);
        $erp_ddyun_history_db->query("delete from mailnos_extension where id in ({$ids})");
    }
    echo $erp_ddyun_history_db_conf['name']." delete mailnos_extension finish".PHP_EOL;
}
echo date("Y-m-d H:i:s")."  delete history 3 month ago data begin".PHP_EOL;