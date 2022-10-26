<?php
require("includes/init.php");
$redis = getDpRedis();


if (isset($_REQUEST['restart']) && $_REQUEST['restart']) {
    curl_exec("service dp restart");
    echo json_encode(['code'=>0,'data'=>'']);
    return;
}

if ((isset($_REQUEST['BI-EB-URL']) && !empty($_REQUEST['BI-EB-URL'])) 
    || (isset($_REQUEST['BI-EB-URL']) && !empty($_REQUEST['BI-DB-URL']))) {

	if (!empty($_REQUEST['BI-EB-URL'])) {
        $redis->set('BI-EB-URL',$_REQUEST['BI-EB-URL']);
    }

    if (!empty($_REQUEST['BI-DB-URL'])) {
        $redis->set('BI-DB-URL',$_REQUEST['BI-DB-URL']);
    }
	echo json_encode(['code'=>0,'data'=>'']);
	return;
}else{
	$db_url = $redis->get('BI-DB-URL');
    $eb_uerl = $redis->get('BI-EB-URL');
	echo json_encode(['code'=>0,'data'=>['BI-DB-URL'=>$db_url,'BI-EB-URL'=>$eb_uerl]]);
	return;
}


function getDpRedis() {
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
    $redis->select(11);
    return $redis;
}