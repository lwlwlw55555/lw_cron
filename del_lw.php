<?php
require("includes/init.php");
echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). ( " refresh_shop_into_pdps  begin \r\n");
include 'PddClient.php';
include 'request/DdyPdpUsersGetRequest.php';
include 'request/DdyPdpUserAddRequest.php';
include 'request/DdyPdpUserDeleteRequest.php';
global $sync_db;
global $pdp_rds_list;
global $oms_user_db;
$oms_user_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomsuser"
);
$pdp_rds_list = [
    1 => '18A60CFEA72AC548',
];

$sql = "select shop_id from shop_extension where created_time > '2021-10-24 09' and pdp_rds_id is not null";
$shops = $sync_db->getCol($sql);
if (!empty($shops)) {
    foreach ($shops as $shop_id) {
        deleteUser($sync_db->getOne("select platform_shop_id from shop where shop_id = {$shop_id}"),$shop_id);
        $update_sql = "update shop_extension set shop_mod = mod(shop_id,200),pdp_rds_id = null where shop_id = {$shop_id}";
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 获取到mod>199 pdp_rds_id is null 修改其mod sql:".$update_sql.PHP_EOL;
        $sync_db->query($update_sql);   
    }
}


function deleteUser($owner_id,$shop_id=null){
    global $pdd_new_app_config;
    $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUserDeleteRequest();
    $request->setOwnerId($owner_id);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        if ($result->error_code == 50001 && strpos($result->sub_msg,'商家尚未添加') !== false) {
            echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest成功 shop_id:{$shop_id}  result:".json_encode($result).PHP_EOL;
            return true;
        }
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 shop_id:{$shop_id} result:".json_encode($result).PHP_EOL;
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest成功 shop_id:{$shop_id}  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 shop_id:{$shop_id} result:".json_encode($result).PHP_EOL;
    return false;
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