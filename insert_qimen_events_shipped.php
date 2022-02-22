<?php
require("includes/init.php");
require("includes/taobaoSDK/TopClient.php");
require("includes/taobaoSDK/request/QimenEventProduceRequest.php");
echo "[]".(date("Y-m-d H:i:s") . " insertQimenEventsShipped  begin \r\n");

global $sync_db;

$last_plan_sync_time = $sync_db->getOne("select last_plan_sync_time from platform_extension where type = 'qimen_shipped' and platform_name = 'taobao'");

if (empty($last_plan_sync_time)) {
	echo "[]". "last_plan_sync_time is empty!".PHP_EOL;
	die;
}

$last_time = date("Y-m-d H:i:s");
while ($last_plan_sync_time < $last_time) {
	$start_time = $last_plan_sync_time;
	$end_time = date("Y-m-d H:i:s",strtotime("{$start_time} +10 minute"))>$last_time?$last_time:date("Y-m-d H:i:s",strtotime("{$start_time} +10 minute"));
	$sql = "select shop_id,tid,consign_time,seller_nick from sync_taobao_order_info where consign_time >= '{$start_time}' and consign_time < '{$end_time}'";
	$orders = $sync_db->getAll($sql);
	try{
		postDataQimen($orders);
	}catch(Exception $e){
	$last_plan_sync_time = $end_time;
		continue;
	}
	$last_plan_sync_time = $end_time;
}
updatePlatformExtensionTime($last_plan_sync_time);

echo "[]".(date("Y-m-d H:i:s") . " insertQimenEventsShipped  end \r\n");

function updatePlatformExtensionTime($last_plan_sync_time){
	global $sync_db;
	$sync_db->query("update platform_extension set last_plan_sync_time = '{$last_plan_sync_time}' where type = 'qimen_shipped' and platform_name = 'taobao'");
}

function postDataQimen($orders){
	global $top_config;
	global $sync_db;
	$c = new TopClient;
	$c->appkey = $top_config['appkey'];
	$c->secretKey = $top_config['secret'];
	foreach ($orders as $order) {
		$token = $sync_db->getOne("select access_token from shop WHERE shop_id = {$order['shop_id']}");
		$req = new QimenEventProduceRequest;
		$req->setStatus("QIMEN_CP_NOTIFY");
		$req->setTid(strval($order['tid']));
		$req->setCreate(date('Ymd',strtotime($order['consign_time'])));
		$req->setNick(strval($order['seller_nick']));
		$req->setExt("{\"rdsName\",\"rm-vy154z9374304872u\"}");
		echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' begin:'.PHP_EOL;
		// $req->putOtherTextParam("tb_eagleeyex_t", "1");
		$resp = $c->execute($req, $token);
		if (isset($resp->is_success)) {
			if ($resp->is_success) {
				echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_NOTIFY插入qimen成功！'.json_encode($resp).PHP_EOL;
			}else{
				echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_NOTIFY插入qimen失败！'.json_encode($resp).PHP_EOL;
			}
		}else{
			echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_NOTIFY插入qimen失败！'.json_encode($resp).PHP_EOL;
		}
		$req->setStatus("QIMEN_CP_OUT");
		$resp = $c->execute($req, $token);
		if (isset($resp->is_success)) {
			if ($resp->is_success) {
				echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_OUT插入qimen成功！'.json_encode($resp).PHP_EOL;
			}else{
				echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_OUT插入qimen失败！'.json_encode($resp).PHP_EOL;
			}
		}else{
			echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' QIMEN_CP_OUT插入qimen失败！'.json_encode($resp).PHP_EOL;
		}
		echo "[]". date("Y-m-d H:i:s").' params:'.json_encode($order).' token:'.$token.' end'.PHP_EOL;
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
    echo "[]".(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}