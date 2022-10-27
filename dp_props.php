<?php
require("includes/init.php");
$redis = getDpRedis();


// 下面注释的三句不能用 第一句没有8199会卡死 第二句可以跑但是因为第一句会卡主 第三局因为权限问题 通过nginx调用php是www用户 www用户执行不了service、systemctl系统服务!!!!!
if (isset($_REQUEST['restart']) && $_REQUEST['restart']) {
    // shell_exec("lsof -i:8199 | awk '{print $2}' | grep -v PID | xargs kill -9");
    // shell_exec("nohup java -jar /opt/bi.jar &");
    // shell_exec("service dp restart");
    $out = shell_exec("sh dp_restart.sh");
    echo json_encode(['code'=>0,'data'=>json_encode($out)]);
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