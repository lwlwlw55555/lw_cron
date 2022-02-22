<?php
require("includes/init.php");
global $db, $db_user, $sync_db;
$shop_count = 0;

$sql = "
    select
        platform_shop_id,
        default_facility_id
    from
        shop
    where 
        default_facility_id = 200004
";
$shop_list = $sync_db->getAll($sql);
foreach ($shop_list as $shop){
    $platform_shop_id = $shop['platform_shop_id'];
    $default_facility_id = $shop['default_facility_id'];
    echo "第 ".$shop_count++."   platform_shop_id = ".$platform_shop_id.PHP_EOL;
    $sql = "
        select
            *
        from
            pay_oauth po 
            inner join user u on u.user_id = po.user_id
        where 
            u.facility_id =  {$default_facility_id}                
    ";
    $pay_oauth = $db_user->getRow($sql);
    insertShopPayOrderByUser($platform_shop_id, $pay_oauth);
}


function insertShopPayOrderByUser($platform_shop_id, $pay_oauth){
    global $db_user;
    $sql = "
        select
            max(effective_end_time) as platform_expire_time
        from
            platform_pay_order
        where 
            platform_shop_id = '{$platform_shop_id}'
    ";
    $platform_expire_time = $db_user->getOne($sql);
    if ( !empty($platform_expire_time)) {
        $effective_start_time = date("Y-m-d", strtotime($pay_oauth['created_time'])) . date(" H:i:s", strtotime($platform_expire_time));
        $effective_end_time = date("Y-m-d", strtotime($pay_oauth['expire_time'])) . date(" H:i:s", strtotime($platform_expire_time));
    } else {
        $effective_start_time = $pay_oauth['created_time'];
        $effective_end_time = $pay_oauth['expire_time'];
    }
    $expire_time = $effective_end_time;

    $user_id = $pay_oauth['user_id'];


    $effective_days = floor( (strtotime($effective_end_time) - strtotime($effective_start_time) ) / 86400);
    $order_sn = getPayOrderSn();
    $sql = "
        insert into 
            shop_pay_order
        set 
            platform_shop_id          = '{$platform_shop_id}',
            user_id                  = {$user_id},
            order_sn                 = '{$order_sn}',
            order_type               = 'PAY_OAUTH_INIT',
            pay_status               = 'PS_PAYED',
            pay_amount               = 0,
            pay_time                 = now(),
            effective_start_time     = '{$effective_start_time}',
            effective_end_time       = '{$effective_end_time}',
            effective_days           = '{$effective_days}',
            note                      = '收费模式改版初始化shop_oauth'
    ";
    $db_user->query($sql);
    $sql = "
        insert into 
            shop_oauth
        set 
            facility_id = {$user_id},
            expire_time = '{$expire_time}',
            platform_shop_id = '{$platform_shop_id}'
    ";
    $db_user->query($sql);

    if ($expire_time > date("Y-m-d H:i:s") && $platform_expire_time > $expire_time && (strtotime($platform_expire_time) - strtotime($expire_time) ) < 90*24*3600 ){
        $sql = "
            insert into 
                free_shop
            set 
                enabled = 1,
                platform_shop_id = '{$platform_shop_id}'
        ";
        $db_user->query($sql);
    }
}

function getPayOrderSn()
{
    return date("YmdHis").rand(10000000, 99999999);
}