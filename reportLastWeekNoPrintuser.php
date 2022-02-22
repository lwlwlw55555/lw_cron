<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
global $db_user;
$last_monday = date('Y-m-d', strtotime('-2 monday', time()));
$last_sunday = date('Y-m-d', strtotime('-1 sunday', time()));
$sql = "
		select 
		    facility_id,
		    count(1) shop_count,
            expire_time  max_expire_time
        from 
            shop_oauth  
        group by facility_id 
        having  max_expire_time > now()
	";
echo 'getLossUser'.PHP_EOL.$sql.PHP_EOL;
$user_list = $db_user->getAll($sql);
foreach ($user_list as $key=>$item){
    $sql = "
		select 
			count(1)
		from 
		    shop_data
		where 
		    print_count > 0
            and facility_id = {$item['facility_id']}
            and created_time >= date(date_sub('{$last_monday}',interval 7 day)) and  created_time < '{$last_monday}'
	";
    $print_count = $db_user->getOne($sql);
    if($print_count == 0){
        continue;
    }
    $sql = "
		select 
			count(1)
		from 
		    shop_data
		where 
		    print_count > 0
            and facility_id = {$item['facility_id']}
            and created_time >= '{$last_monday}' and  created_time <= '{$last_sunday}'
	";
    $print_count = $db_user->getOne($sql);
    if($print_count != 0){
        continue;
    }
    $sql = "
		select 
			user_name,created_time,last_login_time 
		from 
			user
		where 
            facility_id = {$item['facility_id']}
	";
    $user = $db_user->getAll($sql)[0];
    $report_data[$key]['user_name'] = $user['user_name'];
    $report_data[$key]['shop_count'] = $item['shop_count'];
    $report_data[$key]['created_time'] = $user['created_time'];
    $report_data[$key]['last_login_time'] = $user['last_login_time'];

    $sql = "
		select 
			sum(order_count) order_count,
			sum(print_count) print_count
		from 
		     shop_data
		where 
			count_date >=  date(date_sub(now(),interval 3 day))
			and count_date < date(now())
            and facility_id = {$item['facility_id']}
	";
    $user = $db_user->getAll($sql)[0];
    $report_data[$key]['order_count'] =  $user['order_count'];
    //$report_data[$key]['print_count'] = $user['print_count'];

    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );
    $i = $item['facility_id']%256;
    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $sql = "
		select 
			 sender_mobile
		from 
		     facility_address
		where 
			is_default = 1
            and facility_id = {$item['facility_id']}
	";
    $report_data[$key]['mobile'] =  $erp_ddyun_db->getOne($sql);
}
$key_array = array('user_name','shop_count','created_time','last_login_time','order_count','mobile');
$header = array(array('账号名','绑定店铺数','账号注册时间','最后登入时间','近3天订单量','电话'));
send_erp_email('erp有效数据',$header,$key_array, $report_data,0,array("jpxiang@titansaas.com"));
