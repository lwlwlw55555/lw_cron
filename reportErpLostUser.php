<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
global $db_user, $sync_db;



$yesterday = date( 'Y-m-d', strtotime( '-1 day', time() ) );
$two_days_ago = date( 'Y-m-d', strtotime( '-2 day', time() ) );
$three_days_ago = date( 'Y-m-d', strtotime( '-3 day', time() ) );
$seven_days_ago = date( 'Y-m-d', strtotime( '-7 day', time() ) );

echo "getLossUser".PHP_EOL;

// 有效并且最后登陆时间在3天前
$sql = "
    select
        u.facility_id,
        count( IF(session_date <= date_sub(now(), interval 4 day), 1, null ) ) pre_4_day,
        count( IF(session_date > date_sub(now(), interval 4 day), 1, null ) ) last_3_day
    from
        session_date sd
        inner join user u on sd.user_id = u.user_id
    where
        sd.session_date >= date_sub( now(), interval 7 day )
    group by
        sd.user_id
    having
        pre_4_day >= 1
        and last_3_day = 0";
$not_login_facility_list = $db_user->getAll( $sql );

$sql = "
    select
        facility_id,
        sum( CASE when count_date = '{$two_days_ago}' THEN order_count ELSE 0 END ) twoDaysOrderCount,
        sum( CASE when count_date = '{$two_days_ago}' THEN print_count ELSE 0 END ) twoDaysPrintCount,
        sum( CASE when count_date = '{$yesterday}' THEN order_count ELSE 0 END ) yesterDayOrderCount,
        sum( CASE when count_date = '{$yesterday}' THEN print_count ELSE 0 END ) yesterDayPrintCount
    from
        shop_data
    where
        count_date >= '{$two_days_ago}'
        and count_date <= '{$yesterday}'
    group by
        facility_id
    having
        twoDaysPrintCount > 0
        and yesterDayPrintCount = 0";
$not_print_facility_list = $db_user->getAll( $sql );

$facility_id_list = array_merge( array_column( $not_login_facility_list, 'facility_id' ), array_column( $not_print_facility_list, 'facility_id' ) );

$report_data = array();
foreach ( $facility_id_list as $facility_id ) {

    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );
    $i = $facility_id%256;
    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $mobile_sql = "
		select 
			 sender_mobile
		from 
		     facility_address
		where 
			is_default = 1
            and facility_id = {$facility_id}
	";

    $sql = "
		select 
			user_name,
		    created_time,
		    last_login_time 
		from 
			user
		where 
            facility_id = {$facility_id}
	";
    $user = $db_user->getAll($sql)[0];
    $report_data[$facility_id]['user_name'] = $user['user_name'];
    $report_data[$facility_id]['mobile'] = $erp_ddyun_db->getOne($mobile_sql);
    $report_data[$facility_id]['created_time'] = $user['created_time'];

    $sql = "
        select 
            count(1) 
        from
            shop
        where
            default_facility_id = ${facility_id}
    ";
    $report_data[$facility_id]['all_shop_count'] = $sync_db->getOne( $sql );

    $sql = "
        select
            facility_id,
            sum( order_count ) order_count,
            sum( print_count ) print_count
        from
            shop_data
        where
            facility_id = {$facility_id}
            and count_date >= '{$seven_days_ago}'
        group by
            facility_id
    ";
    $shop_data = $db_user->getAll( $sql )[0];

    $sql = "
        select
            facility_id,
            count( CASE when count_date = '{$two_days_ago}' THEN 1 ELSE null END ) shopCount,
            sum( CASE when count_date = '{$two_days_ago}' THEN order_count ELSE 0 END ) twoDaysOrderCount,
            sum( CASE when count_date = '{$two_days_ago}' THEN print_count ELSE 0 END ) twoDaysPrintCount,
            sum( CASE when count_date = '{$yesterday}' THEN order_count ELSE 0 END ) yesterdayOrderCount,
            sum( CASE when count_date = '{$yesterday}' THEN print_count ELSE 0 END ) yesterdayPrintCount
        from
            shop_data
        where
            count_date >= '{$two_days_ago}'
            and count_date <= '{$yesterday}'
            and facility_id = {$facility_id}
        group by
            facility_id";
    $user_shop_data = $db_user->getAll( $sql )[0];
    $report_data[$facility_id]['shop_count'] = $user_shop_data['shopCount'];
    $report_data[$facility_id]['seven_days_order_count'] = $shop_data['order_count'];
    $report_data[$facility_id]['seven_days_print_count'] = $shop_data['print_count'];
    $report_data[$facility_id]['two_days_order_count'] = $user_shop_data['twoDaysOrderCount'];
    $report_data[$facility_id]['two_days_print_count'] = $user_shop_data['twoDaysPrintCount'];
    $report_data[$facility_id]['yesterday_order_count'] = $user_shop_data['yesterdayOrderCount'];
    $report_data[$facility_id]['yesterday_print_count'] = $user_shop_data['yesterdayPrintCount'];

    $sql = "
        select 
            count(1)
        from
            session_date sd 
            inner join user u on sd.user_id = u.user_id
        where
            sd.session_date >= date_sub( now(), interval 7 day )  
            and u.facility_id = {$facility_id}
    ";
    $report_data[$facility_id]['last_7_login_days'] = $db_user->getOne( $sql );
    $sql = "
        select 
            session_date
        from
            session_date sd 
            inner join user u on sd.user_id = u.user_id
        where
            u.facility_id = {$facility_id}
        order by
            session_date desc
    ";
    $report_data[$facility_id]['last_login_time'] = $db_user->getOne( $sql );
}
echo date("Y-m-d H:i:s")." get Lost User query end ".PHP_EOL;

$key_array = array(
    'user_name', 'mobile', 'created_time', 'all_shop_count', 'shop_count', 'seven_days_order_count', 'seven_days_print_count', 'two_days_order_count', 'two_days_print_count', 'yesterday_order_count', 'yesterday_print_count', 'last_7_login_days', 'last_login_time'
);
$header = array(array(
    '账户名', '手机号', '注册时间', '总店铺数', '有效店铺数', '近7日订单量', '近7日打单量', '前日订单量', '前日打单量', '昨日订单量', '昨日打单量', '近7天登录天数', '最后登录时间'
));
send_erp_email("【进销存】每日流失用户报表",$header,$key_array, $report_data, 0, array("erp@titansaas.com"));

echo PHP_EOL;


