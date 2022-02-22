<?php
require("includes/init.php");
global $db_user;
$sql = "
    select super_query_params,facility_id from user where user_env = 'production'
";
$user_list = $db_user->getAll($sql);
foreach ($user_list as $user)
{
    $facility_id = $user['facility_id'];
    $sql = "
        delete from query_combination where  facility_id = {$facility_id} 
    ";
    $db_user->query($sql);

    $super_query_params_list = json_decode($user['super_query_params'], true);
    if (empty($super_query_params_list)){
        echo " not have super_query_params".PHP_EOL;
        continue;
    }
    $sort = 0;
    foreach ( $super_query_params_list as $super_query_params) {
        $sort ++;
        $sql = "
        insert into 
            query_combination 
        set 
            query_criteria_name = " . checkNull($super_query_params['query_criteria_name']) . ",
            facility_id = " . $facility_id . ",
            is_print_tracking = " . checkNull($super_query_params['is_print_tracking']) . ",
            is_can_merge = " . checkNull($super_query_params['can_merge']) . ",
            facility_shipment_flag_ids = " . checkNull($super_query_params['order_flag']) . ",
            is_note = " . checkNull($super_query_params['is_note']) . ",
            note = " . checkNull($super_query_params['note']) . ",
            is_include_goods = " . checkNull($super_query_params['not_include_goods']) . ",
            goods_list = " . checkNull($super_query_params['goods_list']) . ",
            min_goods_number = " . checkNull($super_query_params['min_goods_number']) . ",
            max_goods_number = " . checkNull($super_query_params['max_goods_number']) . ",
            is_pay_amount = " . checkNull($super_query_params['is_pay_amount']) . ",
            is_goods_amount = " . checkNull($super_query_params['is_goods_amount']) . ",
            min_goods_amount = " . checkNull($super_query_params['min_goods_amount']) . ",
            max_goods_amount = " . checkNull($super_query_params['max_goods_amount']) . ",
            min_pay_amount = " . checkNull($super_query_params['min_pay_amount']) . ",
            max_pay_amount = " . checkNull($super_query_params['max_pay_amount']) . ",
            min_weight = " . checkNull($super_query_params['min_weight']) . ",
            max_weight = " . checkNull($super_query_params['max_weight']) . ",
            region_list = " . checkNull($super_query_params['region_list']) .
            ", sort = {$sort} "
        ;
        echo $facility_id.$super_query_params['query_criteria_name'].PHP_EOL;
        $db_user->query($sql);
    }
}

function getRegionListName($region_list){
    if (empty($region_list)){
        return 'null';
    }
    $region_explain = '';
    foreach ($region_list as $region){
        if($region_explain != ''){
            $region_explain .= ',';
        }
        $region_explain .= getRegionName($region['province_id']);
        if(array_key_exists('city_id',$region)){
            $region_explain .= getRegionName($region['city_id']);
        }
        if(array_key_exists('district_id',$region)){
            $region_explain .= getRegionName($region['district_id']);
        }
    }

    return "'省市区包含['.$region_explain.']中的地址'";

}

function getRegionName($region_id){
    global $db_user;
    $sql = "
        select region_name from region where region_id = {$region_id}
    ";
    return $db_user->getOne($sql);
}

function checkNull($temp){
    if(!is_null($temp)){
        if (is_array($temp)){
            $temp = json_encode($temp);
        }
        $temp = addslashes($temp);
        $temp = "'{$temp}'";
    }else{
        $temp = 'null';
    }
    return $temp;
}