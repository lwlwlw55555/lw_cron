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
    3 => 'D1ED8E91442E2111',
];
$pdp_rds_id = 3;

$sql = "select s.shop_id,s.access_token,s.app_key,s.platform_shop_id,se.last_plan_sync_time,s.created_time from shop_extension se  inner join shop s on se.shop_id = s.shop_id 
        where is_big_shop > 0 and pdp_rds_id is null and se.enabled = 1 and se.shop_mod <> 888";
        // and s.created_time < date_sub(curdate() ,INTERVAL 1 day)
echo $sql.PHP_EOL;
$shops = $sync_db->getAll($sql);
echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " pdp_sync_2获取到shops".json_encode($shops).PHP_EOL;
foreach($shops as $shop){
    if(empty($shop['app_key']) || $shop['app_key'] != 'c48d390942d248659cc6ecf4aeba0b01'){
        continue;
    }
    $pdp_rds_id = 3;
    if(insertUser($shop['access_token'],$shop['app_key'],$pdp_rds_id,$shop['shop_id'],$shop['platform_shop_id'])){
       $sql = "";
       if ($shop['last_plan_sync_time'] > date("Y-m-d H:i:s",strtotime("{$shop['created_time']} +30 minute"))) {
           $sql = "update shop_extension set shop_mod=255,pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop['shop_id']}";
           echo "[] refresh_shop_into_pdps[incre-done] ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
       }else{
            $sql = "update shop_extension set pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop['shop_id']}";
           echo "[] refresh_shop_into_pdps[incre-doing] ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
       }
       $sync_db->query($sql);
    }
}

$sql = "select shop_id from shop_extension where shop_mod > 199 and pdp_rds_id is null and shop_mod <> 888";
$shops = $sync_db->getCol($sql);
if (!empty($shops)) {
    foreach ($shops as $shop_id) {
        deleteUser($sync_db->getOne("select platform_shop_id from shop where shop_id = {$shop_id}"),$shop_id);
        $update_sql = "update shop_extension set shop_mod = mod(shop_id,200) where shop_id = {$shop_id}";
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 获取到mod>199 pdp_rds_id is null 修改其mod sql:".$update_sql.PHP_EOL;
        $sync_db->query($update_sql);   
    }
}

$sql = "select shop_id from shop_extension where shop_mod < 200 and pdp_rds_id in ('18A60CFEA72AC548','9F61C756215728B5','D1ED8E91442E2111')
            and last_plan_sync_time > DATE_ADD(created_time,INTERVAL 30 minute) ";
$shops = $sync_db->getCol($sql);
if (!empty($shops)) {
    foreach ($shops as $shop_id) {
        $update_sql = "update shop_extension set shop_mod = 255 where shop_id = {$shop_id}";
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 获取到mod<199 pdp_rds_id is not null 修改其mod sql:".$update_sql.PHP_EOL;
        $sync_db->query($update_sql);   
    }
}

global $db_user;
$sql = "update import_center set status = 'DONE',result='' where type in ('recycle_mailnos','order_weight_update','sync_shop_goods','downlad_goods','downlad_order','goods_sku_alias_update','inventory_change_list','order_weight_update','sync_order_newShop','sync_shop_goods','sync_shop_order','sync_sku_inventory','update_erp_goods_alias','update_erp_sku_alias','update_unprint_shipment_shipping','update_unprint_shipment_tactics')
 and status in ('DOING','TODO','FAIL') and created_time < date_sub(now(),interval 5 minute)";
$db_user->query($sql);

