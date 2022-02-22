<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
global $db_user;
$sql = "select 
			user_id,
			user_name,
			facility_id,
			created_time
		from 
			user
		where 
			created_time >= date_sub(curdate(),interval 1 day)
			and created_time < curdate()
	";
echo 'getNewShop'.PHP_EOL.$sql.PHP_EOL;
$user_list = $db_user->getAll($sql);
foreach ($user_list as $key=>$item){
    $report_data[$key]['user_name'] = $item['user_name'];
    $sql = "
		select 
			count(facility_id) 
		from 
			shop_oauth
		where 
            facility_id = {$item['facility_id']}
	";
    echo 'getNewShop'.PHP_EOL.$sql.PHP_EOL;
    $report_data[$key]['shop_number'] = $db_user->getOne($sql);
    $report_data[$key]['created_time'] =  $item['created_time'];
    $sql = "
		select 
			count(1)
		from 
		    shop_data
		where 
		    print_count > 0
			and facility_id = {$item['facility_id']}
	";
    $report_data[$key]['is_print'] =  $db_user->getOne($sql)>0?'是':'否';
    $sql = "
		select 
			sum(order_count)
		from 
		     shop_data
		where 
			count_date >= date_sub(curdate(),interval 1 day)
			and count_date < curdate()
            and facility_id = {$item['facility_id']}
	";
    $report_data[$key]['order_count'] =  $db_user->getOne($sql);
    $sql = "
		select 
			sum(print_count)
		from 
		     shop_data
		where 
			count_date >= date_sub(curdate(),interval 1 day)
			and count_date < curdate()
            and facility_id = {$item['facility_id']}
	";
    $report_data[$key]['print_count'] =  $db_user->getOne($sql);
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

$key_array = array('user_name','shop_number','created_time','is_print','order_count','print_count','mobile');
$header = array(array('用户名','绑定店铺数','注册时间','该账号下是否打单记录','订单量','打单量','电话'));
send_erp_email(date("Y-m-d").'erp新用户数据',$header,$key_array, $report_data,0);
