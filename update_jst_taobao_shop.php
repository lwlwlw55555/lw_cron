<?php
require("includes/init.php");
echo("[]".date("Y-m-d H:i:s") . " updateJstTaobaoShop  begin \r\n");

global $sync_db;
$sql_get_all_taobao_shop = "SELECT shop_id,access_token,platform_user_name from shop WHERE platform_code = 'taobao'";
$shops = $sync_db->getAll($sql_get_all_taobao_shop);

// global $master_config;
// $url = $master_config['jst_url'].'shop/addShop';
$url = 'http://erpjst.titansaas.com/jst/shop/addShop';

foreach ($shops as $shop) {
	echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
	echo "[]".date("Y-m-d H:i:s").' '.json_encode($shop)." add/update begin".PHP_EOL;
    $params = array(
        "shop_id" => $shop['shop_id'],
        "access_token" => $shop['access_token'],
        "platform_user_name" => $shop['platform_user_name']
    );
    $result = postJsonData($url, json_encode($params),0);
    if(!isset($result['code']) || $result['code'] > 0){
		echo "[]"."error: ";
        var_export($result);
        echo "[]".PHP_EOL;
        echo "[]".date("Y-m-d H:i:s").' '.json_encode($shop)."end".PHP_EOL;
        continue;
	}
    echo "[]".date("Y-m-d H:i:s").' '.json_encode($shop)." add/update end".PHP_EOL;
}

echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
echo("[]".date("Y-m-d H:i:s") . " updateJstTaobaoShop  end \r\n");

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

