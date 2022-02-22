<?php
require("includes/init.php");
require("includes/taobaoSDK/TopClient.php");
require("includes/taobaoSDK/request/QimenEventProduceRequest.php");
require("includes/taobaoSDK/request/CainiaoWaybillIiGetRequest.php");
require("includes/taobaoSDK/domain/WaybillCloudPrintApplyNewRequest.php");
require("includes/taobaoSDK/domain/UserInfoDto.php");
require("includes/taobaoSDK/domain/AddressDto.php");
require("includes/taobaoSDK/domain/TradeOrderInfoDto.php");
require("includes/taobaoSDK/domain/OrderInfoDto.php");
require("includes/taobaoSDK/domain/Item.php");
require("includes/taobaoSDK/domain/PackageInfoDto.php");
require("includes/taobaoSDK/request/LogisticsOfflineSendRequest.php");
echo("[]".date("Y-m-d H:i:s") . " insertQimenEventsCreated  begin \r\n");

global $sync_db;
// $url = "http://gw.api.taobao.com/router/rest";
$url = "http://erpjst.titansaas.com/taobaoapi/router/rest";
if (isset($argv[1]) && $argv[1] == 2) {
	echo "[]".'压测开始'.PHP_EOL;
	$url = "http://mockgw.zjk.taeapp.com/gw";
}

$last_time = date("Y-m-d H:i:s");
if (isset($argv[1]) && $argv[1] == 2) {
	$last_plan_sync_time = $sync_db->getOne("select last_plan_sync_time from platform_extension where type = 'qimen_created' and platform_name = 'taobao'");
}else{
	$last_plan_sync_time = $sync_db->getOne("select last_plan_sync_time from platform_extension where type = 'qimen_created' and platform_name = 'taobao'");
}

if (empty($last_plan_sync_time)) {
	echo "[]"."last_plan_sync_time is empty!".PHP_EOL;
	die;
}

while ($last_plan_sync_time < $last_time) {
	$start_time = $last_plan_sync_time;
	$end_time = date("Y-m-d H:i:s",strtotime("{$start_time} +10 minute"))>$last_time?$last_time:date("Y-m-d H:i:s",strtotime("{$start_time} +10 minute"));
	if (isset($argv[1]) && $argv[1] == 2) {
		$jst_url = "http://erpjst.titansaas.com/jst/order/syncPlatformIncrement";
		$params = array(
			  "start_time" => $start_time,
			  "end_time" => $end_time
			);
		$response = postJsonData($jst_url,json_encode($params));
		$orders = [];
		if ($response['code'] == 0) {
			$orders = $response['data']['orderList'];
		}
	}else{
		$sql = "select shop_id,tid,created_time,seller_nick from sync_taobao_order_info where created_time >= '{$start_time}' and created_time < '{$end_time}'";
		$orders = $sync_db->getAll($sql);
	}
	
	try{
		if (isset($argv[1]) && $argv[1] == 2) {
			postDataQimen2($orders,$url);
		}else{
			postDataQimen1($orders,$url);
		}
	}catch(Exception $e){
	}
	$last_plan_sync_time = $end_time;
}
updatePlatformExtensionTime($last_plan_sync_time);

echo("[]".date("Y-m-d H:i:s") . " insertQimenEventsCreated  end \r\n");

function updatePlatformExtensionTime($last_plan_sync_time){
	global $sync_db;
	$sync_db->query("update platform_extension set last_plan_sync_time = '{$last_plan_sync_time}' where type = 'qimen_created' and platform_name = 'taobao'");
}

