<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$time1 = date("Y-m-d H:i:s");
echo(date("Y-m-d H:i:s") . " send report begin \r\n");
$month_begin_date = date("Y-m-01", strtotime('-1 month'));
$month_end_date =  date('Y-m-01');

$month_date = date('Y-m', strtotime('-1 month'));

$report_file_name = $month_date."-鲸灵ERP用户月报";

echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_data = array();
$i = 0;
$query_start_date = $month_begin_date;
$query_end_date = $month_end_date;
$report_data[$i]['query_date'] = $month_date;
//总用户
$report_data[$i]['all_user'] = getAllUser($query_end_date);
//有效用户
$report_data[$i]['effective_user'] = getEffectiveUser( $query_start_date ,$query_end_date);
//有效试用用户
$report_data[$i]['effective_and_not_buy_user'] = getEffectiveAndNotBuyUser($query_end_date, $query_end_date);
//新增用户
$report_data[$i]['new_user'] = getNewUser($query_start_date,$query_end_date);
//新增付费用户
$report_data[$i]['new_buy_user'] = getNewBuyUser($query_start_date, $query_end_date);

//到期用户数
$report_data[$i]['expired_user'] = getExpiredUser($query_start_date, $query_end_date);
//月流失用户
$report_data[$i]['un_login_effective_user'] = getUnLoginEffectiveUser($query_start_date, $query_end_date);
//流失率 月流失用户/有效用户
$report_data[$i]['churn_rate'] = round($report_data[$i]['un_login_effective_user']/$report_data[$i]['effective_user']*100,2)."%";

//免费笔数
$report_data[$i]['free_pay_order'] = getFreePayOrder($query_start_date, $query_end_date);
//付费笔数
$report_data[$i]['paid_pay_order'] = getPaidPayOrder($query_start_date, $query_end_date);
//收入金额
$report_data[$i]['pay_order_amount'] = getPayOrderAmount($query_start_date, $query_end_date);
//客单价
$report_data[$i]['per_customer_transaction'] = round($report_data[$i]['pay_order_amount']/$report_data[$i]['paid_pay_order'] ,2);

//转化率 = 转化用户数/（有效试用用户数 + 转化用户数）
$transform_user = getTransformUser($query_start_date,$query_end_date);
$report_data[$i]['percent_conversion'] = round($transform_user/($transform_user + $report_data[$i]['effective_and_not_buy_user']) * 100, 2)."%";

//续费率 = 续费用户数 / 到期用户数
$renew_user =  getRenewUser($query_start_date, $query_end_date);
$report_data[$i]['renew_rate'] = round($renew_user / $report_data[$i]['expired_user']*100,2)."%";

//复购率 复购用户数/付费用户
$re_purchase_user = getRePurchaseUser($query_start_date,$query_end_date);
$buy_user = getBuyUser($query_start_date,$query_end_date);
$report_data[$i]['re_purchase_rate'] = round($re_purchase_user/$buy_user*100,2)."%";

//有效用户订单总量
$report_data[$i]['effective_user_all_order'] = getEffectiveUserAllOrder($query_start_date,$query_end_date);

//打单率  日打单量/有效用户订单总量
$print_order = getPrintOrder($query_start_date,$query_end_date);
$report_data[$i]['print_rate'] = round($print_order / $report_data[$i]['effective_user_all_order']*100,2)."%";

echo(date("Y-m-d H:i:s") . " query end query_start_date = ".$query_start_date." query_end_date = ".$query_end_date.PHP_EOL);

$key_array = array('query_date','all_user','effective_user','effective_and_not_buy_user',
                    'new_user','new_buy_user','expired_user', 'un_login_effective_user',
                    'churn_rate', 'free_pay_order','paid_pay_order','pay_order_amount', 'per_customer_transaction',
                    'percent_conversion','renew_rate','re_purchase_rate','effective_user_all_order','print_rate'
);
$header = array(array('时间','总用户数','有效用户数','有效试用用户数',
                    '新增用户数', '本月新增付费用户数','到期用户数','月流失用户',
                    '流失率','免费笔数','付费笔数','收入金额','客单价',
                    '转化率','续费率','复购率','有效用户订单总量','打单率'
));
//send_erp_email($report_file_name,$header,$key_array,$report_data);

$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo PHP_EOL;
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");
