<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$redis = getDeRedis();

// $_REQUEST['is_export'] = true;
if (isset($_REQUEST['danengOuterIdList']) && !empty($_REQUEST['danengOuterIdList'])) {
    // var_dump($_REQUEST['danengOuterIdList']);
    $redis->set('danengOuterIdList',json_encode($_REQUEST['danengOuterIdList']));
    echo json_encode(['code'=>0,'data'=>'']);
    return;
}if (isset($_REQUEST['is_export']) && $_REQUEST['is_export']) {
    $outerIdListStr = $redis->get('danengOuterIdList');
    $res = getNormalAttachmentOut([array_merge(['sku编码'],json_decode($outerIdListStr,true))],'DE电商-赠品忽略编码');
}else{
    $outerIdListStr = $redis->get('danengOuterIdList');
    echo json_encode(['code'=>0,'data'=>['danengOuterIdList'=>json_decode($outerIdListStr,true)]]);
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
