<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$time1 = date("Y-m-d H:i:s");
echo(date("Y-m-d H:i:s") . " send report begin \r\n");

$startDayDate = date("Y-m-d",strtotime("-1 day"));
if (isset($opt_params['start'])){
    $is_date = strtotime($opt_params['start']) ? strtotime($opt_params['start']) : false;
    if($is_date){
        $startDayDate = $opt_params['start']; 
    }
}
$report_file_name = $startDayDate."ERP新增数据日报";
echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_date_array = array(
    $startDayDate,
    date("Y-m-d",strtotime("-1 day",strtotime($startDayDate)))
);
$report_data = array();
$i = 0;
foreach ($report_date_array as $query_start_date){
    $query_end_date = date("Y-m-d",strtotime("+1 day",strtotime($query_start_date)));
	$report_data[$i]['query_date'] = $query_start_date;
	//新增用户数
	$report_data[$i]['new_user'] = getNewUser($query_start_date, $query_end_date);
	//新增店铺数
	$report_data[$i]['new_shop'] = getNewShop($query_start_date, $query_end_date);
	//新增用户次日留存数
	$report_data[$i]['keep_active_new_user'] = getKeepActiveNewUser($query_start_date, $query_end_date, $query_end_date);
	//设置了发货地的新增用户
	$report_data[$i]['set_address_new_user'] = getSetAddressNewUser($query_start_date, $query_end_date);
	//设置了快递的新增用户
	$report_data[$i]['set_shipping_new_user'] = getSetShippingNewUser($query_start_date, $query_end_date);
	//新增打单用户数
	$report_data[$i]['print_new_user'] = getPrintNewUser($query_start_date, $query_end_date);
	//新增用户当日单量
	$report_data[$i]['new_user_order'] = getNewUserOrder($query_start_date, $query_end_date);
    //新增用户当日打单量
    $report_data[$i]['new_user_print_order'] = getNewUserPrintOrder($query_start_date, $query_end_date);

	$i++;
    echo(date("Y-m-d H:i:s") . " query end query_start_date = ".$query_start_date." query_end_date = ".$query_end_date.PHP_EOL);
}
$key_array = array('query_date','new_user','new_shop','keep_active_new_user','set_address_new_user','set_shipping_new_user','print_new_user','new_user_order','new_user_print_order');
$header = array(array("统计时间","新增用户数","新增店铺数","新增用户次日留存数", "设置了发货地的新增用户","设置了快递的新增用户","新增打单用户数","新增用户当日单量","新增用户当日打单量"));
send_erp_email($report_file_name,$header,$key_array, $report_data);
echo PHP_EOL;
$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");




