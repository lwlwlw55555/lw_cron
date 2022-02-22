<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$time1 = date("Y-m-d H:i:s");
echo(date("Y-m-d H:i:s") . " send report begin \r\n");
$query_start_date = date("Y-m-d",strtotime("-1 day"));
if (isset($opt_params['start'])){
    $is_date = strtotime($opt_params['start']) ? strtotime($opt_params['start']) : false;
    if($is_date){
        $query_start_date = $opt_params['start'];
    }
}
$report_file_name = $query_start_date."ERP付费数据日报";
echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_data = array();
$i = 0;
$query_end_date = date("Y-m-d",strtotime("+1 day",strtotime($query_start_date)));
$report_data[$i]['query_date'] = $query_start_date;
//系统免费笔数
$report_data[$i]['free_shop_pay_order'] = getFreeShopPayOrder($query_start_date, $query_end_date);
//系统付费笔数
$report_data[$i]['paid_shop_pay_order'] = getPaidShopPayOrder($query_start_date, $query_end_date);
//系统订购金额
$report_data[$i]['shop_pay_order_amount'] = getShopPayOrderAmount($query_start_date, $query_end_date);
//服务市场免费笔数
$report_data[$i]['free_platform_pay_order'] = getFreePlatformPayOrder($query_start_date, $query_end_date);
//服务市场付费笔数
$report_data[$i]['paid_platform_pay_order'] = getPaidPlatformPayOrder($query_start_date, $query_end_date);
//服务市场订购金额
$report_data[$i]['shop_platform_order_amount'] = getPlatformPayOrderAmount($query_start_date, $query_end_date);
//退款笔数
$report_data[$i]['refund_pay_order'] = getRefundOrderFromPayOrder($query_start_date, $query_end_date);
//退款金额
$report_data[$i]['refund_amount'] = getRefundAmountFromPayOrder($query_start_date, $query_end_date);
//收入金额
$report_data[$i]['income'] = $report_data[$i]['shop_pay_order_amount'] + $report_data[$i]['shop_platform_order_amount'] - $report_data[$i]['refund_amount'];
$i++;
echo date("Y-m-d H:i:s")." query end query_start_date = ".$query_start_date." query_end_date = ".$query_end_date.PHP_EOL;


$key_array = array('query_date','free_shop_pay_order','paid_shop_pay_order','shop_pay_order_amount','free_platform_pay_order','paid_platform_pay_order','shop_platform_order_amount',
    'refund_pay_order','refund_amount','income');
$header = array(array("统计时间","系统免费笔数","系统付费笔数","系统订购金额", "服务市场免费笔数","服务市场付费笔数","服务市场订购金额",'退款笔数','退款金额','收入金额'));
send_erp_email($report_file_name,$header,$key_array, $report_data);

$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);

echo PHP_EOL;
echo(date("Y-m-d H:i:s") . " send report end \r\n");

