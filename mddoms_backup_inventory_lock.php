<?php
date_default_timezone_set("Asia/Shanghai");
require ("includes/ClsPdo.php");

global $oms_db, $oms_db_conf;

$oms_db_conf = array(
    "host" => "10.1.14.61:3306",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
);

echo date("Y-m-d H:i:s")." 备份一个月前已 used 的inventory_lock & inventory_location_lock begin".PHP_EOL;

for ( $i = 0; $i <= 0; $i++ ) {
    $oms_db_conf['name'] = "mddoms_".$i;
    $oms_db = ClsPdo::getInstance( $oms_db_conf );
    backup_inventory_lock_old();
    backup_inventory_location_lock();

//    delete_inventory_lock_old();
//    delete_inventory_location_lock();
//    delete_inventory_location_lock_cancel();
}

function backup_inventory_lock_old() {
    global $oms_db, $oms_db_conf;
    /**
     * 备份inventory_lock( 一个月前已USED的订单 )
     */
    $backup_inventory_lock_sql = "insert ignore into inventory_lock_backup select 
           inventory_lock_id, 
           inventory_batch_id, 
           party_id, biz_id, 
           warehouse_id, 
           sku_id, 
           goods_sub_type, 
           type, 
           status, 
           lock_status, 
           quantity, 
           order_id, 
           order_goods_id, 
           created_time, 
           last_updated_time, 
           created_user_id, 
           last_updated_user_id 
        from 
            inventory_lock 
        where 
            status = 'USED'
            and last_updated_time < SUBDATE( CURDATE(), interval 20 day ) 
        order by
            inventory_lock_id";
    $oms_db->query( $backup_inventory_lock_sql );
    echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." used inventory_lock backup".PHP_EOL;
}

function backup_inventory_location_lock() {
    global $oms_db, $oms_db_conf;
    $backup_inventory_location_lock_sql = "insert ignore into inventory_location_lock_backup select
            inventory_location_lock_id,
            inventory_location_batch_id,
            party_id,
            biz_id,
            warehouse_id,
            sku_id,
            goods_sub_type,
            warehouse_location_id,
            type,
            status,
            is_re_lock,
            quantity,
            order_id,
            order_goods_id,
            created_time,
            last_updated_time,
            created_user_id,
            last_updated_user_id
        from
            inventory_location_lock
        where
            status = 'USED'
            and last_updated_time < SUBDATE( CURDATE(), interval 20 day ) 
        order by
            inventory_location_lock_id";
    $oms_db->query( $backup_inventory_location_lock_sql );
    echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." used inventory_location_lock backup".PHP_EOL;
}

function delete_inventory_lock_old() {
    global $oms_db, $oms_db_conf;
    $inventory_lock_id_sql = "select 
           inventory_lock_id 
        from 
            inventory_lock 
        where 
            status = 'USED'
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
        order by
            inventory_lock_id 
        limit 
            200";
    do{
        $inventory_lock_id_list = $oms_db->getCol( $inventory_lock_id_sql );
        if ( empty($inventory_lock_id_list) ) {
            return;
        }
        $ids = implode( ",", $inventory_lock_id_list );
        $delete_inventory_lock_sql = "delete from inventory_lock where inventory_lock_id in ( $ids )";
        $oms_db->query( $delete_inventory_lock_sql );
        echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." delete ".count($inventory_lock_id_list) ." inventory_lock".PHP_EOL;
    } while( true );
}

function delete_inventory_location_lock() {
    global $oms_db, $oms_db_conf;
    $inventory_location_lock_id_sql = "select 
            inventory_location_lock_id
        from 
            inventory_location_lock
        where 
            status = 'USED'
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
        order by
            inventory_location_lock_id 
        limit 
            200";
    do{
        $inventory_location_lock_id_list = $oms_db->getCol( $inventory_location_lock_id_sql );
        if ( empty($inventory_location_lock_id_list) ) {
            break;
        }
        $ids = implode( ",", $inventory_location_lock_id_list );
        $delete_inventory_lock_sql = "delete from inventory_location_lock where inventory_location_lock_id in ( $ids )";
        $oms_db->query( $delete_inventory_lock_sql );
        echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." delete ".count($inventory_location_lock_id_list) ." inventory_location_lock".PHP_EOL;
    } while( true );
}


function delete_inventory_location_lock_cancel() {
    global $oms_db, $oms_db_conf;
    $inventory_location_lock_id_sql = "select 
            inventory_location_lock_id
        from 
            inventory_location_lock
        where 
            status = 'CANCEL'
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
        order by
            inventory_location_lock_id 
        limit 
            200";
    do{
        $inventory_location_lock_id_list = $oms_db->getCol( $inventory_location_lock_id_sql );
        if ( empty($inventory_location_lock_id_list) ) {
            break;
        }
        $ids = implode( ",", $inventory_location_lock_id_list );
        $delete_inventory_lock_sql = "delete from inventory_location_lock where inventory_location_lock_id in ( $ids )";
        $oms_db->query( $delete_inventory_lock_sql );
        echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." delete ".count($inventory_location_lock_id_list) ." inventory_location_lock".PHP_EOL;
    } while( true );
}