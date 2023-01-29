<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$redis = getDeRedis();

if (isset($_REQUEST['outerIdList']) && !empty($_REQUEST['outerIdList'])) {
    // var_dump($_REQUEST['outerIdList']);
	$redis->set('outerIdList',json_encode($_REQUEST['outerIdList']));
	echo json_encode(['code'=>0,'data'=>'']);
	return;
}if (isset($_REQUEST['is_export']) && $_REQUEST['is_export']) {
    $outerIdListStr = $redis->get('outerIdList');
    $res = getNormalAttachmentOut(json_decode($outerIdListStr,true),'DE电商-赠品忽略编码');
}else{
	$outerIdListStr = $redis->get('outerIdList');
	echo json_encode(['code'=>0,'data'=>['outerIdList'=>json_decode($outerIdListStr,true)]]);
	return;
}


function getDeRedis() {
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
