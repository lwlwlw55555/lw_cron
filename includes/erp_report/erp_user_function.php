<?php
/*查询erp用户日常信息*/

function getPlatformShopIds($end_date,$platform_name )
{
    global $sync_db;
    $sql = "
		select 
		    distinct platform_shop_id
        from 
             shop
        where
              created_time < '{$end_date}'  ";
    if ($platform_name != null) {
        $sql .= " and platform_name =  '{$platform_name}' ";
    }
    return array_column($sync_db->getAll($sql), 'platform_shop_id');
}     
/**
 * 获取总用户数
 */
function getAllUser($end_date,$platform_name = null){
	global $sync_db;
	$sql = "
		select 
            count( distinct default_facility_id)
        from 
            shop
        where
			created_time < '{$end_date}'
	";
    if($platform_name != null){
        $sql .= " and platform_code = '{$platform_name}' ";
    }
	echo 'getAllUser'.PHP_EOL.$sql.PHP_EOL;
	return $sync_db->getOne($sql);
}

/**
 * 获取有效用户数
 */
function getEffectiveUser($start_date, $end_date,$platform_name = null){
	global $db_user,$sync_db;
	$sql = "
		select 
            platform_shop_id
        from 
            shop_pay_order
        where
            pay_status = 'PS_PAYED'
            and refund_status = 'COMMON'
            and effective_start_time < '{$end_date}'
            and effective_end_time >= '{$start_date}'     
	";
	$platform_shop_id_list = $db_user->getAll($sql);
    echo 'getEffectiveShop'.PHP_EOL.$sql.PHP_EOL;
    $platform_shop_ids = implode(',',array_column($platform_shop_id_list,'platform_shop_id'));
    $sql = "
		select 
            count( distinct default_facility_id)
        from 
            shop
        where
            platform_shop_id in ($platform_shop_ids)";
    if($platform_name != null){
        $sql .= " and platform_code = '{$platform_name}' ";
    }
    echo 'getEffectiveUser'.PHP_EOL.$sql.PHP_EOL;
	return $sync_db->getOne($sql);
}

function getEffectiveUserOther($query_end_date,$platform_shop_ids){
    global $db_user;
    $sql = "
		select 
           count(DISTINCT facility_id) 
        from 
            shop_oauth
        where expire_time > '{$query_end_date}'    
            and platform_shop_id in ('{$platform_shop_ids}')    
	";
    return $db_user->getOne($sql);
}

/**
 * 获取活跃用户数
 * （时间段登录过的用户数）
 */
