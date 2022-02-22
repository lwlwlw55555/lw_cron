<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
include 'request/OrderNumberListGetRequest.php';
include 'PddClient.php';

global $db,$sync_db;

echo(date("Y-m-d H:i:s") . " refresh order  begin \r\n");
if (isset($opt_params['shop_id'])){
        $shop_id  = $opt_params['shop_id'];
               $sql = "SELECT
                    s.shop_id,
                    s.platform_shop_id,
                    s.platform_shop_secret,
                    s.platform_name,
                    s.access_token,
                    s.app_key,
                    s.default_facility_id
                FROM
                    shop s
                WHERE
                        s.shop_id = {$shop_id}
                ";
        $shops = $sync_db->getAll($sql);
$shop = $shops[0];
selectRdsByShopId($shop['shop_id']);
$ss = $db->getAll("show tables");
        check_sync_order($shop['shop_id'],$shop['platform_shop_id'],$shop['platform_shop_secret'],$shop['platform_name'],$shop['access_token'],$shop['app_key'],$shop['default_facility_id']);
}else{
	die("ff");
    $start = 0;
    $length = 100000;
    if(isset($opt_params['start'])){
        $start = $opt_params['start'];
    }
    if(isset($opt_params['limit'])){
        $length = $opt_params['limit'];
    }

    $sql = "SELECT
                s.shop_id,
                s.platform_shop_id,
                s.platform_shop_secret,
                s.platform_name,
                s.access_token,
                s.app_key,
                s.default_facility_id
    FROM
        shop s
        inner join shop_extension se on s.shop_id = se.shop_id
    WHERE
        s.enabled = 1
     and s.access_token is not  null
     and se.enabled = 1
     and se.is_big_shop < 20
    order by s.`shop_id`
    limit {$start},{$length}
    ";

    $shop_list = $sync_db->getAll($sql);

    $i = 0;
    foreach($shop_list as $shop){
            $is_big_shop = $sync_db->getOne("select is_big_shop from shop_extension where shop_id = {$shop_id}");
            if ($is_big_shop >= 20) {
                continue;
            }
            selectRdsByShopId($shop['shop_id']);
            echo(date("Y-m-d H:i:s") . " check_sync_order shop_id={$shop['shop_id']} index={$i} total=" . count($shop_list)." \r\n");
            $i++;
            check_sync_order($shop['shop_id'],$shop['platform_shop_id'],$shop['platform_shop_secret'],$shop['platform_name'],$shop['access_token'],$shop['app_key'],$shop['default_facility_id']);
    }
}

echo(date("Y-m-d H:i:s") . " refresh order end \r\n");


