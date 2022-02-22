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

$report_file_name = $startDayDate."ERP用户关键数据日报";
echo(date("Y-m-d H:i:s") . " send ".$report_file_name.PHP_EOL);
$report_date_array = array(
    $startDayDate
);
$report_data = array();
$i = 0;
$platform_name_list = [null,'pinduoduo','taobao','1688','kuaishou','douyin'] ;
foreach ($report_date_array as $query_start_date){
	$query_end_date = date("Y-m-d",strtotime("+1 day",strtotime($query_start_date)));
    foreach($platform_name_list as $platform_name){
        $platform_shop_id_list =  getPlatformShopIds($query_end_date,$platform_name);
        $platform_shop_count = count($platform_shop_id_list);
        $platform_shop_ids = implode("','",$platform_shop_id_list );
        unset($platform_shop_id_list);

        $report_data[$i]['query_date'] = $query_start_date;
        
        $report_data[$i]['platform_name'] = $platform_name?$platform_name:'汇总';
        
        //总用户
        $report_data[$i]['all_user'] = getAllUser($query_end_date,$platform_name);
        //有效用户
        if($platform_name == null || $platform_name == 'pinduoduo') {
            $report_data[$i]['effective_user'] = getEffectiveUser( $query_start_date, $query_end_date, $platform_name);
        }else{
            $report_data[$i]['effective_user'] = getEffectiveUserOther($query_end_date,$platform_shop_ids);
        }
        //新增用户数
        $report_data[$i]['new_user'] = $platform_name != null ?getNewShopOther($query_start_date, $query_end_date,$platform_name):getNewUser($query_start_date, $query_end_date);
        //打单用户
        $report_data[$i]['print_user'] = getPrintUser($query_start_date, $query_end_date,$platform_shop_ids);
        //沉默用户数
        //$report_data[$i]['not_login_user'] = $platform_name != null ?'/':getNotLoginUser($query_end_date);
        //总店铺数
        $report_data[$i]['all_shop'] = $platform_shop_count;
        //有效店铺数
        if($platform_name == null || $platform_name == 'pinduoduo') {
            $report_data[$i]['effective_shop'] = getEffectiveShop($query_start_date,$query_end_date,$platform_name);
        }else{
            $report_data[$i]['effective_shop'] = getEffectiveShopOther($query_end_date,$platform_shop_ids);
        }
        //新增店铺数
        $report_data[$i]['new_shop'] = getNewShop($query_start_date, $query_end_date,$platform_shop_ids);
        //打单店铺数
        $report_data[$i]['print_shop'] = getPrintShop($query_start_date,$query_end_date,$platform_shop_ids);
        //同步订单总量
        $report_data[$i]['all_order'] = getAllOrder($query_start_date,$query_end_date,$platform_shop_ids);
        //日打单量
        $report_data[$i]['print_order'] = getPrintOrder($query_start_date,$query_end_date,$platform_shop_ids);
        //打单率  日打单量/同步订单总量
        $report_data[$i]['print_rate'] = round($report_data[$i]['print_order']/ $report_data[$i]['all_order']*100,2)."%";
        $i++;
        unset($platform_shop_ids);
    }
    echo(date("Y-m-d H:i:s") . " query end query_start_date = ".$query_start_date." query_end_date = ".$query_end_date.PHP_EOL);
}
$key_array = array('query_date','platform_name','all_user','effective_user','new_user',
                    'print_user','all_shop','effective_shop','new_shop','print_shop','all_order','print_order','print_rate'
);
$header = array(array('时间','平台','总用户数','有效用户数','新增用户数',
                    '打单用户数','总店铺数','有效店铺数','新增店铺数','打单店铺数','同步订单总量','日打单量','打单率'
    ));
send_erp_email($report_file_name,$header,$key_array, $report_data);

$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo PHP_EOL;
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");