function postDataQimen1($orders,$url){
	global $top_config;
	global $sync_db;
	$c = new TopClient;
	$c->gatewayUrl = $url;
	$c->appkey = $top_config['appkey'];
	$c->secretKey = $top_config['secret'];
	foreach ($orders as $order) {
		$token = $sync_db->getOne("select access_token from shop WHERE shop_id = {$order['shop_id']}");
		$req = new QimenEventProduceRequest;
		$req->setStatus("QIMEN_ERP_TRANSFER");
		$req->setTid(strval($order['tid']));
		$req->setCreate(date('Ymd',strtotime($order['created_time'])));
		$req->setNick(strval($order['seller_nick']));
		$req->setExt("{\"rdsName\",\"rm-vy103b06rx249m4p6\"}");
		echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' begin:'.PHP_EOL;
		// $req->putOtherTextParam("tb_eagleeyex_t", "1");
		$resp = $c->execute($req, $token);
		if (isset($resp->is_success)) {
			if ($resp->is_success) {
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_TRANSFER插入qimen成功！'.json_encode($resp).PHP_EOL;
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_TRANSFER插入qimen失败！'.json_encode($resp).PHP_EOL;
				throw new Exception("Error Processing Request", 1);
			}
		}else{
			echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_TRANSFER插入qimen失败！'.json_encode($resp).PHP_EOL;
			throw new Exception("Error Processing Request", 1);
		}

		$req->setStatus("QIMEN_ERP_CHECK");
		$resp = $c->execute($req, $token);
		if (isset($resp->is_success)) {
			if ($resp->is_success) {
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_CHECK插入qimen成功！'.json_encode($resp).PHP_EOL;
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_CHECK插入qimen失败！'.json_encode($resp).PHP_EOL;
				throw new Exception("Error Processing Request", 1);
			}
		}else{
			echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_CHECK插入qimen失败！'.json_encode($resp).PHP_EOL;
			throw new Exception("Error Processing Request", 1);
		}
		echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' end'.PHP_EOL;

		// //电子面单云打印接口
		// $req = new CainiaoWaybillIiGetRequest;
		// $param_waybill_cloud_print_apply_new_request = new WaybillCloudPrintApplyNewRequest;
		// $param_waybill_cloud_print_apply_new_request->cp_code="POSTB";
		// // $param_waybill_cloud_print_apply_new_request->product_code="目前已经不推荐使用此字段，请不要使用";
		// $sender = new UserInfoDto;
		// $address = new AddressDto;
		// $address->city="北京市";
		// $address->detail="花家地社区卫生服务站";
		// $address->district="朝阳区";
		// $address->province="北京";
		// $address->town="望京街道";
		// $sender->address = $address;
		// $sender->mobile="1326443654";
		// $sender->name="Bar";
		// $sender->phone="057123222";
		// $param_waybill_cloud_print_apply_new_request->sender = $sender;
		// $trade_order_info_dtos = new TradeOrderInfoDto;
		// $trade_order_info_dtos->logistics_services="";
		// $trade_order_info_dtos->object_id="1";
		// $order_info = new OrderInfoDto;
		// $order_info->order_channels_type="TB";
		// $order_info->trade_order_list=strval($order['tid']);
		// $trade_order_info_dtos->order_info = $order_info;
		// $package_info = new PackageInfoDto;
		// $package_info->id="1";
		// $items = new Item;
		// $items->count="1";
		// $items->name="衣服";
		// $package_info->items = $items;
		// $package_info->volume="1";
		// $package_info->weight="1";
		// $package_info->total_packages_count="10";
		// $package_info->packaging_description="5纸3木2拖";
		// $package_info->goods_description="服装";
		// $trade_order_info_dtos->package_info = $package_info;
		// $recipient = new UserInfoDto;
		// $address = new AddressDto;
		// $address->city="北京市";
		// $address->detail="花家地社区卫生服务站";
		// $address->district="朝阳区";
		// $address->province="北京";
		// $address->town="望京街道";
		// $recipient->address = $address;
		// $recipient->mobile="1326443654";
		// $recipient->name="Bar";
		// $recipient->phone="057123222";
		// $trade_order_info_dtos->recipient = $recipient;
		// $trade_order_info_dtos->template_url="http://cloudprint.cainiao.com/cloudprint/template/getStandardTemplate.json?template_id=1001";
		// $trade_order_info_dtos->user_id="12";
		// $param_waybill_cloud_print_apply_new_request->trade_order_info_dtos = $trade_order_info_dtos;
		// $param_waybill_cloud_print_apply_new_request->store_code="553323";
		// $param_waybill_cloud_print_apply_new_request->resource_code="DISTRIBUTOR_978324";
		// $param_waybill_cloud_print_apply_new_request->dms_sorting="false";
		// $param_waybill_cloud_print_apply_new_request->three_pl_timing="false";
		// $req->setParamWaybillCloudPrintApplyNewRequest(json_encode($param_waybill_cloud_print_apply_new_request));
		// $resp = $c->execute($req, $token);
		// var_dump($req);
		// var_export($req);
		// var_dump($resp);die;
	}
}

