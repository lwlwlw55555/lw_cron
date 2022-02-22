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
//    backup_inventory_lock_new();
    backup_inventory_lock_old();
    backup_order_exception_mapping();
    backup_inventory_location_lock_used();
//     cancel 的inventory_location_lock 删除，不备份
    backup_inventory_location_lock_cancel();
}

function backup_inventory_lock_old() {
    global $oms_db;
    /**
     * 备份inventory_lock( 一个月前已USED的订单 )
     */
    $backup_inventory_lock_sql = "insert into inventory_location_backup select 
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
            and type = 'SALE_OUT' 
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
        order by
            inventory_lock_id";
    do{
        $inventory_lock_list = $oms_db->getAll( $backup_inventory_lock_sql );
        if ( empty($inventory_lock_list) ) {
            return;
        }
        insert_into_inventory_lock_backup( $inventory_lock_list );
        delete_inventory_lock( $inventory_lock_list );
    } while( true );
}

function backup_inventory_lock_new() {
    global $oms_db;
    /**
     * 备份inventory_lock( 一个月前已USED的订单 )
     */
    $backup_inventory_lock_sql = "select 
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
            lock_status = 'USED' 
            and type = 'SALE_OUT' 
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) limit 200";
    do{
        $inventory_lock_list = $oms_db->getAll( $backup_inventory_lock_sql );
        if ( empty($inventory_lock_list) ) {
            return;
        }
        insert_into_inventory_lock_backup( $inventory_lock_list );
//        delete_inventory_lock( $inventory_lock_list );
    } while( true );
}

function backup_inventory_location_lock_used() {
    global $oms_db;
    $backup_inventory_location_lock_sql = "select
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
            and type = 'SALE_OUT'
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
        order by
            inventory_location_lock_id
        limit 200";
    do {
        $inventory_location_lock_list = $oms_db->getAll( $backup_inventory_location_lock_sql );
        if ( empty($inventory_location_lock_list) ) {
            return;
        }
        insert_inventory_location_lock_backup( $inventory_location_lock_list );
        delete_inventory_location_lock( $inventory_location_lock_list );
    } while( true );
}

// cancel 的inventory_location_lock 删除，不备份
function backup_inventory_location_lock_cancel() {
    global $oms_db;
    $backup_inventory_location_lock_sql = "select
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
            status = 'CANCEL'
            and type = 'SALE_OUT'
            and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
        order by
            inventory_location_lock_id
        limit 200";
    do {
        $inventory_location_lock_list = $oms_db->getAll( $backup_inventory_location_lock_sql );
        if ( empty($inventory_location_lock_list) ) {
            return;
        }
//        insert_inventory_location_lock_backup( $inventory_location_lock_list );
        delete_inventory_location_lock( $inventory_location_lock_list );
    } while( true );
}

function backup_order_exception_mapping() {
    global $oms_db;

    /**
     * 备份order_exception_mapping
     */
    $backup_order_exception_mapping_sql = "select
        order_exception_mapping_id,
        order_exception_id,
        party_id,
        order_id,
        order_type,
        origin_order_goods_ids,
        content,
        enabled,
        created_time,
        last_updated_time,
        created_user_id,
        last_updated_user_id
    from
        order_exception_mapping
    where
        enabled = 0
        and last_updated_time < SUBDATE( CURDATE(), interval 1 month ) 
    order by
        order_exception_mapping_id
    limit 200";
    $i = 0;
    do{
        $order_exception_mapping_list = $oms_db->getAll( $backup_order_exception_mapping_sql );
        if ( empty($order_exception_mapping_list) ) {
            return;
        }
        $i++;
        echo $i." ";
        insert_into_order_exception_mapping( $order_exception_mapping_list );die;
        delete_order_exception( $order_exception_mapping_list );die;
    } while( true );
}

