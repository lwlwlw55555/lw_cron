<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

global $db,$db_user;
if(isset($argv[1])){
    $piece = $argv[1];
    if($piece_list[$piece]){
        $db = ClsPdo::getInstance($piece_list[$piece]);
        $db->query("use ".$argv[2]);
    }
}
$time1 = date("Y-m-d H:i:s");
echo(date("Y-m-d H:i:s") . " send report begin \r\n");
$selectDayDate = date("Y-m-d",strtotime("-1 day"));
if (isset($argv[3])){
    $is_date = strtotime($argv[3])?strtotime($argv[3]):false;
    if($is_date){
        $selectDayDate = $argv[3];
        echo "查询时间：".$selectDayDate;
    }
}
$report_file_name = $selectDayDate."-鲸灵ERP店铺报表";
$day01Date = date("Y-m-d",strtotime("-0 day",strtotime($selectDayDate)));
$day02Date = date("Y-m-d",strtotime("-1 day",strtotime($selectDayDate)));
$day03Date = date("Y-m-d",strtotime("-2 day",strtotime($selectDayDate)));

$user_sql = "
    select 
        u.party_id,
        u.user_name,
        u.last_open_time,
        po.expire_time
    from 
        user u 
        inner join pay_oauth po on po.user_id = u.user_id";
$user = $db_user->getAll($user_sql);
$party_id_str = implode(",",array_column($user,"party_id"));
$facility = $db->getAll("select f.party_id, f.sender_mobile from facility f where f.party_id in ('{$party_id_str}')");
$results = array();
$sql = "select 
            s.shop_id,
            s.created_time,
            case s.created_from 
                WHEN 'dddd_system' THEN '迁移时系统创建'
                WHEN 'dddd_menu' THEN '多多打单-左侧菜单'
                WHEN 'dddd_topbtn' THEN '多多打单-顶部升级按钮'
                WHEN 'dddd_hideUrl' THEN '多多打单-群聊链接'
                ELSE '其他' 
            END AS created_from,
            s.shop_name,
            s.enabled
        from
            s.shop
        where 
            s.party_id in ('{$party_id_str}')
        order by
            s.created_time desc
        ";
$result = $db->getAll($sql);
$shop_ids = array_column($result, 'shop_id');
$shop_data = array();
foreach($shop_ids as $shop_id){
    $sql1 = "select order_count, print_count from shop_data where shop_id = {$shop_id} and count_data = {$day01Date}";
    $sql2 = "select order_count, print_count from shop_data where shop_id = {$shop_id} and count_data = {$day02Date}";
    $sql3 = "select order_count, print_count from shop_data where shop_id = {$shop_id} and count_data = {$day03Date}";
    $shop_data_day1 = $db_user->query($sql1);
    $shop_data_day2 = $db_user->query($sql2);
    $shop_data_day3 = $db_user->query($sql3);
    $tmp = [
        'shop_id' => $shop_id,
        'day1_order_count' => $shop_data_day1->order_count,
        'day2_order_count' => $shop_data_day2->order_count,
        'day3_order_count' => $shop_data_day3->order_count,
        'day1_print_count' => $shop_data_day1->print_count,
        'day1_print_count' => $shop_data_day2->print_count,
        'day1_print_count' => $shop_data_day3->print_count,
    ];
    array_push($shop_data, $tmp);
}
foreach($result as $tmp){
    foreach($user as $u){
        if($u->party_id == $tmp->party_id){
            $tmp->user_name = $u->user_name;
            $tmp->last_open_time = $u->last_open_time;
            $tmp->expire_time = $u->expire_time;
        }
    }
    foreach($facility as $f){
        if($tmp->party_id = $f->party_id){
            $tmp->sender_mobile = $f->sender_mobile;
        }
    }
    foreach($shop_data as $sd){
        if($sd->party_id = $tmp->party_id){
            $tmp->day1_order_count = $sd->day1_order_count;
            $tmp->day2_order_count = $sd->day2_order_count;
            $tmp->day3_order_count = $sd->day3_order_count;
        }
    }
}

// echo "**************************************************************\r\n";
// echo $sql;
// echo "**************************************************************\r\n";

if (empty($result)) {
    echo(" 未查询到店铺信息 \r\n");
}else {
    $key_array = array ('created_time','created_from','shop_name','enabled','user_name','last_open_time','expire_time','sender_mobile',
        'day1_order_count','day2_order_count','day3_order_count',
        'day1_print_count','day2_print_count','day3_print_count');
    $header = array(array("店铺创建时间",'创建渠道',"店铺名","店铺授权是否有效","用户名","用户最后打开网页时间","用户购买过期时间","手机号",
        $day01Date."店铺订单量",	$day02Date."店铺订单量",$day03Date."店铺订单量",
        $day01Date."店铺打单量", $day02Date."店铺打单量",$day03Date."店铺打单量"));
    send_erp_email($report_file_name,$header,$key_array, $result);
    $cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
    echo PHP_EOL;
    echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");
}
