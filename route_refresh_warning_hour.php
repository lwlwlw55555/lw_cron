<?php
require("includes/init.php");
require_once 'includes/predis-1.1/autoload.php';

echo("[]" . date("Y-m-d H:i:s") . " route_refresh_warning_hour begin" . PHP_EOL);
global $db_user;

global $sync_customer_redis_config;

$redis = new Predis\Client([
    'host' => $sync_customer_redis_config['host'],
    'port' => $sync_customer_redis_config['port']
]);
if ($sync_customer_redis_config['auth']) {
    $redis->auth($sync_customer_redis_config['auth']);
}
$redis->select($sync_customer_redis_config['database']);

$index = 0;
while (true) {
    $sql = "select * from express_warning_setting where express_warning_setting_id > {$index} limit 1000";
    $records = $db_user->getAll($sql);
    if ($records == null || count($records) == 0) {
        break;
    }
    $index = $records[count($records) - 1]['express_warning_setting_id'];
    update_redis($records, $redis);
}

function update_redis($records, $redis)
{
    foreach ($records as $record) {
        $exception_hour_arr = array(
            'waitAccept' => intval($record['wait_accept']),
            'accept' => intval($record['accept']),
            'station' => intval($record['station']),
            'jzhwRegion' => intval($record['jzhw_region']),
            'jjjRegion' => intval($record['jjj_region']),
            'sameProvince' => intval($record['same_province']),
            'diffProvince' => intval($record['diff_province']),
            'specialRegion' => intval($record['special_region'])
        );
        $hash_key = $record['type'] == 'NORMAL' ? $record['facility_id'] : $record['facility_id'] . $record['type'];
        $redis->hset('warning_hour', $hash_key, json_encode($exception_hour_arr));
        echo("[]" . date("Y-m-d H:i:s") . " route_refresh_warning_hour facility_id:{$record['facility_id']}" . PHP_EOL);
    }
}

echo("[]" . date("Y-m-d H:i:s") . " route_refresh_warning_hour end" . PHP_EOL);