function getNotLoginUser($end_date){
    global $db_user;
    $sql = "
		select
			count(DISTINCT u.user_id)
		from
		    user u
			left join session_date sd on sd.user_id = u.user_id and sd.session_date >= date(date_sub('{$end_date}',interval 7 day))
			AND sd.session_date < '{$end_date}'
		where
			sd.user_id is null
	";
    echo 'getNotLoginUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}


/**
 * 获取新店铺数
 */
function getNewShopOther($start_date, $end_date,$platform_name){
    global $sync_db;

    $sql = "
		select  
			distinct  default_facility_id
		from 
			shop
		where 
			created_time < '{$start_date}'
			and platform_name = '{$platform_name}'
	";
    $all = $sync_db->getAll($sql);
    
    $sql = "
		select 
			distinct  default_facility_id
		from 
			shop
		where 
			created_time >= '{$start_date}'
			and created_time < '{$end_date}'
			and platform_name = '{$platform_name}'
	";
    echo 'getNewShop'.PHP_EOL.$sql.PHP_EOL;
    return count(array_diff(array_column($sync_db->getAll($sql),'default_facility_id'), array_column($all,'default_facility_id'))) ;
}

/**
 *  获取打单量大于0的用户数
 */
function getPrintUser($start_date,$end_date,$platform_shop_ids = null){
    global $db_user;
    $sql = "
		select
			count(DISTINCT facility_id)
		from
			shop_data 
		where
		    print_count > 0
			AND count_date >= '{$start_date}'
			AND count_date < '{$end_date}'
	";
    echo 'getPrintUser'.PHP_EOL.$sql.PHP_EOL;
    if($platform_shop_ids != null){
        $sql .= " and platform_shop_id in ('{$platform_shop_ids}') ";
    }
    return $db_user->getOne($sql);
}

/**
 * 次日活跃留存人数
 * 某日登入的用户，在第二日仍登录的用户数
 */
function getKeepActiveUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count( DISTINCT sd.user_id)
        from
            session_date sd
            inner join session_date sd1 on sd.user_id = sd1.user_id and sd1.session_date = '{$end_date}'
        where
            sd.session_date = '{$start_date}'
    ";
    echo 'getKeepActiveUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}



/**
 * 获取所有店铺数
 */
function getAllShop($end_date){
	global $sync_db;
	$sql = "
		select
			count(DISTINCT platform_shop_id)
		from
			shop
		where
			created_time < '{$end_date}'
	";
    echo 'getAllShop'.PHP_EOL.$sql.PHP_EOL;
	return $sync_db->getOne($sql);
}
/**
 * 获取有效店铺数
 */
function getEffectiveShop($start_date,$end_date,$platform_name=null){
	global $db_user,$sync_db;
	$sql = "
		select 
            DISTINCT platform_shop_id
        from 
            shop_pay_order
        where
            pay_status = 'PS_PAYED'
            and refund_status = 'COMMON'
            and effective_start_time < '{$end_date}'
            and effective_end_time >= '{$start_date}'    
	";
    echo 'getEffectiveShop'.PHP_EOL.$sql.PHP_EOL;
    $platform_shop_ids = implode(',',array_column($db_user->getAll($sql),'platform_shop_id'));
    $sql = "
		select 
            count( distinct platform_shop_id)
        from 
            shop
        where
            platform_shop_id in ($platform_shop_ids)";
    if($platform_name != null){
        $sql .= " and platform_code = '{$platform_name}' ";
    }
    echo 'getEffectiveShop'.PHP_EOL.$sql.PHP_EOL;
    return $sync_db->getOne($sql);
}
/**
 * 获取有效店铺数
 */
function getEffectiveShopOther($query_end_date,$platform_shop_ids ){
    global $db_user;
    $sql = "
		select 
             count(DISTINCT platform_shop_id)
        from 
            shop_oauth
        where expire_time > '{$query_end_date}'    
            and platform_shop_id in ('{$platform_shop_ids}') 
	";
    echo 'getEffectiveShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}
/**
 * 打单店铺数
 *  获取打单量大于0的店铺
 */
function getPrintShop($start_date,$end_date,$platform_shop_ids=null){
    global $db_user;
    $sql = "
		select
			count(DISTINCT shop_id)
		from
			shop_data 
		where
		    print_count > 0
			AND count_date >= '{$start_date}'
			AND count_date < '{$end_date}'
	";
    if($platform_shop_ids != null){
        $sql .= " and platform_shop_id in ('{$platform_shop_ids}') ";
    }
    echo 'getPrintShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}


/**
 * 获取时间段内打印订单数
 */
function getPrintOrder($start_date,$end_date,$platform_shop_ids=null){
    global $db_user;
    $sql = "
		select 
			sum( print_count)
		from 
			shop_data
		where 
		    count_date >= '{$start_date}'
			AND count_date < '{$end_date}'
	";
    if($platform_shop_ids != null){
        $sql .= " and platform_shop_id in ('{$platform_shop_ids}') ";
    }
    echo 'getPrintOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
* 获取时间段内有效用户总订单量
 */
function getEffectiveUserAllOrder($start_date,$end_date){
    $facility_ids = getEffectiveUserFacilityIds( $start_date, $end_date);
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
			count_date >= '{$start_date}'
			AND count_date < '{$end_date}'
			AND facility_id in ({$facility_ids})
	";
    return $db_user->getOne($sql);
}

/**
 * 获取时间段内总订单量
 */
function getAllOrder($start_date,$end_date,$platform_shop_ids=null){
    global $db_user;
    $sql = "
		select 
			sum(order_count)
		from 
		     shop_data
		where 
			count_date >= '{$start_date}'
			AND count_date < '{$end_date}'
	";
    if($platform_shop_ids != null){
        $sql .= " and platform_shop_id in ('{$platform_shop_ids}') ";
    }
    return $db_user->getOne($sql);
}

/**
 * 获取未登录的有效用户数
 */
function getUnLoginEffectiveUser($start_date, $end_date){
	global $db_user;
	$sql = "
		select
            count(DISTINCT u.user_id)
        from
            pay_order po 
            inner join user u on u.user_id = po.user_id
        where
            po.pay_status = 'PS_PAYED'
            and po.effective_start_time < '{$end_date}'
            and po.effective_end_time >= '{$start_date}'
            and u.last_open_time < '{$start_date}'
	";
	echo 'getUnLoginEffectiveUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取有效用户的facility_ids
 */
function getEffectiveUserFacilityIds( $start_date, $end_date){
    global $db_user;
    $user_sql = "
        select
            u.facility_id
        from
            pay_order po 
            inner join user u on u.user_id = po.user_id
        where
            po.pay_status = 'PS_PAYED'
            and po.effective_start_time < '{$end_date}'
            and po.effective_end_time >= '{$start_date}'
        group by
            u.facility_id 
    ";
    echo 'getEffectiveUserFacilityIds'.PHP_EOL.$user_sql.PHP_EOL;
    $facility_ids = $db_user->getCol($user_sql);
    $facility_ids = implode(",", $facility_ids);
    return $facility_ids;
}