global $db_user;
$sql = "select platform_shop_id from shop_oauth where enabled = 1 and expire_time < now() and platform_name = 'pinduoduo'";
$not_sync_users = $db_user->getCol($sql);
if (!empty($not_sync_users)) {
    echo "[] ".(date("Y-m-d H:i:s"))." refresh_shop_into_pdps_shop_oauth_user count(not_sync_users_count) ".count($not_sync_users).PHP_EOL;
    foreach ($not_sync_users as $user) {
        if(deleteUser($user)){
            $sql = "update shop_extension set shop_mod=888,pdp_rds_id=null,pdp_rds_name=null where  platform_code = 'pinduoduo' and shop_id = ".
                $sync_db->getOne("select shop_id from shop where platform_shop_id = '{$user}'");
            if($sync_db->getOne("select shop_id from shop where platform_shop_id = '{$user}'")){        
                $sync_db->query($sql);
            }
            echo "[] refresh_shop_into_pdps_shop_oauth_sync ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
            
            $sql = "update shop_oauth set enabled = 0 where platform_shop_id = '{$user}'";
            $db_user->query($sql);
            echo "[] refresh_shop_into_pdps_shop_oauth_user ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
        }else{
            echo "[] refresh_shop_into_pdps_shop_oauth_not_sync fail! ". (date("Y-m-d H:i:s"))." user".$user.PHP_EOL;

        }
        echo PHP_EOL.PHP_EOL;
    }
}

$sql = "select platform_shop_id from shop_oauth where enabled = 0 and expire_time > now() and platform_name = 'pinduoduo'";
$sync_users = $db_user->getCol($sql);
if (!empty($sync_users)) {
    echo "[] ".(date("Y-m-d H:i:s"))." refresh_shop_into_pdps_shop_oauth_user count(sync_users) ".count($sync_users).PHP_EOL;
     foreach ($sync_users as $user) {
        $shop = $sync_db->getRow("select * from shop where platform_shop_id = '{$user}' and platform_code = 'pinduoduo'");
        if(empty($shop['app_key']) || $shop['app_key'] != 'c48d390942d248659cc6ecf4aeba0b01'){
            if (!empty($shop['app_key']) && $shop['app_key'] != 'c48d390942d248659cc6ecf4aeba0b01') {
                $sql = "update shop_extension set shop_mod=2,enabled=1 where shop_id = {$shop['shop_id']}";
               echo "[] refresh_shop_into_pdps_shop_oauth_sync ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
               $sync_db->query($sql);

               $sql = "update shop_oauth set enabled = 1 where platform_shop_id = '{$user}' and platform_name = 'pinduoduo'";
               $db_user->query($sql);
               echo "[] refresh_shop_into_pdps_shop_oauth_user ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
               
               $redis = getFacilityRedis();
               $redis->lpush("sync_order_newShop", json_encode(['shop_id'=>$shop['shop_id']]));
               echo "[] ".date("Y-m-d H:i:s")." refresh_shop_into_pdps_shop_oauth_user lpush sync_order_newShop ".json_encode(['shop_id'=>$shop['shop_id']]).PHP_EOL;
            }
            continue;
        }
        $pdp_rds_id = 3;
        if(insertUser($shop['access_token'],$shop['app_key'],$pdp_rds_id,$shop['shop_id'],$shop['platform_shop_id'])){
           $sql = "update shop_extension set shop_mod=255,pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop['shop_id']}";
           echo "[] refresh_shop_into_pdps_shop_oauth_sync ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
           $sync_db->query($sql);

           $sql = "update shop_oauth set enabled = 1 where platform_shop_id = '{$user}'";
           $db_user->query($sql);
           echo "[] refresh_shop_into_pdps_shop_oauth_user ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
           
           $redis = getFacilityRedis();
           $redis->lpush("sync_order_newShop", json_encode(['shop_id'=>$shop['shop_id']]));
           echo "[] ".date("Y-m-d H:i:s")." refresh_shop_into_pdps_shop_oauth_user lpush sync_order_newShop ".json_encode(['shop_id'=>$shop['shop_id']]).PHP_EOL;
        }else{
            echo "[] refresh_shop_into_pdps_shop_oauth_sync fail! ". (date("Y-m-d H:i:s"))." user".$user.PHP_EOL;
        }
        echo PHP_EOL.PHP_EOL;
    }
}



$sql = "delete from platform_error_order where remark = 'PDD_DECRYPT_MASK_EXCEPTION'";
echo "[] refresh_shop_into_pdps_delete_PDD_DECRYPT_MASK_EXCEPTION ".$sql.' count'. ($sync_db->query($sql)).PHP_EOL;
/*
$sql = "delete from platform_error_order where retry_count = 4 and created_time < date_sub(CURRENT_DATE(), INTERVAL 1 day)";
$sync_db->query($sql);
echo "[] refresh_shop_into_pdps_delete_yesterday_4 ".$sql.' count'. ($sync_db->query($sql)).PHP_EOL;
*/
$sql = "delete from platform_error_order where created_time < date_sub(now(), interval 7 day)";
echo "[] refresh_shop_into_pdps_delete ".$sql.' count'. ($sync_db->query($sql)).PHP_EOL;


