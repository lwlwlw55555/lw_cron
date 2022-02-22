<?php
require("includes/init.php");
require("Services/OMSExpressApiService.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
use Services\OMSExpressApiService;

echo("[]".date("Y-m-d H:i:s") . " downloadOrderTaobaoNewShopByApi  begin \r\n");
echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;

global $oms_sync_db;
$oms_sync_db_conf = array(
    "host" => "100.65.2.110:32057",
    "user" => "mddomsapi_sync",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomssync"
);
$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);

$sql_get_new_taobao_shops = "SELECT se.shop_id from shop_extension se inner join shop s on se.shop_id = s.shop_id WHERE se.platform_code = 'TAOBAO' AND se.last_plan_sync_time < now()";
$shop_ids = $oms_sync_db->getAll($sql_get_new_taobao_shops);
$end_time = date("Y-m-d H:i:s");
$modified_time = date("Y-m-d H:i:s", strtotime("{$end_time} +10 year"));
foreach ($shop_ids as $shop_id) {
    for($i=1;$i<=15;$i++) {
        $start_time = date("Y-m-d H:i:s", strtotime("{$end_time} -1 day"));
        echo "[]".date("Y-m-d H:i:s").'[params]:shop_id:'.$shop_id['shop_id'].',start_time:'.$start_time.',end_time:'.$end_time.PHP_EOL;
        $result = OMSExpressApiService::syncNewTaobaoShopByApi($start_time,$end_time,$shop_id['shop_id']);
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
    // check_sync_order($shop_id['shop_id'],'TAOBAO',$shop_id['default_facility_id']);

    $end_time = date("Y-m-d H:i:s");
    $sql_update_new_shop_date = "UPDATE shop_extension SET last_plan_sync_time = '{$modified_time}' WHERE shop_id = {$shop_id['shop_id']}";
    $oms_sync_db->query($sql_update_new_shop_date);
}

echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
echo "[]".date("Y-m-d H:i:s") . " downloadPlatformErrorTimestamp  end \r\n";

function getPlatformUnShippedOrderSn($shop_id){
    $result = OMSExpressApiService::downloadOrder($shop_id);
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

