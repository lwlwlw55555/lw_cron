<?php
require("includes/init.php");
$ddyun_redis = getDdyunRedis();
// $all_keys = $aliyun_redis->keys('*');

global $sync_db;
$count = 0;
// while(true){
	try{
		$ddyun_redis = getDdyunRedis();
    // $shops = $sync_db->getCol("select shop_id from shop where valid_oauth_time > '2021-11-16 14'");
    // $shops = $sync_db->getCol("select shop_id from sync_order_new_shop_job where created_time > '2021-11-16 14'");

    // $shops = $sync_db->getCol("select shop_id from sync_order_new_shop_job where created_time > '2021-11-17 10'");
    $shops = $sync_db->getCol("select s.shop_id from shop s 
left join  sync_order_new_shop_job j on s.shop_id = j.shop_id and j.created_time >= curdate()
where (s.valid_oauth_time >= curdate() or (s.access_token <> '' and s.created_time >= curdate()))
and j.job_id is null");

    foreach ($shops as $shop) {
      $ddyun_redis->lpush('sync_order_newShop',json_encode(['shop_id'=>$shop]));
      echo 'lpush sync_order_newShop'.json_encode(['shop_id'=>$shop]).PHP_EOL;
      sleep(60);
    }
	}catch(Exception $e){
		echo date("Y-m-d H:i:s").' Exception:'.$e->getMessage();
	}
	$count++;
  echo $count.PHP_EOL;
	// echo date("Y-m-d H:i:s").' 已检查'.$count.' 次'.PHP_EOL;
// }


function getDdyunRedis() {
    global $redis_config;
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => '100.65.5.161',
          'port' => '6379'
    ]);
    $redis->auth('Titanerpredis2020');
    $redis->select('0');
    return $redis;
}