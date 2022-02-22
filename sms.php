<?php
require("includes/init.php");
global $db, $sync_db, $db_user;
$is_ok = true;

$sql = "select count(1) from shop_extension where enabled = 1 and shop_mod < 200 and last_plan_sync_time < date_sub(now(), interval 15 minute)";
$shop_extension_delay = $sync_db->getOne($sql);
if ($shop_extension_delay > 10) {
    send_sms("ERP报警 shop_extension 5分钟延迟{$shop_extension_delay}家");
    $is_ok = false;
}

$sql = "
    select 
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_1))/60) as last_plan_sync_time_1_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_2))/60) as last_plan_sync_time_2_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_3))/60) as last_plan_sync_time_3_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_4))/60) as last_plan_sync_time_4_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_5))/60) as last_plan_sync_time_5_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_6))/60) as last_plan_sync_time_6_dalay
    from 
        platform_extension 
    where 
        (last_plan_sync_time_1 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_2 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_3 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_4 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_5 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_6 < date_sub(now(), interval 3 minute)) and platform_name = 'pinduoduo'
";
$platform_extension_delay = $sync_db->getRow($sql);
if ($platform_extension_delay) {
    $msg = " last_plan_sync_time_";
    if ($platform_extension_delay['last_plan_sync_time_1_dalay'] > 3) {
        $msg .= " 1延迟{$platform_extension_delay['last_plan_sync_time_1_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_2_dalay'] > 3) {
        $msg .= " 2延迟{$platform_extension_delay['last_plan_sync_time_2_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_3_dalay'] > 3) {
        $msg .= " 3延迟{$platform_extension_delay['last_plan_sync_time_3_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_4_dalay'] > 3) {
        $msg .= " 4延迟{$platform_extension_delay['last_plan_sync_time_4_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_5_dalay'] > 3) {
        $msg .= " 5延迟{$platform_extension_delay['last_plan_sync_time_5_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_6_dalay'] > 3) {
        $msg .= " 6延迟{$platform_extension_delay['last_plan_sync_time_6_dalay']}分钟 ";
    }
    send_sms("platform_extension {$msg}");
    $is_ok = false;
}

$sql = "
    select 
        pdp_rds_name,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_1))/60) as last_plan_sync_time_1_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_2))/60) as last_plan_sync_time_2_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_3))/60) as last_plan_sync_time_3_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_4))/60) as last_plan_sync_time_4_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_5))/60) as last_plan_sync_time_5_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_6))/60) as last_plan_sync_time_6_dalay
    from 
        platform_extension 
    where 
        pdp_rds_name <> 'pdp_sync_5' and (
            last_plan_sync_time_1 < date_sub(now(), interval 3 minute) or 
            last_plan_sync_time_2 < date_sub(now(), interval 3 minute) or 
            last_plan_sync_time_3 < date_sub(now(), interval 3 minute) or 
            last_plan_sync_time_4 < date_sub(now(), interval 3 minute) or 
            last_plan_sync_time_5 < date_sub(now(), interval 3 minute) or 
            last_plan_sync_time_6 < date_sub(now(), interval 3 minute)
        )
