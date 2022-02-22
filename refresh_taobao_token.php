<?php
require("includes/init.php");
global $db;
if (isset($argv[1])) {
	$shop = $sync_db->getRow("select shop_id,refresh_token,platform_user_id from shop where shop_id = {$argv[1]}");
	$result = refresh_shop_token($shop['refresh_token']);
	echo "[]".date('Y-m-d H:s:m').' shop_id:'.$shop['shop_id'].'刷新taobao-token refresh_token:'.$shop['refresh_token'].' result'.$result.PHP_EOL;
	saveNewToken($result,$shop['shop_id'],$shop['platform_user_id']);
}else{
	$now = date('Y-m-d H:i:s');
	$shop_counts = $sync_db->getOne("select count(1) from shop where platform_code = 'taobao' and refresh_expire_time > '{$now}'");
	for ($i=0;$i<$shop_counts;$i=$i+100) {
		$shops = $sync_db->getAll("select shop_id,refresh_token,platform_user_id from shop where platform_code = 'taobao' and refresh_expire_time > '{$now}' order by refresh_expire_time limit {$i},100");
		foreach($shops as $shop){
			if (isset($shop['refresh_token']) && !empty($shop['refresh_token'])) {
				echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
				$result = refresh_shop_token($shop['refresh_token']);
				echo "[]".date('Y-m-d H:s:m').' shop_id:'.$shop['shop_id'].'刷新taobao-token refresh_token:'.$shop['refresh_token'].' result'.$result.PHP_EOL;
				saveNewToken($result,$shop['shop_id'],$shop['platform_user_id']);
			}
		}
	}
}

// $sql = "
//         SELECT
//             o.oauth_id,
//             o.refresh_token
//         FROM
//             oauth o
//             left join shop s on o.platform_user_id = s.platform_user_id
//         WHERE
//             s.shop_id = null
//         AND o.platform_name = 'taobao'
//         LIMIT {$limit},{$offset}
//         ";
//     $refresh_oauth_list = $db->getAll($sql);
//     foreach ($refresh_oauth_list as $oauth) {
//         $result = refresh_shop_token($oauth['refresh_token']);
//         echo "[]".date('Y-m-d H:s:m').' oauth_id:'.$oauth['oauth_id'].'刷新taobao-token refresh_token:'.$oauth_id['refresh_token'].' result'.$result.PHP_EOL;
//         global $db;
// 		$db->query("update oauth set access_token = '{$result['access_token']}',refresh_token='{$result['refresh_token']}' where oauth_id = {$oauth_id}");
// 		echo "[]".date('Y-m-d H:s:m').' oauth_id:'.$oauth_id.'刷新taobao-token 成功！'.PHP_EOL;
//     }

function saveNewToken($result,$shop_id,$platform_user_id){
	global $sync_db,$db;
	selectRdsByShopId($shop_id);
	if (!empty($result)) {
		$result = json_decode($result,true);
		$refresh_expire_time = date('Y-m-d H:i:s',$result['refresh_token_valid_time']/1000);
		if (isset($result['access_token'])) {
			$sync_db->query("update shop set access_token = '{$result['access_token']}',refresh_token='{$result['refresh_token']}',refresh_expire_time = '{$refresh_expire_time}',enabled=1 where shop_id = {$shop_id}");
			$db->query("update shop set access_token = '{$result['access_token']}',refresh_token='{$result['refresh_token']}',refresh_expire_time = '{$refresh_expire_time}',enabled=1 where shop_id = {$shop_id}");
			$db->query("update oauth set access_token = '{$result['access_token']}',refresh_token='{$result['refresh_token']}' where platform_user_id = '{$platform_user_id}'");
			echo "[]".date('Y-m-d H:s:m').' shop_id:'.$shop_id.'刷新taobao-token 成功！'.PHP_EOL;
		}
	}
}

function refresh_shop_token($refresh_token){
	 // $url = 'https://oauth.taobao.com/token';
	 $url = 'http://erpjst.titansaas.com/taobaooauth/token';
	 global $taobao_client_id;
	 global $taobao_client_secret;
	 global $taobao_redirect_uri;
	 $postfields= array('grant_type'=>'refresh_token',
				 'client_id'=>$taobao_client_id,
				 'client_secret'=>$taobao_client_secret,
				 'redirect_uri'=>$redirect_uri,
				 'refresh_token'=>$refresh_token);
	 $post_data = '';
	 foreach($postfields as $key=>$value){
	 $post_data .="$key=".urlencode($value)."&";}
	 $ch = curl_init();
	 curl_setopt($ch, CURLOPT_URL, $url);
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	 curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	 
	 //指定post数据
	 curl_setopt($ch, CURLOPT_POST, true);

	 //添加变量
	 curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data,0,-1));
	 $output = curl_exec($ch);
	 $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	 curl_close($ch);
	 return $output;
}
 
?>