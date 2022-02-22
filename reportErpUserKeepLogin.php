<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$time1 = date("Y-m-d H:i:s");
echo(date("Y-m-d H:i:s") . " send report begin \r\n");

$startDayDate = date("Y-m-d",strtotime("-15 day"));
if (isset($opt_params['start'])){
    $is_date = strtotime($opt_params['start']) ? strtotime($opt_params['start']) : false;
    if($is_date){
        $startDayDate = $opt_params['start']; 
    }
}
$endDayDate = date("Y-m-d",strtotime("+15 day",strtotime($startDayDate)));
if (isset($opt_params['end'])){
    $is_date = strtotime($opt_params['end']) ? strtotime($opt_params['end']) : false;
    if($is_date){
        $endDayDate = $opt_params['end'];
    }
}
$report_file_name = $startDayDate."_".$endDayDate."ERP用户留存数据";
echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_date_array = array();
while ( $startDayDate < $endDayDate){
    $report_date_array[] = $startDayDate;
    $startDayDate = date("Y-m-d",strtotime("+1 day",strtotime($startDayDate)));
}
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
foreach ($report_date_array as $query_start_date){
    foreach ($created_from_array as $created_from => $name) {
        $query_end_date = date("Y-m-d", strtotime("+1 day", strtotime($query_start_date)));
        $report_data[$i]['query_date'] = $query_start_date;
        $report_data[$i]['created_from'] = $name;
        //新增用户数
        $report_data[$i]['new_user'] = getNewUser($query_start_date, $query_end_date, $created_from);
        if ( !$report_data[$i]['new_user']) {
            //新增为0
            for ($j = 1 ; $j <= 7; $j++) {
                //j 日留存数
                $report_data[$i]['keep_active_'.$j] = 0;
                // j 日留存率
                $report_data[$i]['rate_'.$j] = "0%";
            }
            //14日留存数
            $report_data[$i]['keep_active_14'] = 0;
            // 14日留存率
            $report_data[$i]['rate_14'] = "0%";

            //14日留存数
            $report_data[$i]['keep_active_30'] = 0;
            // 14日留存率
            $report_data[$i]['rate_30'] = "0%";
        } else {
            for ($j = 1; $j <= 7; $j++) {
                //j 日留存数
                $report_data[$i]['keep_active_' . $j] = getKeepActiveNewUser($query_start_date, $query_end_date, date("Y-m-d", strtotime("+" . $j . " day", strtotime($query_start_date))), $created_from);
                // j 日留存率
                $report_data[$i]['rate_' . $j] = round($report_data[$i]['keep_active_' . $j] / $report_data[$i]['new_user'] * 100, 2) . "%";
            }

            //14日留存数
            $report_data[$i]['keep_active_14'] = getKeepActiveNewUser($query_start_date, $query_end_date, date("Y-m-d", strtotime("+14 day", strtotime($query_start_date))), $created_from);
            // 14日留存率
            $report_data[$i]['rate_14'] = round($report_data[$i]['keep_active_14'] / $report_data[$i]['new_user'] * 100, 2) . "%";

            //14日留存数
            $report_data[$i]['keep_active_30'] = getKeepActiveNewUser($query_start_date, $query_end_date, date("Y-m-d", strtotime("+30 day", strtotime($query_start_date))), $created_from);
            // 14日留存率
            $report_data[$i]['rate_30'] = round($report_data[$i]['keep_active_30'] / $report_data[$i]['new_user'] * 100, 2) . "%";
        }
	    $i++;
    }

    echo(date("Y-m-d H:i:s") . " query end query_start_date = ".$query_start_date." query_end_date = ".$query_end_date.PHP_EOL);
}
$key_array = array('query_date','created_from','new_user','keep_active_1','rate_1',
                    'keep_active_2','rate_2','keep_active_3','rate_3',
                    'keep_active_4','rate_4','keep_active_5','rate_5',
                    'keep_active_6','rate_6','keep_active_7','rate_7',
                    'keep_active_14','rate_14','keep_active_30','rate_30');
$header = array(array("统计时间","渠道","新增用户","1日后留存-用户数", "1日后留存-留存率",
                    "2日后留存-用户数", "2日后留存-留存率","3日后留存-用户数", "3日后留存-留存率",
                    "4日后留存-用户数", "4日后留存-留存率","5日后留存-用户数", "5日后留存-留存率",
                    "6日后留存-用户数", "6日后留存-留存率","7日后留存-用户数", "7日后留存-留存率",
                    "14日后留存-用户数", "14日后留存-留存率","30日后留存-用户数", "30日后留存-留存率",));
send_erp_email($report_file_name,$header,$key_array, $report_data, 0);
echo PHP_EOL;
$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");




