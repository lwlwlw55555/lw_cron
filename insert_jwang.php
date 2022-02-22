<?php
require("includes/init.php");
echo "[]". (date("Y-m-d H:i:s")). ( " insert_pddyun_sync_users  begin \r\n");
include 'PddClient.php';
include 'request/DdyPdpUserAddRequest.php';
include 'request/DdyPdpUserDeleteRequest.php';
include 'request/DdyPdpUsersGetRequest.php';

$pdp_rds_list = [
    1 => '18A60CFEA72AC548'
];

$action = isset($argv[1])?$argv[1]:'insert';


global $db,$sync_db;
if ($action == 'insert') {
    
    $shops = $sync_db->getAll("select s.* from shop s left join jwang_shop_pdp p on s.shop_id = p.shop_id  where enabled = 1 and p.shop_id is null");
    foreach ($shops as $shop) {
	    //deleteUser($shop['platform_shop_id']);
        insertUser($shop['access_token'],$shop['app_key'],1,$shop['shop_id'], $shop['platform_shop_id']);
    } 
}

/*
for($i = 1; $i< 10; $i++) {
	$list = 	getUsers($i);
	foreach ($list['users'] as $l) {
	
	    deleteUser($l['owner_id']);
	}

}
 */

function insertUser($token,$app_key,$pdp_rds_id,$shop_id, $owner_id){
    global $pdd_new_app_config;
    global $pdp_rds_list;
    $pddClient = null;
    if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
    }
    $request = new DdyPdpUserAddRequest();
    $request->setRdsId($pdp_rds_list[$pdp_rds_id]);
    $request->setHistoryDays(6);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} owner_id:{$owner_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
        if ($result->error_code == 50001 && strpos($result->sub_msg,'商家已经存在') !== false) {

            // global $sync_db;
            // deleteUser($sync_db->getOne("select platform_shop_id from shop where shop_id = {$shop_id}"));
        }
        return false;
    }else if(isset($result->is_success) && $result->is_success){
	    // return $result->total_count;
	    global $sync_db;

	    $sync_db->query("insert into jwang_shop_pdp (shop_id) values ({$shop_id})");
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} owner_id:{$owner_id} token:".$token."调取DdyPdpUserAddRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} owner_id:{$owner_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
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
	    if ($result->sub_code != 'isv.invalid-parameter:owner_id') {
	    	die;
	    }
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
        echo "[]". (date("Y-m-d H:i:s")). "owner_id:{$owner_id} 调取DdyPdpUserDeleteRequest失败 result:".json_encode($result).PHP_EOL;
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). "owner_id:{$owner_id} 调取DdyPdpUserDeleteRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). "owner_id:{$owner_id} 调取DdyPdpUserDeleteRequest失败 result:".json_encode($result).PHP_EOL;
    return false;
}