function insert_into_inventory_lock_backup( $inventory_lock_list ) {
    global $oms_db, $oms_db_conf;
    if ( empty($inventory_lock_list) ) {
        return;
    }
    $insert_inventory_lock_backup_sql = "insert ignore inventory_lock_backup( 
            inventory_lock_id,
            inventory_batch_id,
            party_id,
            biz_id,
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
            last_updated_user_id ) value ";
    foreach ( $inventory_lock_list as $inventory_lock ) {
        $insert_inventory_lock_backup_sql .= "(".
            checkNull( $inventory_lock['inventory_lock_id'] ).",".
            checkNull( $inventory_lock['inventory_batch_id'] ).",".
            checkNull( $inventory_lock['party_id'] ).",".
            checkNull( $inventory_lock['biz_id'] ).",".
            checkNull( $inventory_lock['warehouse_id'] ).",".
            checkNull( $inventory_lock['sku_id'] ).",".
            checkNull( $inventory_lock['goods_sub_type'] ).",".
            checkNull( $inventory_lock['type'] ).",".
            checkNull( $inventory_lock['status'] ).",".
            checkNull( $inventory_lock['lock_status'] ).",".
            checkNull( $inventory_lock['quantity'] ).",".
            checkNull( $inventory_lock['order_id'] ).",".
            checkNull( $inventory_lock['order_goods_id'] ).",".
            checkNull( $inventory_lock['created_time'] ).",".
            checkNull( $inventory_lock['last_updated_time'] ).",".
            checkNull( $inventory_lock['created_user_id'] ).",".
            checkNull( $inventory_lock['last_updated_user_id'] )."),";
    }
    $insert_inventory_lock_backup_sql = substr( $insert_inventory_lock_backup_sql, 0, -1 );
    if ( !empty( $insert_inventory_lock_backup_sql ) ) {
        $oms_db->query( $insert_inventory_lock_backup_sql );
        echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." insert ".count($inventory_lock_list) ." inventory_lock into backup".PHP_EOL;
    }
}

function delete_inventory_lock( $inventory_lock_list ) {
    global $oms_db, $oms_db_conf;
    if ( empty($inventory_lock_list) ) {
        return;
    }
    $ids = implode( ",", array_column( $inventory_lock_list, 'inventory_lock_id' ) );
    $delete_inventory_lock_sql = "delete from inventory_lock where inventory_lock_id in ( $ids )";
    $oms_db->query( $delete_inventory_lock_sql );
    echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." delete ".count($inventory_lock_list) ." inventory_lock".PHP_EOL;
}

function insert_into_order_exception_mapping( $order_exception_mapping_list ) {
    global $oms_db, $oms_db_conf;
    if ( empty( $order_exception_mapping_list ) ) {
        return;
    }
    $insert_order_exception_mapping_backup_sql = "insert into order_exception_mapping_backup(
        order_exception_mapping_id,
        order_exception_id,
        party_id,
        order_id,
        order_type,
        origin_order_goods_ids,
        content,
        enabled,
        created_time,
        last_updated_time,
        created_user_id,
        last_updated_user_id ) value ";
    foreach ( $order_exception_mapping_list as $order_exception_mapping ) {
        $insert_order_exception_mapping_backup_sql .= "(".
            checkNull( $order_exception_mapping['order_exception_mapping_id'] ).",".
            checkNull( $order_exception_mapping['order_exception_id'] ).",".
            checkNull( $order_exception_mapping['party_id'] ).",".
            checkNull( $order_exception_mapping['order_id'] ).",".
            checkNull( $order_exception_mapping['order_type'] ).",".
            checkNull( $order_exception_mapping['origin_order_goods_ids'] ).",".
            checkNull( $order_exception_mapping['content'] ).",".
            checkNull( $order_exception_mapping['enabled'] ).",".
            checkNull( $order_exception_mapping['created_time'] ).",".
            checkNull( $order_exception_mapping['last_updated_time'] ).",".
            checkNull( $order_exception_mapping['created_user_id'] ).",".
            checkNull( $order_exception_mapping['last_updated_user_id'] )."),";
    }
    $insert_order_exception_mapping_backup_sql = substr( $insert_order_exception_mapping_backup_sql, 0, -1 );
    if ( !empty( $insert_order_exception_mapping_backup_sql ) ) {
        $oms_db->query( $insert_order_exception_mapping_backup_sql );
        echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." insert ".count($order_exception_mapping_list) ." order_exception_mapping into backup".PHP_EOL;
    }
}

