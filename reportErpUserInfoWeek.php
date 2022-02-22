<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$time1 = date("Y-m-d H:i:s");
echo(date("Y-m-d H:i:s") . " send report begin \r\n");
$query_start_date = date("Y-m-d",strtotime("-7 day"));
if (isset($opt_params['start'])){
    $is_date = strtotime($opt_params['start']) ? strtotime($opt_params['start']) : false;
    if($is_date){
        $query_start_date = $opt_params['start'];
    }
}
$query_end_date = date("Y-m-d",strtotime("+6 day",strtotime($query_start_date)));

$report_file_name = $query_start_date."_".$query_end_date."麦得多erp数据周报";
echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_data = array();
$i = 0;
$report_data[$i]['query_date'] = $query_start_date."_".$query_end_date;
$query_end_date = date("Y-m-d",strtotime("+1 day",strtotime($query_end_date)));
//总用户数
$report_data[$i]['all_user'] = getAllUser($query_end_date);
//有效用户数
$report_data[$i]['effective_user'] = getEffectiveUser( $query_start_date, $query_end_date);
//新增用户数
$report_data[$i]['new_user'] = getNewUser($query_start_date,$query_end_date);
//总店铺数
$report_data[$i]['all_shop'] = getAllShop($query_end_date);
//有效店铺数
$report_data[$i]['effective_shop'] = getEffectiveShop($query_start_date,$query_end_date);
//新增店铺数
$report_data[$i]['new_shop'] = getNewShop($query_start_date, $query_end_date);
//付费店铺数
$report_data[$i]['effective_paid_shop'] = getEffectivePaidShop($query_start_date, $query_end_date);
//试用店铺数
$report_data[$i]['have_free_order_shop'] = getHaveFreeOrderShop($query_end_date);
//免费笔数
$report_data[$i]['free_pay_order'] = getFreeShopPayOrder($query_start_date, $query_end_date) + getFreePlatformPayOrder($query_start_date, $query_end_date); 
//付费笔数
$report_data[$i]['paid_pay_order'] = getPaidShopPayOrder($query_start_date, $query_end_date) + getPaidPlatformPayOrder($query_start_date, $query_end_date);
//收入金额
$report_data[$i]['income'] = getShopPayOrderAmount($query_start_date, $query_end_date) + getPlatformPayOrderAmount($query_start_date, $query_end_date) - getRefundAmountFromPayOrder($query_start_date, $query_end_date);;
//客单价
$report_data[$i]['pay_order_amount'] =  round($report_data[$i]['income'] / $report_data[$i]['paid_pay_order'],2);
//转化率
$report_data[$i]['percent_conversion'] = round(getPaidHaveFreeOrderShop($query_start_date, $query_end_date) / $report_data[$i]['have_free_order_shop'] * 100,2)."%";
//续费率
$report_data[$i]['renew_rate'] = round(getRePaidShop($query_start_date, $query_end_date) / getExpiredShop($query_start_date, $query_end_date) * 100,2)."%";
//复购率
$report_data[$i]['re_purchase_rate'] = getRePaidTwoShopRate($query_start_date, $query_end_date);
//同步订单总量
$report_data[$i]['all_order'] = getAllOrder($query_start_date,$query_end_date);
//打单率
$report_data[$i]['print_rate'] = round(getPrintOrder($query_start_date,$query_end_date)/ $report_data[$i]['all_order']*100,2)."%";


echo(date("Y-m-d H:i:s") . " query end query_start_date = ".$query_start_date." query_end_date = ".$query_end_date.PHP_EOL);

$key_array = array('query_date','all_user','effective_user','new_user','all_shop',
                    'effective_shop','new_shop', 'effective_paid_shop', 'have_free_order_shop','free_pay_order',
                    'paid_pay_order','income', 'pay_order_amount','percent_conversion',
                    'renew_rate', 're_purchase_rate','all_order','print_rate'
);
$header = array(array( '时间','总用户数','有效用户数','新增用户数','总店铺数',
                        '有效店铺数', '新增店铺数','付费店铺数', '试用店铺数','免费笔数',
                        '付费笔数','收入金额', '客单价','转化率',
                        '续费率(含免费试用)','复购率','同步订单总量','打单率'
));
send_erp_email($report_file_name,$header,$key_array, $report_data);

$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo PHP_EOL;
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");



