<?php
require("includes/init.php");
require("includes/taobaoSDK/TopClient.php");
require("includes/taobaoSDK/request/JushitaJdpUsersGetRequest.php");
require("includes/taobaoSDK/request/JushitaJdpUserAddRequest.php");
echo("[]".date("Y-m-d H:i:s") . " checkJushitaUserInsert  begin \r\n");

global $top_config;
$c = new TopClient;
$c->appkey = $top_config['appkey'];
$c->secretKey = $top_config['secret'];
$req = new JushitaJdpUsersGetRequest;
$page = 0;
$req->setPageSize("200");
$jst_seller_nicks = [];
$total_results = 0;
$is_start = true;
while ($is_start || count($users) == 200) {
	$is_start = false;
	$page ++;
	$req->setPageNo(strval($page));
	$resp = $c->execute($req);
	if(empty($resp)){
		echo "[]".date("Y-m-d H:i:s")."get Jst Users Error:".json_encode($resp).PHP_EOL;
		// die;
		break;
	}
	$resp = (array)$resp;
	if (isset($resp['error_response']) || !isset($resp['users'])) {
		echo "[]".date("Y-m-d H:i:s")."get Jst Users Error:".json_encode($resp).PHP_EOL;
		// die;
		break;
	}
	$total_results = $resp['total_results'];
	$users = (array)$resp['users']->jdp_user;
	foreach ($users as $user) {
		$jst_seller_nicks[] = $user->user_nick;
	}
}
echo "[]".date("Y-m-d H:i:s") . " jst_seller_nicks :".json_encode($jst_seller_nicks).' total_results:'.$total_results.' count:'.count($jst_seller_nicks).PHP_EOL;

global $sync_db;
$sys_seller_nicks = [];
$sys_shop_mapping = [];
$sql_get_sys_jst_shops = "SELECT s.access_token,s.platform_user_name from shop s
left join shop_extension se on s.shop_id = se.shop_id
WHERE s.platform_code  = 'taobao' and se.enabled = 1 and se.shop_mod <> 888";
$sys_shops = $sync_db->getAll($sql_get_sys_jst_shops);
foreach ($sys_shops as $value) {
	$sys_seller_nicks[] = $value['platform_user_name'];
	$sys_shop_mapping[$value['platform_user_name']] = $value['access_token'];
}
echo "[]".date("Y-m-d H:i:s") . " sys_seller_nicks :".json_encode($sys_seller_nicks).PHP_EOL;

$seller_nick_diff = array_diff($sys_seller_nicks,$jst_seller_nicks);
echo "[]".date("Y-m-d H:i:s") . " seller_nick_diff :".json_encode($seller_nick_diff).PHP_EOL;

if (!empty($seller_nick_diff)) {
	foreach ($seller_nick_diff as $value) {
		echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
		echo "[]".date("Y-m-d H:i:s") . " add seller_nick to jst begin params:".$value.' token :'.$sys_shop_mapping[$value].PHP_EOL;
		$req_add = new JushitaJdpUserAddRequest;
		$req_add->setRdsName($top_config['rds_name']);
		$req_add->setHistoryDays($top_config['history_days']);
		$resp_add = $c->execute($req_add, $sys_shop_mapping[$value]);
		if (!empty($resp_add)) {
			// $resp_add_json = json_decode($resp_add);
			// echo "[]".json_encode($resp_add);die;
			$resp_add = (array)$resp_add;
			if(isset($resp_add['error_response']) || !isset($resp_add['jushita_jdp_user_add_response'])){
				if (strpos(json_encode($resp_add),"invalid-sessionkey") !== false) {
					$shop_id = $sync_db->getOne("select shop_id from shop where access_token = '{$sys_shop_mapping[$value]}'");
					if (!empty($shop_id)) {
						$sync_db->query("update shop_extension set enabled = 0 where shop_id = {$shop_id}");
					}
				}
				echo "[]".date("Y-m-d H:i:s") . " add seller_nick to jst params:".$value.' token :'.$sys_shop_mapping[$value].' ERROR repsonse:'.json_encode($resp_add).PHP_EOL;
				continue;
			}
			if ($resp_add['jushita_jdp_user_add_response']->is_success) {
				echo "[]".date("Y-m-d H:i:s") . " add seller_nick to jst params:".$value.' token :'.$sys_shop_mapping[$value].' SUCCESS repsonse:'.json_encode($resp_add).PHP_EOL;
			}else{
				echo "[]".date("Y-m-d H:i:s") . " add seller_nick to jst params:".$value.' token :'.$sys_shop_mapping[$value].' ERROR repsonse:'.json_encode($resp_add).PHP_EOL;
			}
		}else{
			echo "[]".date("Y-m-d H:i:s") . " add seller_nick to jst params:".$value.' token :'.$sys_shop_mapping[$value].' ERROR repsonse:'.json_encode($resp_add).PHP_EOL;
		}
	}
}

echo("[]".date("Y-m-d H:i:s") . " checkJushitaUserInsert  end \r\n");