function delete_order_exception( $order_exception_mapping_list ) {
    global $oms_db, $oms_db_conf;
    if ( empty($order_exception_mapping_list) ) {
        return;
    }
    $ids = implode( ",", array_column( $order_exception_mapping_list, "order_exception_mapping_id" ) );
    $delete_order_exception_mapping_sql = "delete from order_exception_mapping where order_exception_mapping_id in ( $ids )";
    $oms_db->query( $delete_order_exception_mapping_sql );
    echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." delete ".count($order_exception_mapping_list) ." order_exception_mapping ".PHP_EOL;
}

function insert_inventory_location_lock_backup( $inventory_location_lock_list ) {
    global $oms_db, $oms_db_conf;
    if ( empty($inventory_location_lock_list) ) {
        return;
    }
    $insert_inventory_location_lock_backup_sql = "insert ignore inventory_location_lock_backup( 
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
        last_updated_user_id ) value ";
    foreach ( $inventory_location_lock_list as $inventory_location_lock ) {
        $insert_inventory_location_lock_backup_sql .= "(".
            checkNull( $inventory_location_lock['inventory_location_lock_id'] ).",".
            checkNull( $inventory_location_lock['inventory_location_batch_id'] ).",".
            checkNull( $inventory_location_lock['party_id'] ).",".
            checkNull( $inventory_location_lock['biz_id'] ).",".
            checkNull( $inventory_location_lock['warehouse_id'] ).",".
            checkNull( $inventory_location_lock['sku_id'] ).",".
            checkNull( $inventory_location_lock['goods_sub_type'] ).",".
            checkNull( $inventory_location_lock['warehouse_location_id'] ).",".
            checkNull( $inventory_location_lock['type'] ).",".
            checkNull( $inventory_location_lock['status'] ).",".
            checkNull( $inventory_location_lock['is_re_lock'] ).",".
            checkNull( $inventory_location_lock['quantity'] ).",".
            checkNull( $inventory_location_lock['order_id'] ).",".
            checkNull( $inventory_location_lock['order_goods_id'] ).",".
            checkNull( $inventory_location_lock['created_time'] ).",".
            checkNull( $inventory_location_lock['last_updated_time'] ).",".
            checkNull( $inventory_location_lock['created_user_id'] ).",".
            checkNull( $inventory_location_lock['last_updated_user_id'] )."),";
    }
    $insert_inventory_location_lock_backup_sql = substr( $insert_inventory_location_lock_backup_sql, 0, -1 );
    if ( !empty( $insert_inventory_location_lock_backup_sql ) ) {
        $oms_db->query( $insert_inventory_location_lock_backup_sql );
        echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." insert ".count($inventory_location_lock_list) ." inventory_location_lock into backup".PHP_EOL;
    }
}

function delete_inventory_location_lock( $inventory_location_lock_list ) {
    global $oms_db, $oms_db_conf;
    if ( empty($inventory_location_lock_list) ) {
        return;
    }
    $ids = implode( ",", array_column( $inventory_location_lock_list, 'inventory_location_lock_id' ) );
    $delete_inventory_location_lock_sql = "delete from inventory_location_lock where inventory_location_lock_id in ( $ids )";
    $oms_db->query( $delete_inventory_location_lock_sql );
    echo date("Y-m-d H:i:s")." ".$oms_db_conf['name']." delete ".count($inventory_location_lock_list) ." inventory_location_lock".PHP_EOL;
}

function checkNull($temp){
    if(!is_null($temp)){
        $temp = addslashes($temp);
        $temp = "'{$temp}'";
    }else{
        $temp = 'null';
    }
    return $temp;
}