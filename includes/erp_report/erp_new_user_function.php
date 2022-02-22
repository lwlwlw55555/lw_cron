<?php
/* 查询erp新用户数据 */

/**
 * 获取新用户数
 */
function getNewUser($start_date, $end_date, $created_from = ''){
    $sql_where = '';
    if ( !empty($created_from)){
        $sql_where = " and u.created_from = '{$created_from}'";
    }
    global $db_user;
    $sql = "
		select 
			count(DISTINCT u.user_id)
		from 
			user u
		where 
			u.created_time >= '{$start_date}'
			and u.created_time < '{$end_date}'
			{$sql_where}
	";
    //echo 'getNewUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取新店铺数
 */
function getNewShop($start_date, $end_date,$platform_shop_ids =null){
    global $db_user;
    $sql = "
		select 
			count(DISTINCT platform_shop_id)
		from 
			shop_oauth
		where 
			created_time >= '{$start_date}'
			and created_time < '{$end_date}'
	";
    if($platform_shop_ids != null){
        $sql .= " and platform_shop_id in ('{$platform_shop_ids}') ";
    }
    echo 'getNewShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取新增试用用户数
 * 新增用户中，支付金额 = 0 的账户数
 */
function getNewAndBuyFreeUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select 
            count(distinct t.user_id)
        from 
            (
                select 
                    user_id
                from 
                    user u
                where 
                    u.created_time >= '{$start_date}'
                    and u.created_time < '{$end_date}'
            ) as t    
            left join pay_order p on p.user_id = t.user_id AND p.pay_amount > 0 AND p.pay_status = 'PS_PAYED' and pay_time < '{$end_date}'
        where 
            p.user_id is null 
    ";
    echo 'getNewAndBuyFreeUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 新增用户留存数
 * 新增的用户 某天登录的有多少
 */
function getKeepActiveNewUser($start_date, $end_date, $login_date, $created_from = ''){
    $sql_where = '';
    if ( !empty($created_from)){
        $sql_where = " and u.created_from = '{$created_from}'";
    }
    global $db_user;
    $sql = "
		select 
			count(DISTINCT u.user_id)
		from 
			user u
			inner join session_date sd on sd.user_id = u.user_id and session_date = '{$login_date}'
		where 
			u.created_time >= '{$start_date}'
			and u.created_time < '{$end_date}'
			{$sql_where}
	";

   // echo 'getKeepActiveNewUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}


/**
 * 获取设置了发货地的新用户数
 */
function getSetAddressNewUser($start_date, $end_date, $created_from = ''){
    $sql_where = '';
    if ( !empty($created_from)){
        $sql_where = " and u.created_from = '{$created_from}'";
    }
    global $db, $db_user;

    $user_sql = "
        select
            u.user_id,
            u.rds,
            u.db,
            u.facility_id 
        from 
            user u 
        where 
            u.created_time >= '{$start_date}'and 
            u.created_time < '{$end_date}'
            {$sql_where}
    ";
    $user_list = $db_user->getAll($user_sql);
    $rds_list = getFacilityIdListByRds($user_list);

    $result = 0;
    foreach ($rds_list as $user_rds => $db_list) {
        foreach ($db_list as $user_db => $facility_ids) {
            selectRds($user_rds, $user_db);
            $facility_ids = implode("','", $facility_ids);
            $count = $db->getOne("select count(DISTINCT facility_id) from facility_address where facility_id in ('{$facility_ids}') and created_time < '{$end_date}'");
            $result += $count;
        }
    }
    return $result;
}
/**
 * 获取设置了快递的新用户数
 */
function getSetShippingNewUser($start_date,$end_date,$created_from = ''){
    $sql_where = '';
    if ( !empty($created_from)){
        $sql_where = " and u.created_from = '{$created_from}'";
    }
    global $db,$db_user;
    $user_sql = "
        select
            u.user_id,
            u.rds,
            u.db,
            u.facility_id 
        from 
            user u 
        where 
            u.created_time >= '{$start_date}'
			and u.created_time < '{$end_date}'
			{$sql_where}";
    $user_list = $db_user->getAll($user_sql);
    $rds_list = getFacilityIdListByRds($user_list);

    $result = 0;
    foreach ($rds_list as $user_rds => $db_list) {
        foreach ($db_list as $user_db => $facility_ids) {
            selectRds($user_rds, $user_db);
            $facility_ids = implode("','", $facility_ids);
            $count = $db->getOne("select count(DISTINCT facility_id) from facility_shipping where facility_id in ('{$facility_ids}') and created_time < '{$end_date}'");
            $result += $count;
        }
    }
    return $result;
}

/**
 *  获取新用户当日打单的用户
 */
function getPrintNewUser($start_date,$end_date, $created_from = ''){
    $facility_ids = getNewUserFacilityIds($start_date,$end_date, $created_from);
    if(empty($facility_ids)){
        return 0;
    }
    global $db_user;
    $sql = "
		select
			count(DISTINCT facility_id)
		from
			shop_data 
		where
		    print_count > 0
			AND facility_id in ({$facility_ids})
			AND count_date < '{$end_date}'
			AND count_date >= '{$start_date}'
	";
    echo 'getPrintNewUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取新用户订单数
 */
function getNewUserOrder($start_date,$end_date){
    $facility_ids = getNewUserFacilityIds($start_date,$end_date);
    if(empty($facility_ids)){
        return 0;
    }
    global $db_user;
    $sql = "
		select 
			sum(order_count)
		from 
			shop_data 
		where 
			facility_id in ({$facility_ids})
			AND count_date >= '{$start_date}'
			and count_date < '{$end_date}'
	";
    echo 'getNewUserOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}
/**
 * 获取新用户打单数
 */
function getNewUserPrintOrder($start_date,$end_date){
    $facility_ids = getNewUserFacilityIds($start_date,$end_date);
    if(empty($facility_ids)){
        return 0;
    }
    global $db_user;
    $sql = "
		select 
			sum(print_count)
		from 
			shop_data 
		where 
			facility_id in ({$facility_ids})
			AND count_date >= '{$start_date}'
			and count_date < '{$end_date}'
	";

    return $db_user->getOne($sql);
}
/**
 * 获取新增用户的 facilityId
 */
function getNewUserFacilityIds($start_date, $end_date, $created_from = ''){
    $sql_where = '';
    if ( !empty($created_from)){
        $sql_where = " and u.created_from = '{$created_from}'";
    }
    global $db_user;
    $sql = "
        select
            u.facility_id 
        from 
            user u 
        where 
            u.created_time >= '{$start_date}'
			and u.created_time < '{$end_date}'
			{$sql_where}";
    $result = $db_user->getCol($sql);
    $facility_ids = array();
    if(empty($result)){
        return $facility_ids;
    }
    $facility_ids = implode(",", $result);
    return $facility_ids;
}