";
global $dddd_sync_db_conf;
if(isset($dddd_sync_db_conf)){
    $dddd_sync_db = ClsPdo::getInstance($dddd_sync_db_conf);
}
$platform_extension_delays = $dddd_sync_db->getAll($sql);
foreach ($platform_extension_delays as $platform_extension_delay) {
        $msg = " last_plan_sync_time_";
        if ($platform_extension_delay['last_plan_sync_time_1_dalay'] > 3) {
            $msg .= " 1延迟{$platform_extension_delay['last_plan_sync_time_1_dalay']}分钟 ";
        }
        if ($platform_extension_delay['last_plan_sync_time_2_dalay'] > 3) {
            $msg .= " 2延迟{$platform_extension_delay['last_plan_sync_time_2_dalay']}分钟 ";
        }
        if ($platform_extension_delay['last_plan_sync_time_3_dalay'] > 3) {
            $msg .= " 3延迟{$platform_extension_delay['last_plan_sync_time_3_dalay']}分钟 ";
        }
        if ($platform_extension_delay['last_plan_sync_time_4_dalay'] > 3) {
            $msg .= " 4延迟{$platform_extension_delay['last_plan_sync_time_4_dalay']}分钟 ";
        }
        if ($platform_extension_delay['last_plan_sync_time_5_dalay'] > 3) {
            $msg .= " 5延迟{$platform_extension_delay['last_plan_sync_time_5_dalay']}分钟 ";
        }
        if ($platform_extension_delay['last_plan_sync_time_6_dalay'] > 3) {
            $msg .= " 6延迟{$platform_extension_delay['last_plan_sync_time_6_dalay']}分钟 ";
        }
        send_sms("dddd platform_extension {$platform_extension_delay['pdp_rds_name']} {$msg}","13758199822,13615811405");
        $is_ok = false;
}


$sql = "select count(1) from platform_error_order o inner join shop s on o.shop_id = s.shop_id where s.enabled = 1
and (remark <> 'PDD_DECRYPT_MASK_EXCEPTION' and remark <> 'DATA_TOO_LONG' or  remark is null)";
$platform_error_order_count = $sync_db->getOne($sql);
if ($platform_error_order_count > 50000) {
    send_sms("platform_error_order 错误订单数:{$platform_error_order_count}");
    $is_ok = false;
}

if (isset($argv[1]) && $argv[1] == 'is_ok' && $is_ok) {
    send_sms("一切正常");
}

echo "[] " . date("Y-m-d H:i:s")." send_msg is_ok:{$is_ok}".PHP_EOL;


$sql = "select count(*) from export_center where status = 'CHECKING' and retry_count > 1  and created_time < date_sub(now(), interval 15 minute) and checking_time >= curdate()";
$export_center = $db_user->getOne($sql);
if ($export_center>0) {
    send_sms("ERP报警 export_center 有{$export_center}个任务重试了3次",'13567177855');
}

$sql = "select count(*) count,group_concat(distinct type) as types from import_center 
         where status != 'DONE' and created_time < date_sub(now(), interval 10 minute) 
             and created_time >= curdate() 
            and ((result not like '%access_token已过期%' and result not like '%调用过于频繁%' 
            and result not like '%店铺ID号不存在%' and result not like '%面单已经被揽收、签收、或回收%') or result is null) 
            and type in ('sync_shop_order','sync_shop_goods','update_unprint_shipment_shipping','update_unprint_shipment_tactics','order_weight_update','update_erp_goods_alias','update_erp_sku_alias','sync_order_newShop','recycle_mailnos','sync_sku_inventory','inventory_change_list')";
$import_center = $db_user->getAll($sql);
if ($import_center[0]['count']>0) {
    send_sms("ERP报警 import_center 10分钟之前{$import_center[0]['types']}的还有{$import_center[0]['count']}个未完成",'13567177855,13615811405');
}

$sql = "select count(*) count,group_concat(distinct type) as types from import_center 
         where status != 'DONE' and created_time < date_sub(now(), interval 10 minute) 
             and created_time >= curdate() 
            and (type != 'refresh_route_facility_id' or  count_sum > count_success+count_fail)
            and type in ('goods_sku_alias_update','inventory_quantity_manage','purchase_price','package_fee','weight','refresh_route_facility_id','shop_data')";
$import_center = $db_user->getAll($sql);
if ($import_center[0]['count']>0) {
    send_sms("ERP报警 import_center 10分钟之前{$import_center[0]['types']}的还有{$import_center[0]['count']}个未完成",'13567177855');
}

$sql = "select count(*) count,group_concat(distinct type) as types from import_center 
         where status != 'DONE' and created_time < date_sub(now(), interval 10 minute) 
             and created_time >= curdate() 
            and type like 'finance_update%' ";
