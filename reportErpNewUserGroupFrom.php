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

$report_file_name = $startDayDate."ERP新增日报分来源统计";
echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_date_array = array(
    $startDayDate,
    date("Y-m-d",strtotime("-1 day",strtotime($startDayDate)))
);

$created_from_array = array(
    ""                  => "小计",
    "other"             => "其他来源",
    "dddd_menu"         => "多多打单菜单",
    "dddd_topbtn"       => "多多打单顶部升级按钮",
    "dddd_hideUrl"      => "多多打单弹窗和群链接",
    "dddd_system"       => "迁移时系统创建",
	"served_market"	    => "服务市场",
    "register_active"   => "用户推荐"
);
$report_data = array();
$i = 0;
foreach ($report_date_array as $query_start_date) {
    foreach ($created_from_array as $created_from => $name) {
        $query_end_date = date("Y-m-d", strtotime("+1 day", strtotime($query_start_date)));
        $report_data[$i]['query_date'] = $query_start_date;
        $report_data[$i]['created_from'] = $name;
        //新增用户数
        $report_data[$i]['new_user'] = getNewUser($query_start_date, $query_end_date, $created_from);
        //设置了发货地的新增用户
        $report_data[$i]['set_address_new_user'] = getSetAddressNewUser($query_start_date, $query_end_date, $created_from);
        //设置了快递的新增用户
        $report_data[$i]['set_shipping_new_user'] = getSetShippingNewUser($query_start_date, $query_end_date, $created_from);
        //新增打单用户数
        $report_data[$i]['print_new_user'] = getPrintNewUser($query_start_date, $query_end_date, $created_from);
        //新增用户次日留存数
        $report_data[$i]['keep_active_new_user'] = getKeepActiveNewUser($query_start_date, $query_end_date, $query_end_date, $created_from);
        $i++;
    }
}


$key_array = array('query_date','created_from','new_user','set_address_new_user','set_shipping_new_user','print_new_user','keep_active_new_user');
$header = array(array("统计时间","渠道","新增用户数","设置了发货地的新增用户","设置了快递的新增用户","新增打单用户数","新增用户次日留存数"));
send_erp_email($report_file_name,$header,$key_array, $report_data);
echo PHP_EOL;
$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");




