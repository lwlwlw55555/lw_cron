<?php
require("includes/init.php");
require("Services/ExpressApiService.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
use Services\ExpressApiService;

echo("[]".date("Y-m-d H:i:s") . " downloadOrderTaobaoNewShopByApi  begin \r\n");
echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;

global $sync_db;
$sql_get_new_taobao_shops = "SELECT se.shop_id,s.default_facility_id from shop_extension se inner join shop s on se.shop_id = s.shop_id WHERE s.shop_id = 6450182";
$shop_ids = $sync_db->getAll($sql_get_new_taobao_shops);
$end_time = date("Y-m-d H:i:s");
$end_time = date("Y-m-d H:i:s",strtotime("2021-08-23 10:00:19"));
// $modified_time = date("Y-m-d H:i:s", strtotime("{$end_time} +10 year"));
foreach ($shop_ids as $shop_id) {
    for($i=1;$i<=48*48*48;$i++) {
        // $start_time = date("Y-m-d H:i:s", strtotime("{$end_time} -1 hour"));
        $start_time = date("Y-m-d H:i:s", strtotime("{$end_time} -1 minute"));
        // $start_time = date("Y-m-d H:i:s", strtotime("{$end_time} -1 second"));
        echo "[]".date("Y-m-d H:i:s").'[params]:shop_id:'.$shop_id['shop_id'].',start_time:'.$start_time.',end_time:'.$end_time.PHP_EOL;
        $result = ExpressApiService::syncNewTaobaoShopByApi($start_time,$end_time,$shop_id['shop_id']);
        if(!isset($result['code']) || $result['code'] > 0){
            echo "[]".date("Y-m-d H:i:s") ."error: ";
            var_export($result);
            echo "[]".PHP_EOL;
            echo "[]".date("Y-m-d H:i:s").' '.json_encode($result)."end".PHP_EOL;
            echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
        }else{
            echo "[]".date("Y-m-d H:i:s") ."success: ";
            var_export($result);
            echo "[]".PHP_EOL;
            echo "[]".date("Y-m-d H:i:s").' '.json_encode($result)."end".PHP_EOL;
            echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
        }
        $end_time = $start_time;
    }
    check_sync_order($shop_id['shop_id'],'taobao',$shop_id['default_facility_id']);

    $end_time = date("Y-m-d H:i:s");
    $sql_update_new_shop_date = "UPDATE shop_extension SET last_plan_sync_time = '{$modified_time}' WHERE shop_id = {$shop_id['shop_id']}";
    $sync_db->query($sql_update_new_shop_date);
}

echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
echo "[]".date("Y-m-d H:i:s") . " downloadPlatformErrorTimestamp  end \r\n";

function check_sync_order($shop_id,$platform_name,$facility_id){
    $result = getPlatformUnShippedOrderSn($shop_id);
    if(isset($result->error_code)){
            print_r($result);
    }else{
        print_r("shop_id:{$shop_id}, count:".count($result).",list:".json_encode($result));
            $our_order_list = OrderModel::getUnShippedOrderSn($shop_id,$platform_name,$facility_id);
            $platform_order_list = $result;
            $our_minu_platform = array_udiff($our_order_list,$platform_order_list,"myCompare");
            $platform_minu_our = array_udiff($platform_order_list,$our_order_list,"myCompare");
            echo("[]".date("Y-m-d H:i:s") . " {$shop_id} our_minu_platform ".count($our_minu_platform)." \r\n");
            foreach($our_minu_platform as $order_sn){
                // $response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
                // if ($platform_name == 'taobao') {
                    // if (isset($response['msg']) && strpos($response['msg'], '找不到该订单') !== false) {
                        // echo "[]".'taobao 找不到该订单:'.$order_sn.' 调用api获取:'.PHP_EOL;
                        $res = ExpressApiService::downloadTaobaoSingleOrderByApi($shop_id,$order_sn);
                        if (isset($res['msg']) && strpos($res['msg'], 'isv.trade-not-exist') !== false) {
                            echo "[]".'taobao 发现三个月前找不到的待发货订单:'.$order_sn.',sync库生产库删除该数据'.PHP_EOL;
                            $sync_db->query("delete from sync_taobao_order_info where tid = '{$order_sn}'");
                            selectRdsByShopId($shop_id);
                            $db->query("delete from order_info where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                            $db->query("delete from shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                            $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                        }
                    // }
                // }
            }
            echo("[]".date("Y-m-d H:i:s") . " {$shop_id}  platform_minu_our ".count($platform_minu_our)."\r\n");
            foreach($platform_minu_our as $order_sn){
                // $response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
                // if ($platform_name == 'taobao') {
                    // if (isset($response['msg']) && strpos($response['msg'], '找不到该订单') !== false) {
                        // echo "[]".'taobao 找不到该订单:'.$order_sn.' 调用api获取:'.PHP_EOL;
                        $res = ExpressApiService::downloadTaobaoSingleOrderByApi($shop_id,$order_sn);
                        if (isset($res['msg']) && strpos($res['msg'], 'isv.trade-not-exist') !== false) {
                            echo "[]".'taobao 发现三个月前找不到的待发货订单:'.$order_sn.',sync库生产库删除该数据'.PHP_EOL;
                            $sync_db->query("delete from sync_taobao_order_info where tid = '{$order_sn}'");
                            selectRdsByShopId($shop_id);
                            $db->query("delete from order_info where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                            $db->query("delete from shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                            $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and shop_id = {$shop_id}");
                        }
                    // }
                // }
            }
            echo("[]".date("Y-m-d H:i:s") . " {$shop_id} our_minu_platform ".count($our_minu_platform)." platform_minu_our ".count($platform_minu_our)."\r\n");
    }
}

function getPlatformUnShippedOrderSn($shop_id){
    $result = ExpressApiService::downloadOrder($shop_id);
    if(isset($result['code']) && $result['code'] > 0){
        $error_object = new stdClass();
        $error_object->error_code = $result['code'];
        print_r($result);
        return $error_object;
    }
    return $result['data']['orderSnList'];
}

//调用接口  刷新订单状态
function myCompare($a,$b){
    if ($a==$b){
        return 0;
    }
    return ($a>$b)?1:-1;
}