$sql = "update platform_error_order set retry_count = 0 where retry_count >= 4";
$count = $sync_db->query($sql);
echo "[] refresh_shop_into_pdps_update_peo ". (date("Y-m-d H:i:s")). ' '.$sql.' count'.$count.PHP_EOL;


$sql = "delete p.* from platform_error_order p left join shop s on p.shop_id = s.shop_id where s.shop_id is null";
$count = $sync_db->query($sql);
echo "[] refresh_shop_into_pdps_delete_peo ". (date("Y-m-d H:i:s")). ' '.$sql.' count'.$count.PHP_EOL;

$sql = "update sync_order_new_shop_job set status = 'DONE',sync_order_start_time=created_time where status in ('TODO','FAIL') and created_time < date_sub(now(),interval 5 minute)";
$sync_db->query($sql);

$sql = "update sync_order_new_shop_job set status = 'DONE',sync_order_start_time=created_time where status in ('FAIL') and created_time < date_sub(now(),interval 1 minute)";
$sync_db->query($sql);

$sql ="update shop_extension set shop_mod = shop_mod+round(rand()*(20-1)+1)
where last_plan_sync_time < date_sub(now(), interval 5 minute)
and platform_code <> 'pinduoduo' and shop_mod < 200 and enabled = 1";
$sync_db->query($sql);

$sql ="update shop_extension 
set last_plan_sync_time = now() 
where last_plan_sync_time < date_sub(now(), interval 10 minute)
and pdp_rds_id is not null and shop_mod < 200 and enabled = 1";
$sync_db->query($sql);

$sql = "update shop_extension set shop_mod = mod(shop_id,20) where shop_mod = 888";
$sync_db->query($sql);

$oms_user = ClsPdo::getInstance($oms_user_db_conf);
$sql = "select async_job_id from async_job
where async_job_key = 'sync_shop_goods' and status <> 'DONE' 
and created_time > date_sub(now(),INTERVAL 10 minute)";
$ids = $oms_user->getCol($sql);
if(!empty($ids)){
    $sql ="update async_job set status = 'DONE' where async_job_id in (".implode(",",$ids ).")";
    echo "[]".$sql.PHP_EOL;
    $oms_user->query($sql);
}



function insertUser($token,$app_key,$pdp_rds_id,$shop_id,$platform_shop_id=null){
    global $pdd_new_app_config;
    global $pdp_rds_list;
    $pddClient = null;
    if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
    }
    $request = new DdyPdpUserAddRequest();
    $request->setRdsId($pdp_rds_list[$pdp_rds_id]);
    $request->setHistoryDays(2);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} mall_id:{$platform_shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
        if ($result->error_code == 50001 && strpos($result->sub_msg,'商家已经存在') !== false) {
            global $sync_db;
            $sql = "update shop_extension set shop_mod=255,pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop_id}";
            echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
            $sync_db->query($sql);
            return true;
        }
        if ($result->error_code == 10019 && strpos($result->sub_msg,'access_token') !== false) {
            global $sync_db,$db;
            $sync_db->query("update shop_extension set shop_mod=mod(shop_id,200),pdp_rds_id = null,pdp_rds_name=null,enabled = 0 where shop_id = {$shop_id}");
            $sync_db->query("update shop set enabled = 0 where shop_id = {$shop_id}");
            selectRdsByShopId($shop_id);
            $db->query("update shop set enabled = 0 where shop_id = {$shop_id}");
        }
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} mall_id:{$platform_shop_id} token:".$token."调取DdyPdpUserAddRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} mall_id:{$platform_shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
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
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 result:".json_encode($result).PHP_EOL;
        return json_decode(json_encode($result),true);
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest成功  result:".json_encode($result).PHP_EOL;
        return json_decode(json_encode($result),true);
    }
    echo "[] refresh_shop_into_pdps ". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 result:".json_encode($result).PHP_EOL;
    return json_decode(json_encode($result),true);
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