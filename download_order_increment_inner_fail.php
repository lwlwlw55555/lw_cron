<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo(date("Y-m-d H:i:s") . " downloadOrderIncrement  begin \r\n");
//die;
/**
 * 第一个参数为店铺id，如果不传或传0，则表示所有店铺都要执行
 *
 * 第二个参数为piece值
 * */
//第一个参数存在且不为0，则下载该店铺订单
global $db;
$need_enabled_shops = $sync_db->getCol("SELECT se.shop_id from shop_extension se LEFT JOIN shop s on se.shop_id = s.shop_id
where s.shop_id is null && se.enabled = 1
                                   and last_plan_sync_time  <  DATE_SUB(now(),INTERVAL 3 minute) and se.platform_code <> 'taobao'");
if (!empty($need_enabled_shops)) {
    echo date("Y-m-d H:i:s").' '.json_encode($need_enabled_shops).' shop not exist！'.PHP_EOL;
    $sql_1 = "update shop_extension set enabled = 0 where shop_id in (".implode(",", $need_enabled_shops).")";
    echo $sql_1.PHP_EOL;
    $sync_db->query($sql_1);
}



$sql = "SELECT
            s.shop_id,
            s.last_plan_sync_time,
            s.last_plan_sync_wait_ship_time,
            s.last_plan_sync_shipped_time,
            s.is_big_shop,
            s.platform_code
FROM
     shop_extension  s
WHERE
     enabled = 1 AND last_plan_sync_time < DATE_SUB(now(), INTERVAL 3 minute) and platform_code <> 'taobao'
     and s.is_big_shop < 20 and s.shop_mod < 200
";
if (isset($opt_params['mod_index'])) {
    $mod = isset($opt_params['mod']) ? $opt_params['mod'] : 10;
    $sql .= " and mod(shop_id, {$mod}) = {$opt_params['mod_index']}";
}
if (isset($opt_params['shop_id'])) {
    $sql .= " and shop_id = {$opt_params['shop_id']}";
}

$shop_list = $sync_db->getAll($sql);
var_dump($shop_list);
echo 'begin!'.PHP_EOL;


foreach($shop_list as $shop){
    $is_big_shop = $sync_db->getOne("select is_big_shop from shop_extension where shop_id = {$shop['shop_id']}");
    if ($is_big_shop >= 20) {
        continue;
    }
    selectRdsByShopId($shop['shop_id']);
    $shop_id = $shop['shop_id'];
    $last_plan_sync_time = $shop['last_plan_sync_time'];
    $is_big_shop = $shop['is_big_shop'];
    $platform_code = $shop['platform_code'];
    syncShop($shop_id,$last_plan_sync_time,$is_big_shop,$platform_code,null);
}

function syncShop($shop_id,$last_plan_sync_time,$is_big_shop,$platform_code,$order_type){
    $is_record_mongo = '0';
    $time_interval = 5*60;
    if ($is_big_shop == 3){
        $is_record_mongo = '1';
        $time_interval = 10;
    }
    if(empty($last_plan_sync_time)){
            $last_plan_sync_time = date("Y-m-d H:i:s", time() - 2*24*60*60);
    }elseif(strtotime($last_plan_sync_time) < time() - 2*24*60*60){
            $last_plan_sync_time = date("Y-m-d H:i:s", time() - 2*24*60*60);
    }
    $start_time = strtotime($last_plan_sync_time);
    if($platform_code == 'beibei'){
        $start_time = strtotime($last_plan_sync_time) - 1*60*60;
    }
    // $end_time = time() - 1*60;//两分钟前
    $end_time = time();

    if($start_time > $end_time){
        return null;
    }
    $last_success_time = strtotime($last_plan_sync_time);
    $max_updated_at = "";
    for (; $start_time < $end_time; $start_time += $time_interval) {
        $end_time_item = ($start_time + $time_interval) > $end_time?$end_time:($start_time + $time_interval);
        $result = ExpressApiService::syncShopWithTimestamp($shop_id,date("Y-m-d H:i:s", $start_time),date("Y-m-d H:i:s", $end_time_item),$is_record_mongo,$order_type);
        if (isset($result['data']['max_updated_at']) && !empty($result['data']['max_updated_at'])) {
            $max_updated_at = strtotime($result['data']['max_updated_at']);
        }

        if(isset($result['code']) && $result['code'] == 20004 || ($result['code'] > 0 && checkTokenFail($result))){
            ShopModel::enableShop($shop_id);
            return $result;
        }
        if(!isset($result['code']) || $result['code'] > 0){
            if (strpos($result['msg'],"inner_fail") !== false
                && (strpos($result['msg'],"不存在") !== false || strpos($result['msg'],"record") !== false)) {
                $order_sn = substr($result['msg'], strpos($result['msg'],"order_sn:")+9,strpos($result['msg'],"inner_fail")-strpos($result['msg'],"order_sn:")-9);
                echo date("Y-m-d H:i:s")."order_sn:{$order_sn} inner_fail 删除所有相关该订单数据".PHP_EOL;
                // var_dump($order_sn);
                global $db;
               
                $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}'");
                $db->query("delete from multi_goods_shipment_goods where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                $db->query("delete from order_info  where order_sn = '{$order_sn}'");
                $db->query("delete from shipment  where order_sn = '{$order_sn}'");
                deleteSyncData($platform_code,$order_sn,$shop_id);
                $last_success_time += $time_interval;
                continue;
            }
            if (strpos($result['msg'],"inner_fail") !== false
                && strpos($result['msg'],"exception:null") !== false) {
                $order_sn = substr($result['msg'], strpos($result['msg'],"order_sn:")+9,strpos($result['msg'],"inner_fail")-strpos($result['msg'],"order_sn:")-9);
                echo date("Y-m-d H:i:s")."order_sn:{$order_sn} inner_fail 删除所有相关该订单数据".PHP_EOL;
                // var_dump($order_sn);
                global $db;
               
                $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}'");
                $db->query("delete from multi_goods_shipment_goods where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                $db->query("delete from order_info  where order_sn = '{$order_sn}'");
                $db->query("delete from shipment  where order_sn = '{$order_sn}'");
                deleteSyncData($platform_code,$order_sn,$shop_id);
                $last_success_time += $time_interval;
                continue;
            }
            if (strpos($result['msg'],"InnerOrderRefundResult") !== false
                && strpos($result['msg'],"org.apache.ibatis.exceptions.TooManyResultsException") !== false) {
                $order_sn = substr($result['msg'], strpos($result['msg'],"order_sn:")+9,strpos($result['msg'],"inner_fail")-strpos($result['msg'],"order_sn:")-9);
                echo date("Y-m-d H:i:s")."order_sn:{$order_sn} inner_fail 删除所有相关该订单数据".PHP_EOL;
                // var_dump($order_sn);
                global $db;
               
                $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}'");
                $db->query("delete from multi_goods_shipment_goods where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                $db->query("delete from order_info  where order_sn = '{$order_sn}'");
                $db->query("delete from shipment  where order_sn = '{$order_sn}'");
                deleteSyncData($platform_code,$order_sn,$shop_id);
                $last_success_time += $time_interval;
                continue;
            }
            if (strpos($result['msg'],"Duplicate") !== false
                && strpos($result['msg'],"u_order_sn_index") !== false) {
                $order_sn = substr($result['msg'], strpos($result['msg'],"Duplicate entry '")+17,strpos($result['msg'],"' for key")-strpos($result['msg'],"Duplicate entry '")-17);
                // var_dump($order_sn);
                global $sync_db;
                $delete_shop_id = $sync_db->getOne("select s.shop_id from sync_pinduoduo_order_info s  
                                left join shop sp on s.shop_id = sp.shop_id where sp.shop_id is null and s.order_sn = '{$order_sn}'");
                // var_dump($delete_shop_id);
                if(!empty($delete_shop_id)){
                    echo date("Y-m-d H:i:s")."order_sn:{$order_sn} delete_shop_id:{$delete_shop_id} Duplicate 删除所有相关该店铺数据".PHP_EOL;
                    // $sync_db->query("delete from sync_pinduoduo_order_info where shop_id = {$delete_shop_id}");
                    // $sync_db->query("delete from sync_pinduoduo_order_goods where shop_id = {$delete_shop_id}");
                    // $sync_db->query("delete from goods_pinduoduo where shop_id = {$delete_shop_id}");
                    // $sync_db->query("delete from sku_pinduoduo where shop_id = {$delete_shop_id}");
                }

                $start_time = strtotime($last_plan_sync_time) - $time_interval;
                continue;
            }

            $prepare_updated_time = $last_success_time-1*60;
            if ($prepare_updated_time > strtotime($last_plan_sync_time) || $max_updated_at > strtotime($last_plan_sync_time)) {
                if (!empty($max_updated_at) && $max_updated_at > $prepare_updated_time) {
                    $prepare_updated_time = $max_updated_at;
                }
                ShopModel::updateShopSyncTime($shop_id,date("Y-m-d H:i:s",$prepare_updated_time),$order_type);
            }
            return $result;
        }
        $last_success_time += $time_interval;
    }

    $prepare_updated_time = $end_time-1*60;
    if ($prepare_updated_time > strtotime($last_plan_sync_time) || $max_updated_at > strtotime($last_plan_sync_time)) {
        if (!empty($max_updated_at) && $max_updated_at > $prepare_updated_time) {
            $prepare_updated_time = $max_updated_at;
        }
        ShopModel::updateShopSyncTime($shop_id,date("Y-m-d H:i:s", $prepare_updated_time),$order_type);
    }
}

echo(date("Y-m-d H:i:s") . " downloadOrderIncrement end \r\n");
function deleteSyncData($platform_name,$order_sn,$shop_id){
    global $sync_db;
    if ($platform_name == 'pinduoduo') {
        $order_id = $sync_db->getOne("select order_id from sync_pinduoduo_order_info where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
        if (!empty($order_id)) {
            $sync_db->query("delete from sync_pinduoduo_order_goods where order_id = {$order_id}");
            $sync_db->query("delete from sync_pinduoduo_order_info where order_id = {$order_id}");
        }
    }else if ($platform_name == 'youzan') {
        $sync_db->query("delete from youzan_order where tid = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from youzan_order_goods where tid = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'mogujie'){  
        $sync_db->query("delete from sync_mogujie_order_info where shop_order_id = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_mogujie_order_goods where shop_order_id = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'jd'){  
        $sync_db->query("delete from sync_jd_order_info where order_id = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_jd_order_goods where order_id = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'beibei'){  
        $sync_db->query("delete from sync_beibei_order_info where oid = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_beibei_order_goods where oid = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'taobao'){  
        $sync_db->query("delete from sync_taobao_order_info where tid = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_taobao_order_goods where tid = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'weimeng'){  
        $sync_db->query("delete from sync_weimeng_order_info where order_no = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_weimeng_order_goods where order_no = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'dangdang'){  
        $sync_db->query("delete from sync_dangdang_order_info where order_id = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_dangdang_order_goods where order_id = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'suning'){  
        $sync_db->query("delete from sync_suning_order_info where order_code = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_suning_order_goods where order_code = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'weidian'){  
        $sync_db->query("delete from sync_weidian_order_info where order_id = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_weidian_order_goods where order_id = '{$order_sn}' and shop_id = {$shop_id}");
    }else if ($platform_name == 'haoshiqi'){
        $sync_db->query("delete from sync_haoshiqi_order_info where id = '{$order_sn}' and shop_id = {$shop_id}");
        $sync_db->query("delete from sync_haoshiqi_order_goods where order_id = '{$order_sn}' and shop_id = {$shop_id}");
    }
}

function checkTokenFail($result){
    if(strpos($result['msg'],'当前用户或应用存在风险') !== false){ 
     return true;
    }
    if(strpos($result['msg'],'已过期') !== false){
     return true;
    }
    if(strpos($result['msg'],'签名授权失败') !== false){
     return true;
    }

    if(strpos($result['msg'],'必传参数为空 检查secret、session等参数') !== false){
     return true;
    }
    if(strpos($result['msg'],'Access_token') !== false){
     return true;
    }
    if(strpos($result['msg'],'Accesstoken') !== false){
     return true;
    }
    return false;
}