$import_center = $db_user->getAll($sql);
if ($import_center[0]['count']>0) {
    send_sms("ERP报警 import_center 10分钟之前{$import_center[0]['types']}的还有{$import_center[0]['count']}个未完成",'13567177855,15857139821');
}

if(date("Y-m-d H:i:s") > date("Y-m-d 02:00:00")){
    $sql = "select count(*) from import_center
        where status = 'DONE' 
        and created_time >= curdate() 
        and type = 'move_mailnos_data_to_history_db'";
    $import_center = $db_user->getOne($sql);
    if($import_center != 256){
        send_sms("ERP报警 import_center mailnos 数据迁移至历史库错误", '13567177855,15272027675');
    }

    $sql = "select count(*) from import_center
        where status = 'DONE' 
        and created_time >= curdate() 
        and type = 'move_shipment_package_data_to_history_db'";
    $import_center = $db_user->getOne($sql);
    if($import_center != 256){
        send_sms("ERP报警 import_center shipment_package 数据迁移至历史库错误", '13567177855,15272027675');
    }
}

if(date("Y-m-d H:i:s") > date("Y-m-d 04:00:00")){
    $sql = "select count(*) from import_center
        where status = 'DONE' 
        and created_time > DATE_SUB(CURDATE(),interval 120 minute)
        and type = 'move_print_log_data_to_history_db'";
    $import_center = $db_user->getOne($sql);
    if($import_center != 256){
        send_sms("ERP报警 import_center print_log 数据迁移至历史库错误", '13567177855,15272027675');
    }

    $sql = "select count(*) from import_center
        where status = 'DONE' 
        and created_time > DATE_SUB(CURDATE(),interval 120 minute)
        and type = 'move_shipment_data_to_history_db'";
    $import_center = $db_user->getOne($sql);
    if($import_center != 256){
        send_sms("ERP报警 import_center shipment 数据迁移至历史库错误", '13567177855,15272027675');
    }
}

selectRds('piece_1', 'erp_182');
$sql = "select count(*) from finance_detail where sync_update_status = 'WAIT_UPDATE' and created_time < date_sub(now(), interval 5 minute) ";
$finance_detail_count = $db->getOne($sql);
if ($finance_detail_count > 1000) {
    send_sms("ERP报警 erp_182.finance_detail 5 分钟之前的还有{$finance_detail_count}条未更新",'13567177855,15857139821');
}

global $oms_sync_db;
$oms_is_ok = true;
$oms_sync_db_conf = array(
    "host" => "100.65.2.110:32057",
    "user" => "mddomsapi_sync",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomssync"
);
$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);
$sql = "select 
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_1))/60) as last_plan_sync_time_1_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_2))/60) as last_plan_sync_time_2_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_3))/60) as last_plan_sync_time_3_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_4))/60) as last_plan_sync_time_4_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_5))/60) as last_plan_sync_time_5_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_6))/60) as last_plan_sync_time_6_dalay
    from 
        platform_extension 
    where 
        (last_plan_sync_time_1 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_2 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_3 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_4 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_5 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_6 < date_sub(now(), interval 3 minute)) and platform_name in ('pinduoduo') and type='order'";
$platform_extension_delay = $oms_sync_db->getRow($sql);
if ($platform_extension_delay) {
    $msg = " last_plan_sync_time_";
    if ($platform_extension_delay['last_plan_sync_time_1_dalay'] > 3) {
        $msg .= " 1延迟{$platform_extension_delay['last_plan_sync_time_1_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_2_dalay'] > 3) {
        $msg .= " 2延迟{$platform_extension_delay['last_plan_sync_time_2_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_3_dalay'] > 3) {
        $msg .= " 3延迟{$platform_extension_delay['last_plan_sync_time_3_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_4_dalay'] > 3) {
        $msg .= " 4延迟{$platform_extension_delay['last_plan_sync_time_4_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_5_dalay'] > 3) {
        $msg .= " 5延迟{$platform_extension_delay['last_plan_sync_time_5_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_6_dalay'] > 3) {
        $msg .= " 6延迟{$platform_extension_delay['last_plan_sync_time_6_dalay']}分钟 ";
    }
    send_sms("oms platform_extension_pdd {$msg}");
    $oms_is_ok = false;
}

