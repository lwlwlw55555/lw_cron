<?php
require("includes/init.php");
$aliyun_redis = getAliyunRedis();
$ddyun_redis = getDdyunRedis();
// $all_keys = $aliyun_redis->keys('*');

$count = 0;
while(true){
	try{
		$aliyun_redis = getAliyunRedis();
		$ddyun_redis = getDdyunRedis();
		$msg = $aliyun_redis->rpop('update_erp_sku_alias');
		if (!empty($msg)) {
			$ddyun_redis->lpush('update_erp_sku_alias',$msg);
      echo 'lpush update_erp_sku_alias'.$msg.PHP_EOL;
		}else{
			// die;
      sleep(2);
		}
	}catch(Exception $e){
		echo date("Y-m-d H:i:s").' Exception:'.$e->getMessage();
	}
	$count++;
	echo date("Y-m-d H:i:s").' 已检查'.$count.' 次'.PHP_EOL;
}


function getAliyunRedis() {
    global $redis_config;
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => 'saas-288bc6c5-master',
          'port' => '6379'
    ]);
    $redis->auth('Titanerpredis2020');
    $redis->select('11');
    return $redis;
}

function getDdyunRedis() {
    global $redis_config;
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => 'saas-288bc6c5-master',
          'port' => '6379'
    ]);
    $redis->auth('Titanerpredis2020');
    $redis->select('0');
    return $redis;
}