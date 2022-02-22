<?php
require("includes/init.php");
$url = 'http://localhost:8080/express_dderpsync_inner';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

$redis = getFacilityRedis();

$default_start_date = date("Y-m-d 00:00:00",strtotime(" -2 day"));
echo date("Y-m-d H:i:s").PHP_EOL;

$facility_id = $argv[1];
if (empty($facility_id)) {
    die('null facility_id');
}

$erp_ddyun_user_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
        "name" => "erpuser"
    );
$erp_ddyun_user_db = ClsPdo::getInstance($erp_ddyun_user_conf);

$erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );
$erp_ddyun_db_conf['name'] = $erp_ddyun_user_db->getOne("select db from user where facility_id = {$facility_id}");
$erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

$sql = "select sku_id from sku where facility_id = {$facility_id} and weight is not null";

$skus = $erp_ddyun_db->getAll($sql);
var_dump($skus);

$redis = getFacilityRedis();
foreach ($skus as $sku) {
    $msg = json_encode(['facility_id'=>$facility_id,'sku_id'=>$sku['sku_id']]);
    echo "lpush order_weight_update_inner ".$msg.PHP_EOL;
    $redis->lpush("order_weight_update", $msg);
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