$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);
$sql = "select 
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_1))/60) as last_plan_sync_time_1_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_2))/60) as last_plan_sync_time_2_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_3))/60) as last_plan_sync_time_3_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_4))/60) as last_plan_sync_time_4_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_5))/60) as last_plan_sync_time_5_dalay,
        ceil((unix_timestamp() - unix_timestamp(last_plan_sync_time_6))/60) as last_plan_sync_time_6_dalay
    from 
        platform_extension 
    where 
        (last_plan_sync_time_1 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_2 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_3 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_4 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_5 < date_sub(now(), interval 3 minute) or 
        last_plan_sync_time_6 < date_sub(now(), interval 3 minute)) and platform_name in ('taobao') and type='order'";
$platform_extension_delay = $sync_db->getRow($sql);
if ($platform_extension_delay) {
    $msg = " last_plan_sync_time_";
    if ($platform_extension_delay['last_plan_sync_time_1_dalay'] > 3) {
        $msg .= " 1延迟{$platform_extension_delay['last_plan_sync_time_1_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_2_dalay'] > 3) {
        $msg .= " 2延迟{$platform_extension_delay['last_plan_sync_time_2_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_3_dalay'] > 3) {
        $msg .= " 3延迟{$platform_extension_delay['last_plan_sync_time_3_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_4_dalay'] > 3) {
        $msg .= " 4延迟{$platform_extension_delay['last_plan_sync_time_4_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_5_dalay'] > 3) {
        $msg .= " 5延迟{$platform_extension_delay['last_plan_sync_time_5_dalay']}分钟 ";
    }
    if ($platform_extension_delay['last_plan_sync_time_6_dalay'] > 3) {
        $msg .= " 6延迟{$platform_extension_delay['last_plan_sync_time_6_dalay']}分钟 ";
    }
    send_sms("oms platform_extension_taobao {$msg}");
    $oms_is_ok = false;
}

$sql = "select count(1) from platform_error_order where remark <> 'PDD_DECRYPT_MASK_EXCEPTION' and remark <> 'DATA_TOO_LONG'";
$platform_error_order_count = $oms_sync_db->getOne($sql);
if ($platform_error_order_count > 50000) {
    send_sms("oms platform_error_order 错误订单数:{$platform_error_order_count}");
    $oms_is_ok = false;
}

$sql = "select count(*) from shop_extension 
where enabled = 1 
and shop_mod < 200 and last_plan_sync_time < date_sub(now(), interval 15 minute) 
and platform_code not in ('taobao','pinduoduo')";
$shop_extension_delay = $oms_sync_db->getOne($sql);
if ($shop_extension_delay > 0) {
    send_sms("oms shop_extension 5分钟延迟{$shop_extension_delay}家");
    $oms_is_ok = false;
}

global $oms_oms_user_db;
$oms_oms_user_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomsuser"
);
$oms_user_db = ClsPdo::getInstance($oms_oms_user_db_conf);

$sql = "select count(1) from origin_transform_error_order o
inner join shop s on o.shop_id = s.shop_id
where s.is_sync = 1 and s.enabled = 1 and o.transform_level <> 7 and o.created_time < date_sub(now(), interval 5 minute)";
$transform_delay = $oms_user_db->getOne($sql);
if ($transform_delay > 5000) {
    send_sms("oms 转化 剩余{$transform_delay}单未转");
    $oms_is_ok = false;
}

if (isset($argv[1]) && $argv[1] == 'is_ok' && $oms_is_ok) {
    send_sms("oms sync 一切正常");
}


function send_sms($msg, $mobile = '13567177855') {
    global $sync_db;
    $sql = "INSERT into send_msg (receiver_mobiles, msg) VALUES ('{$mobile}','{$msg}');";
    $sync_db->query($sql);
    echo "[] " . date("Y-m-d H:i:s")." send_msg sql:{$sql}".PHP_EOL;
}
