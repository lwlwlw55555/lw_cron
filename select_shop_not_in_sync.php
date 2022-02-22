<?php
require("includes/init.php");

echo(date("Y-m-d H:i:s") . " begin".PHP_EOL);

global $db_user, $sync_db, $piece_list;

$limit = 0;
echo ("shop in sync but not in drds"."/r/n");
do{
    $shop_list_sql = "select shop_id, default_facility_id, enabled, access_token, date(expire_time) as expire_time from shop limit {$limit}, 1000";
    $limit += 1000;
    $shop_list = $sync_db->getAll($shop_list_sql);
    foreach ($shop_list as $shop){
            $sql = "select facility_id, rds, db from user where facility_id = {$shop['default_facility_id']}";
        $db_message = $db_user->getRow($sql);
        if(empty($db_message)){
            echo ("facility_id : " . $shop['default_facility_id'] . " shop_id : " . $shop['shop_id'] . " not in erpuser");
            die;
        }
        $drds_db = ClsPdo::getInstance($piece_list[$db_message['rds']]);
        $drds_shop_sql = "select shop_id, default_facility_id, enabled, access_token, date(expire_time) as expire_time from {$db_message['db']}.shop where shop_id = {$shop['shop_id']}";
        $drds_shop = $drds_db->getRow($drds_shop_sql);
        if(empty($drds_shop)){
            echo ("facility_id : " . $shop['default_facility_id'] . " shop_id : " . $shop['shop_id'] . " not in drds");
            die;
        }
        if(!($shop['enabled'] == $drds_shop['enabled']
            && $shop['access_token'] == $drds_shop['access_token']
            && $shop['expire_time'] == $drds_shop['expire_time'])){
            if($shop['access_token'] == $drds_shop['access_token']
                && $shop['expire_time'] == $drds_shop['expire_time']
                && $shop['enabled'] != $drds_shop['enabled'] ){
                $enabled = checkToken($shop['access_token']) == true ? 1 : 0;
                $update_enabled_sql = "update shop set enabled = {$enabled} where shop_id = {$shop['shop_id']}";
                $update_drds_enabled_sql = "update {$db_message['db']}.shop set enabled = {$enabled} where shop_id = {$shop['shop_id']}";
                $sync_db->query($update_enabled_sql);
                $drds_db->query($update_drds_enabled_sql);
                echo $shop['shop_id'] . "ok \n";
                continue;
            }
            print_r($shop);
            print_r($drds_shop);
            echo ("erp_{$db_message['db']} . facility_id : " . $shop['default_facility_id'] . " shop_id : " . $shop['shop_id'] . " message error");
            die;
        }
        echo $shop['shop_id'] . "ok \n";
    }
} while (count($shop_list) == 1000);

echo ("shop in drds but not in sync"."/r/n");
$drds_db = ClsPdo::getInstance($piece_list['piece_1']);
for ($i = 0 ; $i < 256; $i++){
    $limit = 0;
    $db = "erp_".$i;
    do{
        $sql = "select shop_id, default_facility_id, enabled, access_token, date(expire_time) as expire_time from {$db}.shop limit {$limit}, 1000";
        $limit += 1000;
        $drds_shop_list = $drds_db->getAll($sql);
        foreach ($drds_shop_list as $drds_shop){
            $sync_shop_sql = "select shop_id, default_facility_id, enabled, access_token, date(expire_time) as expire_time from shop where shop_id = {$drds_shop['shop_id']}";
            $sync_shop = $sync_db->getRow($sync_shop_sql);
            if(empty($sync_db)){
                echo ("facility_id : " . $drds_shop['default_facility_id'] . " shop_id : " . $drds_shop['shop_id'] . " not in sync");
                die;
            }
            if(!($drds_shop['enabled'] == $sync_shop['enabled']
                && $drds_shop['access_token'] == $sync_shop['access_token']
                && $drds_shop['expire_time'] == $sync_shop['expire_time'])){
                print_r($sync_shop);
                print_r($drds_shop);
                echo ("erp_{$i} facility_id : " . $drds_shop['default_facility_id'] . " shop_id : " . $drds_shop['shop_id'] . " message error");
                die;
            }
            echo $drds_shop['shop_id'] . "ok\n";
        }
    } while(count($drds_shop_list) == 1000);
}

function checkToken($token,$app_key=null){
    global $pdd_new_app_config;
    $pddClient = null;
    if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
    }else{
        $pddClient = new PddClient('9fee082b095f4853b5323427f25dba5e','28846543fd00a55885bf00a71d3148c0117fbf04',$token);
    }
    $request = new MallInfoGetRequest();
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
        return false;
    }else if(isset($result->mall_name)){
        return true;
    }
    echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
    return false;
}