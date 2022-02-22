<?php
require("includes/init.php");
$url = 'http://100.65.128.171:10317';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

global $erp_ddyun_db;

for($ii = 0; $ii < 256; $ii++){
// echo date("Y-m-d H:i:s").' '.(isset($opt_params['mod_index'])?$opt_params['mod_index']:"ALL")." erp_{$ii} delete_check begin".PHP_EOL;
$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_history_db_conf = array(
    "host" => "100.65.2.183:32058",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_db_conf['name'] = 'erp_'.$ii;
$erp_history_db_conf['name'] = 'erp_'.$ii;

$erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
$erp_history_db = ClsPdo::getInstance($erp_history_db_conf);

	$shop_ids = $erp_ddyun_db->getAll("select shop_id,platform_code,back_created_time from shop_back where back_created_time > '2021-11-04 09:00:00' and back_created_time<'2021-11-04 10:10:00'");
	if (!empty($shop_ids)) {
	echo date("Y-m-d H:i:s").' '.'erp_'.$ii.' '.json_encode($shop_ids).PHP_EOL;
		// var_dump($shop_ids);
	}
}

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
    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}

function getFacilityRedis() {
    global $redis_config;
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => $redis_config['host'],
          'port' => $redis_config['port']
    ]);
    if ($redis_config['auth']) {
        $redis->auth($redis_config['auth']);
    }
    if ($redis_config['database']) {
        $redis->select($redis_config['database']);
    }
    return $redis;
}
