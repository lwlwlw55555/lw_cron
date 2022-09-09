<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

$redis = getRedis();

if (isset($_REQUEST['control_status']) && !empty($_REQUEST['control_status'])) {
	$redis->set('Onlinestatus',$_REQUEST['control_status']);
	echo json_encode(['code'=>0,'data'=>'']);
	return;
}else{
	$status = $redis->get('Onlinestatus');
	echo json_encode(['code'=>0,'data'=>['control_status'=>$status]]);
	return;
}


function getRedis() {
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