function postDataQimen2($orders,$url){
	global $top_config;
	global $sync_db;
	$c = new TopClient;
	$c->gatewayUrl = $url;
	$c->appkey = $top_config['appkey'];
	$c->secretKey = $top_config['secret'];
	foreach ($orders as $trade_fullinfo_get_response) {
		echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
		$trade_fullinfo_get_response = json_decode($trade_fullinfo_get_response,true);
		$order = $trade_fullinfo_get_response['trade_fullinfo_get_response']['trade'];
		// var_dump($order);die;
		$token = $sync_db->getOne("select access_token from shop WHERE shop_id = {$order['shop_id']}");
		$enabled = $sync_db->getOne("select enabled from shop_extension WHERE shop_id = {$order['shop_id']}");
		if (!empty($enabled) && $enabled == 1) {
			$req = new QimenEventProduceRequest;
			$req->setStatus("QIMEN_ERP_TRANSFER");
			$req->setTid(strval($order['tid']));
			$req->setCreate(date('Ymd',strtotime($order['created'])));
			$req->setNick(strval($order['seller_nick']));
			$req->setExt("{\"rdsName\",\"rm-vy103b06rx249m4p6\"}");
			$req->putOtherTextParam("tb_eagleeyex_t", "1");
			echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' begin:'.PHP_EOL;
			echo "[]".PHP_EOL;
			$resp = $c->execute($req, $token);
			if (isset($resp->is_success)) {
				if ($resp->is_success) {
					echo "[]".date("Y-m-d H:i:s").' params:'.$order['tid'].' token:'.$token.' QIMEN_ERP_TRANSFER插入qimen成功！'.json_encode($resp).PHP_EOL;
				}else{
					echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_TRANSFER插入qimen失败！'.json_encode($resp).PHP_EOL;
					if (strpos(json_encode($resp), 'invalid-sessionKey') !== false) {
						$sync_db->query("update shop_extension set enabled = 0 where shop_id = {$shop_id}");
						continue;
					}
				}
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_TRANSFER插入qimen失败！'.json_encode($resp).PHP_EOL;
			}
			$req->setStatus("QIMEN_ERP_CHECK");
			$resp = $c->execute($req, $token);
			if (isset($resp->is_success)) {
				if (isset($resp->is_success) && $resp->is_success) {
					echo "[]".date("Y-m-d H:i:s").' params:'.$order['tid'].' token:'.$token.' QIMEN_ERP_CHECK插入qimen成功！'.json_encode($resp).PHP_EOL;
				}else{
					echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_CHECK插入qimen失败！'.json_encode($resp).PHP_EOL;
				}
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_ERP_CHECK插入qimen失败！'.json_encode($resp).PHP_EOL;
			}
			$req->setStatus("QIMEN_CP_NOTIFY");
			$resp = $c->execute($req, $token);
			if (isset($resp)) {
				if (isset($resp->is_success) && $resp->is_success) {
					echo "[]".date("Y-m-d H:i:s").' params:'.$order['tid'].' token:'.$token.' QIMEN_CP_NOTIFY插入qimen成功！'.json_encode($resp).PHP_EOL;
				}else{
					echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_NOTIFY插入qimen失败！'.json_encode($resp).PHP_EOL;
				}
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_NOTIFY插入qimen失败！'.json_encode($resp).PHP_EOL;
			}
			
			//电子面单云打印接口
			$req = new CainiaoWaybillIiGetRequest;
			$param_waybill_cloud_print_apply_new_request = new WaybillCloudPrintApplyNewRequest;
			$sss = '{
						"cp_code": "STO",
						"product_code": "STANDARD_EXPRESS",
						"sender": {
							"address": {
								"province": "江苏",
								"city": "无锡",
								"district": "江阴",
								"detail": "测试地址",
								"town": ""
							},
							"name": "张三",
							"phone": "",
							"mobile": "13128888888"
						},
						"trade_order_info_dtos": [
						{
							"recipient": {
								"address": {
									"detail": "中国工商银行承德市支行个人贷款中心",
									"district": "'.strval($order['receiver_district']).'",
									"city": "'.strval($order['receiver_city']).'",
									"province": "'.strval($order['receiver_state']).'"
								},
								"name": "测试账号0",
								"mobile": "18812345678",
								"phone": "18512345678"
							},
							"template_url": "http://cloudprint.cainiao.com/template/standard/201/172",
							"object_id": "1534226054258576518",
							"user_id": "17555720",
							"package_info": {
								"id": "'.strval($order['tid']).'",
								"items": [{
									"count": 1,
									"name": "物品"
								}]
							},
							"order_info": {
								"order_channels_type": "TB",
								"trade_order_list": ["'.strval($order['tid']).'"]
							}
						}]
					}';
			$req->setParamWaybillCloudPrintApplyNewRequest($sss);
			$resp = $c->execute($req, $token);
			if(isset($resp->modules->waybill_cloud_print_response)){
				$waybill_code = $resp->modules->waybill_cloud_print_response[0]->waybill_code;
				echo "[]".date("Y-m-d H:i:s").' params:'.$order['tid'].' token:'.$token.' WaybillCloudPrintApply成功！'.json_encode($resp).PHP_EOL;
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.$sss.' token:'.$token.' WaybillCloudPrintApply失败！'.json_encode($resp).PHP_EOL;
			}


			$req = new QimenEventProduceRequest;
			$req->setTid(strval($order['tid']));
			$req->setCreate(date('Ymd',strtotime($order['created'])));
			$req->setNick(strval($order['seller_nick']));
			$req->setExt("{\"rdsName\",\"rm-vy103b06rx249m4p6\"}");
			$req->putOtherTextParam("tb_eagleeyex_t", "1");
			$req->setStatus("QIMEN_CP_OUT");
			$resp = $c->execute($req, $token);
			if (isset($resp)) {
				if (isset($resp->is_success) && $resp->is_success) {
					echo "[]".date("Y-m-d H:i:s").' params:'.$order['tid'].' token:'.$token.' QIMEN_CP_OUT插入qimen成功！'.json_encode($resp).PHP_EOL;
				}else{
					echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_OUT插入qimen失败！'.json_encode($resp).PHP_EOL;
				}
			}else{
				echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_OUT插入qimen失败！'.json_encode($resp).PHP_EOL;
			}

			//发货
			$req = new LogisticsOfflineSendRequest;
	        $req->setTid(strval($order['tid']));
	        $req->setOutSid(strval($waybill_code));
	        $req->setCompanyCode("STO");
	        $resp = $c->execute($req, $token);
	        if (isset($resp->shipping->is_success)) {
	            if ($resp->shipping->is_success) {
	                echo "[]".date("Y-m-d H:i:s").' params:'.$order['tid'].' token:'.$token.' qimen发货成功！'.json_encode($resp).PHP_EOL;
	            }else{
	                echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' qimen发货失败！'.json_encode($resp).PHP_EOL;
	            }
	        }else{
	            echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' qimen发货失败！'.json_encode($resp).PHP_EOL;
	        }

	        echo "[]".PHP_EOL;
			echo "[]".date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' end'.PHP_EOL;
			echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
		}
	}
}

/**
 * @param $url
 * @param $data
 * @return string
 */
function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data))
    );
    $time_start = microtime(true);
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    $result = json_decode($return_content, true);
    if(isset($result['code']) && $result['code'] == 0) {
        $str = "[ExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
    }else{
        $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
    }
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo("[]".date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}

