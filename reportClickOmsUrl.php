<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
global $db_user;
$oms_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "name" => "mddomsuser",
    "pconnect" => "1",
);
$oms_db = ClsPdo::getInstance($oms_db_conf);

$sql = "
		select 
		    sc.user_id,u.user_name,sc.created_time,u.facility_id
        from 
            system_click sc
		    inner join user u on sc.user_id = u.user_id
		where sc.type = 'omsUrl' 
        and  sc.created_time > date_sub(current_date,interval 1 day) and  sc.created_time < current_date
        group by sc.user_id;
	";
echo 'getSystemClick'.PHP_EOL.$sql.PHP_EOL;
$user_list = $db_user->getAll($sql);
$key = 0;
foreach ($user_list as $item){

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
    $sender_mobile =  $erp_ddyun_db->getOne($sql);

    $sql = "
		select 
			user_name,mobile,created_time,created_from
		from 
		     user
		where 
			created_from_erp_user_id = {$item['user_id']}
	";
    $oms_user = $oms_db->getAll($sql);
    if(!empty($oms_user) && count($oms_user) > 0){
        foreach ($oms_user as $o => $it){
            $report_data[$key]['created_time'] = $item['created_time'];
            $report_data[$key]['user_name'] = $item['user_name'];
            $report_data[$key]['sender_mobile'] = $sender_mobile;
            $report_data[$key]['oms_created_time'] = $it['created_time'] ;
            $report_data[$key]['oms_user_name'] = $it['user_name'] ;
            $report_data[$key]['oms_mobile'] = $it['mobile'] ;
            $report_data[$key]['created_from'] = $it['created_from'] ;
            $key ++;
        }
    }else{
        $report_data[$key]['created_time'] = $item['created_time'];
        $report_data[$key]['user_name'] = $item['user_name'];
        $report_data[$key]['sender_mobile'] = $sender_mobile;
        $key ++;
    }
}
$key_array = array('created_time','user_name','sender_mobile','oms_created_time','oms_user_name','oms_mobile','created_from');
$header = array(array('点击时间','进销存账号名称','电话','企业版注册时间','企业版注册账号','企业版注册电话','来源'));
send_erp_email('用户点击注册数据',$header,$key_array, $report_data,0,array("jpxiang@titansaas.com", "pxie1@titansaas.com"));
