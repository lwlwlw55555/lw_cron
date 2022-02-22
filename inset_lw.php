<?php
require("includes/init.php");
echo "[]". (date("Y-m-d H:i:s")). ( " insert_pddyun_sync_users  begin \r\n");
include 'PddClient.php';
include 'request/DdyPdpUserAddRequest.php';
include 'request/DdyPdpUserDeleteRequest.php';

$pdp_rds_list = [
    1 => '18A60CFEA72AC548'
];

$action = isset($argv[1])?$argv[1]:'insert';
global $db,$sync_db;
if ($action == 'insert') {
    
    $shops = $sync_db->getAll("select * from shop where enabled = 1 order by shop_id desc limit 2500");
    foreach ($shops as $shop) {
	    deleteUser($shop['platform_shop_id']);
	   // sleep(1);
        insertUser($shop['access_token'],$shop['app_key'],1,$shop['shop_id']);
    } 
}

function insertUser($token,$app_key,$pdp_rds_id,$shop_id){
    global $pdd_new_app_config;
    global $pdp_rds_list;
    $pddClient = null;
    if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
    }
    $request = new DdyPdpUserAddRequest();
    $request->setRdsId($pdp_rds_list[$pdp_rds_id]);
    $request->setHistoryDays(5);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
        if ($result->error_code == 50001 && strpos($result->sub_msg,'商家已经存在') !== false) {

            // global $sync_db;
            // deleteUser($sync_db->getOne("select platform_shop_id from shop where shop_id = {$shop_id}"));
        }
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} token:".$token."调取DdyPdpUserAddRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
    return false;
}

function getUsers($page=1,$app_key=null){
    global $pdd_new_app_config;
    $pddClient  = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUsersGetRequest();
    $request->setPageSize(200);
    $request->setPageNo($page);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 result:".json_encode($result).PHP_EOL;
        return json_decode(json_encode($result),true);
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest成功  result:".json_encode($result).PHP_EOL;
        return json_decode(json_encode($result),true);
    }
    echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 result:".json_encode($result).PHP_EOL;
    return json_decode(json_encode($result),true);
}

function deleteUser($owner_id){
    global $pdd_new_app_config;
    $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUserDeleteRequest();
    $request->setOwnerId($owner_id);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 result:".json_encode($result).PHP_EOL;
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 result:".json_encode($result).PHP_EOL;
    return false;
}