function check_sync_order($shop_id,$platform_shop_id,$secret,$platform_name,$access_token=null,$app_key=null,$facility_id){
        global $sync_db, $db;
        $result = getPlatformUnShippedOrderSn($shop_id,$platform_shop_id,$secret,$platform_name,$access_token,$app_key);
        if(isset($result->error_code)){
                print_r($result);
        }else{
            print_r("shop_id:{$shop_id}, count:".count($result).",list:".json_encode($result));
                $our_order_list = OrderModel::getUnShippedOrderSn($shop_id,$platform_name,$facility_id);
                $platform_order_list = $result;
                $our_minu_platform = array_udiff($our_order_list,$platform_order_list,"myCompare");
                $our_minu_platform = [];
                $platform_minu_our = array_udiff($platform_order_list,$our_order_list,"myCompare");
		echo(date("Y-m-d H:i:s") . " {$shop_id} our_minu_platform ".count($our_minu_platform)." \r\n");

		$jj = 0;
		foreach($our_minu_platform as $order_sn){
			if ($jj++ % 100 == 0) {
                        selectRdsByShopId($shop_id);
                    }
                    $response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
                    if ($platform_name == 'taobao') {
                        if (isset($response['msg']) && strpos($response['msg'], '找不到该订单') !== false) {
                            echo 'taobao 找不到该订单:'.$order_sn.' 调用api获取:'.PHP_EOL;
                            $res = ExpressApiService::downloadTaobaoSingleOrderByApi($shop_id,$order_sn);
                            if (isset($res['msg']) && strpos($res['msg'], 'isv.trade-not-exist') !== false) {
                                echo 'taobao 发现三个月前找不到的待发货订单:'.$order_sn.',sync库生产库删除该数据'.PHP_EOL;
                                $sync_db->query("delete from sync_taobao_order_info where tid = '{$order_sn}'");
                                $db->query("delete from order_info where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                                $db->query("delete from shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                                $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                                $db->query("delete from multi_goods_shipment_goods where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                            }
                        }
                    }else{
                        if (isset($response['msg']) && strpos($response['msg'], '订单不属于当前店铺或订单不存在') !== false) {
                            echo 'pinduoduo 发现三个月前找不到的待发货订单:'.$order_sn.',sync库生产库删除该数据'.PHP_EOL;
                            $sync_db->query("delete from sync_pinduoduo_order_info where order_sn = '{$order_sn}'");
                            $db->query("delete from order_info where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                            $db->query("delete from shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                            $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                            $db->query("delete from multi_goods_shipment_goods where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                            continue;
                        }else if ((isset($response['code']) && $response['code'] == 1) || (isset($response['data']['msg']) && strpos($response['data']['msg'], '订单为已发货或退款订单不保存') !== false)) {
                            if (!$sync_db->getOne("SELECT 1 from check_errors where shop_id = {$shop_id} and order_sn = '{$order_sn}'")) {
                               $sync_db->query("INSERT into check_errors (shop_id, platform_name, order_sn, info, type) VALUES ({$shop_id},'{$platform_name}','{$order_sn}','".addslashes(json_encode($response))."','软件多于平台')");
                            }
                        }
                    }
                    if (isset($response['data']['msg']) && strpos($response['data']['msg'], '订单为已发货或退款订单不保存') !== false) {
                        $db->query("delete from order_info where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                        $db->query("delete from shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                        $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                        $db->query("delete from multi_goods_shipment_goods where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                        echo '发现可能超时导致的sync库数据(order_sn:'.$order_sn.')回滚不存在，已删除生产库数据'.PHP_EOL;
                    }
                     // $order_info_created_time = $db->getOne("SELECT created_time from order_info where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                    // if ($order_info_created_time != null && $order_info_created_time < date("Y-m-d H:i:s",strtotime("-25 day"))) {
                        $shipment_status = $db->getRow("SELECT status,shipment_status from shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                        $multi_status = $db->getRow("SELECT status,shipment_status from multi_goods_shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                        if (!empty($shipment_status['status']) && !empty($multi_status['status']) && $shipment_status['status'] != $multi_status['status']) {
                            $db->query("update multi_goods_shipment set status = '{$shipment_status['status']}' where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                            echo '发现可能并发导致的multi 和 shipment的status不一致情况 shipment:'.$shipment_status['status'].' multi_status:'.$multi_status['status'].' 已将multi改为shipment状态'.PHP_EOL;
                        }
                        if (!empty($shipment_status['shipment_status']) && !empty($multi_status['shipment_status']) && $shipment_status['shipment_status'] != $multi_status['shipment_status'] && $multi_status['shipment_status'] != 'PRE_SHIP') {
                            $db->query("update multi_goods_shipment set shipment_status = '{$shipment_status['shipment_status']}' where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                            echo '发现可能并发导致的multi 和 shipment的shipment_status不一致情况 shipment:'.$shipment_status['shipment_status'].' multi_status:'.$multi_status['shipment_status'].' order_sn:'.$order_sn.' 已将multi改为shipment状态'.PHP_EOL;
                        }

                       
			// }

                    $shipment_exist = $db->getOne("SELECT 1 from shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
			if (!$shipment_exist) {
				var_dump($db->getAll("show tables"));
                        $db->query("delete from multi_goods_shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
			$db->query("delete from multi_goods_shipment_goods where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
			$sync_db->query("delete from sync_pinduoduo_order_info where order_sn = '{$order_sn}'");
                        echo '非pdd发现可能并发导致的mutil存在shipment不存在的情况 已删除multi相关数据 order_sn:'.$order_sn.' shop_id:'.$shop_id.PHP_EOL;
                    }
                    if ((isset($response['code']) && $response['code'] == 1) && (isset($response['msg']) && strpos($response['msg'], 'inner_fail') !== false)) {
                        $order_info_created_time = $db->getOne("SELECT created_time from order_info where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                        $shipment_exist = $db->getOne("SELECT 1 from shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                        $sigle_multi_exist = 1;
                        $sigle_multi_exist = $db->getOne("SELECT 1 from multi_goods_shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                        echo '发现shop_id:'.$shop_id.' order_sn:'.$order_sn.'系统多于平台且发生inner_fail错误 生产库各表存在情况:order_info_created_time:'.$order_info_created_time.' shipment_exist:'.$shipment_exist.' sigle_multi_exist:'.$sigle_multi_exist.PHP_EOL;
                        if ($order_info_created_time == null || ($order_info_created_time < date("Y-m-d H:i:s",strtotime("-25 day")) && (!$shipment_exist || !$sigle_multi_exist))) {
                            deleteSyncData($platform_name,$order_sn,$shop_id);
                            if ($order_info_created_time != null) {
                                $db->query("delete from order_info where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                            }
                            if ($shipment_exist) {
                                $db->query("delete from shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                            }
                            if ($sigle_multi_exist) {
                                $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}' and facility_id = {$facility_id}");
                                $db->query("delete from multi_goods_shipment_goods where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                            }
                            $response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
                            echo 'shop_id:'.$shop_id.' order_sn:'.$order_sn.'系统多于平台且发生inner_fail错误 且满足删除数据重跑条件已删除重跑'.PHP_EOL;
                        }
                    }
                }
                echo(date("Y-m-d H:i:s") . " {$shop_id}  platform_minu_our ".count($platform_minu_our)."\r\n");
		$jj = 0;
		foreach($platform_minu_our as $order_sn){
		    if ($jj++ % 100 == 0) {
                        selectRdsByShopId($shop_id);
                    }
                    $response = ExpressApiService::downloadSingleOrder($shop_id,'pinduoduo',$order_sn);
		   $shipment_exist = $db->getOne("SELECT 1 from shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
                    if (!$shipment_exist) {
                        $db->query("delete from multi_goods_shipment where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
			$db->query("delete from multi_goods_shipment_goods where facility_id = {$facility_id} and order_sn = '{$order_sn}'");
			$sync_db->query("delete from sync_pinduoduo_order_info where order_sn = '{$order_sn}'");
                        echo '非pdd发现可能并发导致的mutil存在shipment不存在的情况 已删除multi相关数据 order_sn:'.$order_sn.' shop_id:'.$shop_id.PHP_EOL;
                    } 
		    if ($platform_name == 'taobao') {
                        if (isset($response['msg']) && strpos($response['msg'], '找不到该订单') !== false) {
                            echo 'taobao 找不到该订单:'.$order_sn.' 调用api获取:'.PHP_EOL;
                            $res = ExpressApiService::downloadTaobaoSingleOrderByApi($shop_id,$order_sn);
                            if (isset($res['msg']) && strpos($res['msg'], 'isv.trade-not-exist') !== false) {
                                echo 'taobao 发现三个月前找不到的待发货订单:'.$order_sn.',sync库生产库删除该数据'.PHP_EOL;
                                $sync_db->query("delete from sync_taobao_order_info where tid = '{$order_sn}'");
                                $db->query("delete from order_info where order_sn = '{$order_sn}'");
                                $db->query("delete from shipment where order_sn = '{$order_sn}'");
                                $db->query("delete from multi_goods_shipment where order_sn = '{$order_sn}'");
                                $db->query("delete from multi_goods_shipment_goods where order_sn = '{$order_sn}'");
                            }
                        }
                    }else if ((isset($response['code']) && $response['code'] == 1) || (isset($response['data']['msg']) && strpos($response['data']['msg'], '订单为已发货或退款订单不保存') !== false)) {
                        if (!$sync_db->getOne("SELECT 1 from check_errors where shop_id = {$shop_id} and order_sn = '{$order_sn}'")) {
                           $sync_db->query("INSERT into check_errors (shop_id, platform_name, order_sn, info, type) VALUES ({$shop_id},'{$platform_name}','{$order_sn}','".addslashes(json_encode($response))."','平台多于软件')");
                        }
                    } 
                }
                echo(date("Y-m-d H:i:s") . " [check_result_".((count($our_minu_platform)==0&&count($platform_minu_our)==0)?"none":"catch")."] shop_id:{$shop_id} our_minu_platform ".count($our_minu_platform)." platform_minu_our ".count($platform_minu_our)."\r\n");
        }
}

function getPlatformUnShippedOrderSn($shop_id,$platform_shop_id,$secret,$platform_name,$access_token=null,$app_key=null){
    // if($platform_name != 'pinduoduo'){
        $result = ExpressApiService::downloadOrder($shop_id);
        if(isset($result['code']) && $result['code'] > 0){
            $error_object = new stdClass();
            $error_object->error_code = $result['code'];
            print_r($result);
            return $error_object;
        }
        return $result['data']['orderSnList'];
    // }
    //         $pddClient = null;
    // global $pdd_new_app_config;
    // if (!empty($shop['app_key']) && !empty($pdd_new_app_config) && $shop['app_key'] == $pdd_new_app_config['appkey']) {
    //     $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$shop['access_token']);
    // }else{
    //     $pddClient = new PddClient('714f11c17c974d0bea91c292d53f1745','56ea2c93c8fe90184eb42d13c67af34b66da33d0',$shop['access_token']);
    // }
    global $pdd_new_app_config;
    $pddClient=null;
    if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$access_token);
    }else{
        // c6d589c7ba100f61f02b5373d414a68ce929f971
        $pddClient = new PddClient('9fee082b095f4853b5323427f25dba5e','28846543fd00a55885bf00a71d3148c0117fbf04',$access_token);
    }
    // $pddClient = new PddClient();
    // $pddClient->mallId = $platform_shop_id;
    // $pddClient->serverUrl = 'http://open.yangkeduo.com/api/router';
    // $pddClient->clientSecret = $secret;
    // $pddClient->dataType = 'JSON';

    $order_request = new OrderNumberListGetRequest();
    $order_request->setOrderStatus(1);
    $order_request->setPageSize(100);
    $pdd_order_list = [];
    $i = 0;
    do{
        $i++;
        $order_request->setPage($i);
        $item_pdd_order_list = $pddClient->execute($order_request);

        if(isset($item_pdd_order_list->error_code)){
            if($item_pdd_order_list->error_code == 20004 || $item_pdd_order_list->error_code == 20030){
                ShopModel::enableShop($shop_id);
            }
            return $item_pdd_order_list;
        }
        $pdd_order_list = array_merge($pdd_order_list,$item_pdd_order_list->order_sn_list);
    }while(count($item_pdd_order_list->order_sn_list) == 100);
    $pdd_order_array = array();
    foreach ($pdd_order_list as $order_sn) {
        $pdd_order_array[] = $order_sn->order_sn;
    }
    return $pdd_order_array;
}
//调用接口  刷新订单状态

function myCompare($a,$b){
    if ($a==$b){
        return 0;
    }
    return ($a>$b)?1:-1;
}

function unicodeDecode($unicode_str){
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $unicode_str);
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

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
    }
}
