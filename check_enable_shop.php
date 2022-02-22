<?php
require("includes/init.php");
include 'PddClient.php';
include 'request/MallInfoGetRequest.php';
global $db,$sync_db,$db_user;
	$minute_interval = 5;
	if (! empty(getopt('', ['interval_minute:']))){
        $minute_interval = intval(getopt('', ['interval_minute:'])['interval_minute']);
	}

	$sql2 = "select shop_id,access_token,enabled,app_key from shop where platform_code = 'pinduoduo' and enabled = 1 and last_updated_time > DATE_SUB(now(),INTERVAL ".$minute_interval." minute)";
	if (isset($opt_params['shop_id'])) {
		$sql2 = "select shop_id,access_token,enabled,app_key from shop where shop_id = {$opt_params['shop_id']}";
	}
	$shops = $sync_db->getAll($sql2);
	$i = 0;
	echo date("Y-m-d H:i:s") . " check_enable_shop begin\r\n";
	foreach ($shops as $shop) {
        $i++;
        selectRdsByShopId($shop['shop_id']);
		$shop = $db->getRow("select shop_id, access_token,enabled,app_key, abs(unix_timestamp(now()) - unix_timestamp(last_updated_time)) tdiff from shop where shop_id = {$shop['shop_id']}");
		$sync_shop = $sync_db->getRow("select shop_id, access_token,enabled,app_key,abs(unix_timestamp() - unix_timestamp(last_updated_time)) tdiff from shop where shop_id = {$shop['shop_id']}");

		if ($shop && $shop['tdiff'] < 60) {
			continue;
		}
		if ($sync_shop && $sync_shop['tdiff'] < 60) {
			continue;
		}

		if (empty($sync_shop)) {
			$full_shop = $db->getRow("select platform_shop_id, platform_shop_secret, shop_name, access_token, created_user, created_time, last_updated_time, default_facility_id, platform_code, is_auto_merge, enabled, is_notify_shipped, party_id, auto_merge, platform_name, pay_id, expire_time, app_id, refresh_token, platform_user_id, platform_user_name, refresh_expire_time, erp_pay_id, version, app_key from shop where shop_id = {$shop['shop_id']}");
			$sql_insert_shop = "INSERT INTO shop (shop_id,platform_shop_id, platform_shop_secret, shop_name, access_token, created_user, created_time, last_updated_time, default_facility_id, platform_code, is_auto_merge, enabled, is_notify_shipped, party_id, auto_merge, platform_name, pay_id, expire_time, app_id, refresh_token, platform_user_id, platform_user_name, refresh_expire_time, erp_pay_id, version, app_key) 
			VALUES ({$shop['shop_id']},'{$full_shop['platform_shop_id']}', '{$full_shop['platform_shop_secret']}', '{$full_shop['shop_name']}', '{$full_shop['access_token']}', '{$full_shop['created_user']}', '{$full_shop['created_time']}', '{$full_shop['last_updated_time']}', {$full_shop['default_facility_id']}, '{$full_shop['platform_code']}', {$full_shop['is_auto_merge']}, {$full_shop['enabled']}, 
				{$full_shop['is_notify_shipped']}, {$full_shop['party_id']}, {$full_shop['auto_merge']}, '{$full_shop['platform_name']}', {$full_shop['pay_id']}, '{$full_shop['expire_time']}', '{$full_shop['app_id']}', '{$full_shop['refresh_token']}', ".(empty($full_shop['platform_user_id'])?"null":"'{$full_shop['platform_user_id']}'").", '{$full_shop['platform_user_name']}', '{$full_shop['refresh_expire_time']}', {$full_shop['erp_pay_id']}, '{$full_shop['version']}', '{$full_shop['app_key']}');";
			$sync_db->query($sql_insert_shop);
			$sql_insert_extension = "INSERT INTO shop_extension (shop_id, platform_code, created_time, last_updated_time, last_refresh_route_time, last_plan_sync_time, last_plan_sync_wait_ship_time, last_plan_sync_shipped_time, last_plan_sync_rate_time, is_big_shop, enabled, shop_mod, version) VALUES ({$shop['shop_id']}, '{$full_shop['platform_code']}', now(), now(), now(), now(), now(), now(), null, 0, {$full_shop['enabled']}, 0, '{$full_shop['version']}');";
			$sync_db->query($sql_insert_extension);
			$sync_shop = $sync_db->getRow("select access_token,enabled,app_key from shop where shop_id = {$shop['shop_id']}");
			echo date("Y-m-d H:i:s") . " check_enable_shop total:".count($shops)." i:{$i} shop_id={$shop['shop_id']} check_result:sync not exists inner_shop: ". json_encode($shop) ."\r\n";
			continue;
		}
		$msg = "";
		if ($sync_shop['access_token'] !== $shop['access_token']) {
			if(checkToken($sync_shop['access_token'],$sync_shop['app_key'])){
				$sql_update_token = "update shop set access_token = '{$sync_shop['access_token']}' where shop_id = {$shop['shop_id']};";
				$db->query($sql_update_token);
				$msg .= " inner_shop access_token error";
			}else{
				if (checkToken($shop['access_token'],$shop['app_key'])) {
					$msg .= " sync_shop access_token error";
				}else{
					$msg .= " all_shop access_token error";
				}
				$sql_update_token = "update shop set access_token = '{$shop['access_token']}' where shop_id = {$shop['shop_id']};";
				$sync_db->query($sql_update_token);
			}
		}
		if ($sync_shop['enabled'] !== $shop['enabled']) {
			$sql_update_enabled = "update shop set enabled = 1 where shop_id = {$shop['shop_id']};";
			$sync_db->query($sql_update_enabled);
			$db->query($sql_update_enabled);

			$sql_update_extension_enabled = "update shop_extension set enabled = 1 where shop_id = {$shop['shop_id']};";
			$sync_db->query($sql_update_extension_enabled);
			$msg .= " enabled error";
		}
		if ($sync_shop['app_key'] !== $shop['app_key']) {
			if(checkToken($sync_shop['access_token'],$sync_shop['app_key'])){
				$sql_update_app_key = "update shop set app_key = '{$sync_shop['app_key']}' where shop_id = {$shop['shop_id']};";
				$db->query($sql_update_app_key);
				$msg .= " inner_shop app_key error";
			}else{
				if (checkToken($shop['access_token'],$shop['app_key'])) {
					$msg .= " sync_shop app_key error";
				}else{
					$msg .= " all_shop app_key error";
				}
				$sql_update_app_key = "update shop set app_key = '{$shop['app_key']}' where shop_id = {$shop['shop_id']};";
				$sync_db->query($sql_update_app_key);
				$msg .= " sync_shop app_key error";
			}
		}
		if ($msg) {
			echo date("Y-m-d H:i:s") . " check_enable_shop total:".count($shops)." i:{$i} shop_id={$shop['shop_id']} check_result:{$msg} inner_shop:". json_encode($shop) ." sync_shop:". json_encode($sync_shop) ."\r\n";
		} else {
			echo date("Y-m-d H:i:s") . " check_enable_shop total:".count($shops)." i:{$i} shop_id={$shop['shop_id']} check_result:ok \r\n";
		}

	}
	echo date("Y-m-d H:i:s") . " check_enable_shop end\r\n";


	function checkToken($token,$app_key=null){
		global $pdd_new_app_config;
		$pddClient = null;
		if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
			$pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
		}else{
			$pddClient = new PddClient('9fee082b095f4853b5323427f25dba5e','28846543fd00a55885bf00a71d3148c0117fbf04',$token);
		}
		$request = new MallInfoGetRequest();
		$result = $pddClient->execute($request);
		if (isset($result->error_code)) {
			echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
			return false;
		}else if(isset($result->mall_name)){
			return true;
		}
		echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
		return false;
	}
