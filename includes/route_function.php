<?php
/**
 * @param $start_date
 * @return mixed
 * 统计每天的情况
 */
function getDayCount($start_date,$shipping_id){
	global $route_drds_db;
	$sql = "
		select count(1) count,logistic_status,date(shipped_time) day
		from mailno_dispatch_info
		where shipped_time > '{$start_date}'
		and shipping_id = {$shipping_id}
		group by logistic_status,day
	";
	return $route_drds_db->getAll($sql);
}

/**
 * @return mixed
 * 统计快递接口成功率
 */
function getQueryCount(){
	global $route_drds_db;
	$sql = "
		select shipping_id,sum(query_times) count,sum(failed_times) failed_count
		from mailno_dispatch_info 
		where query_type = 'express' 
		and shipping_id in (1,2,3,4,5,9,10,16,17,97)
		group by shipping_id
	";
	return $route_drds_db->getAll($sql);
}

/**
 * @return mixed
 * 统计快递订阅成功数
 */
function getRegisterCount(){
	global $route_drds_db;
	$sql = "
		select shipping_id,sum(register_status) success_count,COUNT(1) count
		from mailno_dispatch_info
		where register_type is not NULL
		and shipping_id in (1,2,4,5,10,17)
		group by shipping_id,register_type
	";
	return $route_drds_db->getAll($sql);
}

/**
 * @return mixed
 * 统计揽件比发货早的单量
 */
function getShippedDelay(){
	global $route_drds_db;
	$sql = "
		select sum(if(shipped_time>accept_time,1,0)) before_accept,sum(if(shipped_time>station_time,1,0)) before_station,sum(if(accept_time is null,0,1)) cou
		from mailno_dispatch_info 
	";
	return $route_drds_db->getRow($sql);
}

/**
 * @return mixed
 * 统计及时率
 */
function getRegisterPromptness(){
	global $route_drds_db;
	$sql = "
		select shipping_id,
		avg(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time)) avg,
		count(1) count,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) < 30 ,1,0)) 30_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) >= 30 and TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) < 60 ,1,0)) 60_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) >= 60 and TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) < 120 ,1,0)) 120_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) >= 120 and TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) < 240 ,1,0)) 240_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),register_accept_time) >= 240 ,1,0)) after_240_minute
		from mailno_dispatch_info
		where shipping_id in (1,4,5,10,2,17) 
		and register_accept_time is not null
		group by shipping_id 
	";
	return $route_drds_db->getAll($sql);	
}

/**
 * @return mixed
 * 统计查询及时率
 */
function getQueryPromptness(){
	global $route_drds_db;
	$sql = "
		select shipping_id,
		avg(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time)) avg,
		count(1) count,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) < 30 ,1,0)) 30_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) >= 30 and TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) < 60 ,1,0)) 60_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) >= 60 and TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) < 120 ,1,0)) 120_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) >= 120 and TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) < 240 ,1,0)) 240_minute,
		sum(if(TIMESTAMPDIFF(minute,if(shipped_time>accept_time,shipped_time,accept_time),query_accept_time) >= 240 ,1,0)) after_240_minute
		from mailno_dispatch_info
		where shipping_id in (2,3,5,17) 
		and query_accept_time is not null
		group by shipping_id
	";
	return $route_drds_db->getAll($sql);	
}

/**
 * @param $numerator 分子
 * @param $Denominator 分母
 * @return string
 * 获取百分比
 */
function getRate($numerator,$denominator){

	return $denominator == null || $denominator == 0 ? 0:getRateFormat($numerator*100/$denominator);
}

/**
 * @param $content
 * @return string
 * 获取内容的百分比
 */
function getRateFormat($content){
	return sprintf("%01.2f", $content);
}

//获取intvalue的值
function getIntValue($intValue){
	return $intValue == null || $intValue == 0 ? ' 0':$intValue;
}

//获取两个时间的天数差
function getDiffDays($startDay,$endDay){
	$startDay = strtotime($startDay);
	$endDay = strtotime($endDay);
	return ceil(($endDay-$startDay)/3600/24);
}
?>
