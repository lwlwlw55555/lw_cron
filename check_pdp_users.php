<?php
require("includes/init.php");
echo "[]". (date("Y-m-d H:i:s")). ( " check_pdp_users  begin \r\n");
include 'PddClient.php';
include 'request/DdyPdpUsersGetRequest.php';
include 'request/DdyPdpUserAddRequest.php';
include 'request/DdyPdpUserDeleteRequest.php';
global $pdp_rds_list,$pdp_rds_mapping;
die;
$pdp_rds_mapping = [
    '18A60CFEA72AC548' => 1,
    '9F61C756215728B5' => 2,
    'D1ED8E91442E2111' => 3
];

$pdp_rds_list = [
    1 => '18A60CFEA72AC548',
    2 => '9F61C756215728B5',
    3 => 'D1ED8E91442E2111'
];

$all_users_map = [
    1 => [],
    2 => [],
    3 => []
];

$pre_un_enabled_map = [
    1 => [],
    2 => [],
    3 => []
];

$page = 1;
$is_start = true;
while($is_start || count($users['users']) == 200){
    $is_start = false;
    $users = getUsers($page);
    foreach ($users['users'] as $user) {
        if (isset($pdp_rds_mapping[$user['rds_id']])) {
            if (isset($user['status']) && $user['status'] != 1) {
                $pre_un_enabled_map[$pdp_rds_mapping[$user['rds_id']]][] = $user;
            }else{
                $all_users_map[$pdp_rds_mapping[$user['rds_id']]][] = $user['owner_id'];
            }
        }
    }
    $page ++;
}

for($pdp_rds_id=1;$pdp_rds_id<=3;$pdp_rds_id++){
    echo "[]". (date("Y-m-d H:i:s")). " check_enable pdp_sync_{$pdp_rds_id} begin".PHP_EOL;
    checkShopsEnabledByPdpRdsId($pdp_rds_id,$pre_un_enabled_map[$pdp_rds_id]);
    echo "[]". (date("Y-m-d H:i:s")). " check_enable pdp_sync_{$pdp_rds_id} end".PHP_EOL;
}

for($pdp_rds_id=1;$pdp_rds_id<=3;$pdp_rds_id++){
    echo "[]". (date("Y-m-d H:i:s")). " check_diff pdp_sync_{$pdp_rds_id} begin".PHP_EOL;
    checkShopsDiffByPdpRdsId($pdp_rds_id,$all_users_map[$pdp_rds_id]);
    echo "[]". (date("Y-m-d H:i:s")). " check_diff pdp_sync_{$pdp_rds_id} end".PHP_EOL;
}

function checkShopsEnabledByPdpRdsId($pdp_rds_id,$pre_un_enabled){
    foreach ($pre_un_enabled as $user) {
        echo "[]". (date("Y-m-d H:i:s")). " check_single_enable pdp_sync_{$pdp_rds_id} begin".json_encode($user).PHP_EOL;
        global $sync_db;
        global $db;
        $shop_id = $sync_db->getOne("select shop_id from shop where platform_shop_id = '{$user['owner_id']}' and platform_code = 'pinduoduo'");
        if(!empty($shop_id)){
            if (deleteUser($user['owner_id'],$shop_id)) {
                $sql = "update shop set enabled = 0 where shop_id = {$shop_id}";
                echo "[]". (date("Y-m-d H:i:s")).' '.$sql.PHP_EOL;
                $sync_db->query($sql);
                selectRdsByShopId($shop_id);
                $db->query($sql);
                $shop_mod = $sync_db->getOne("select shop_mod from shop_extension where shop_id = {$shop_id}");
                $sql = "";
                if ($shop_mod == 888) {
                    $sql = "update shop_extension set enabled=0,pdp_rds_id=null,pdp_rds_name=null where shop_id = {$shop_id}";
                }else{
                    $sql = "update shop_extension set shop_mod = mod(shop_id,200),enabled=0,pdp_rds_id=null,pdp_rds_name=null where shop_id = {$shop_id}";
                }
                echo "[]". (date("Y-m-d H:i:s")).' '.$sql.PHP_EOL;
                $sync_db->query($sql);
            }
        }else{
            deleteUser($user['owner_id'],$shop_id);
        }
        echo "[]". (date("Y-m-d H:i:s")). " check_single_enable pdp_sync_{$pdp_rds_id} ".json_encode($user).' shop_id:'.$shop_id.' end'.PHP_EOL;
    }
}

function checkShopsDiffByPdpRdsId($pdp_rds_id,$all_users){
    global $pdp_rds_list;
    $is_start = true;
    $shops = [];
    $start = 0;
    $limit = 5000;
    while($is_start || count($ss)==5000){
        $is_start = false;
        $sql = "select s.platform_shop_id from shop s 
                inner join shop_extension se on s.shop_id = se.shop_id 
                where pdp_rds_id = '{$pdp_rds_list[$pdp_rds_id]}' limit {$start},{$limit}";
        global $sync_db;
        $ss = $sync_db->getCol($sql);
        $shops = array_merge($shops,$ss);
        $start += $limit;
    }
    $diff = array_diff($shops, $all_users);
    if (count($diff) > 1000) {
        echo "[]". (date("Y-m-d H:i:s")). " 发现 pdp_sync_{$pdp_rds_id}比数据库少的shops数量大于1000 暂停此次check count:".count($diff).PHP_EOL;
        die;
    }
    if (!empty($diff)) {
        $sql = "select s.shop_id,s.access_token,s.app_key,platform_shop_id from shop s 
            inner join shop_extension se on s.shop_id = se.shop_id 
            where s.platform_shop_id in ('".implode("','", $diff)."')";
        $shops2 = $sync_db->getAll($sql);
        echo "[]". (date("Y-m-d H:i:s")). " 获取到pdp_sync_{$pdp_rds_id}比数据库少的shops".json_encode($shops2).PHP_EOL;
        foreach($shops2 as $shop){
            if(insertUser($shop['access_token'],$shop['app_key'],$pdp_rds_id,$shop['shop_id'],$shop['platform_shop_id'])){
               $sql = "update shop_extension set shop_mod=255,pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop['shop_id']}";
               echo "[]". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
               $sync_db->query($sql);
            }else{
                deleteUser($sync_db->getOne("select platform_shop_id from shop where shop_id = {$shop['shop_id']}"),null);
                $sql = "update shop_extension set shop_mod=mod(shop_id,200),pdp_rds_id=null,pdp_rds_name=null where shop_id = {$shop['shop_id']}";
                echo "[]". (date("Y-m-d H:i:s")). $sql.PHP_EOL;
                $sync_db->query($sql);
            }
        }
    }
    
    $diff2 = array_diff($all_users, $shops);
    if (count($diff2) > 1000) {
        echo "[]". (date("Y-m-d H:i:s")). " 发现 pdp_sync_{$pdp_rds_id}比数据库多的shops数量大于1000 暂停此次check count:".count($diff2).PHP_EOL;
        die;
    }
    if (!empty($diff2)) {
        $sql = "select s.platform_shop_id from shop s 
                inner join shop_extension se on s.shop_id = se.shop_id 
                where s.platform_shop_id in ('".implode("','", $diff2)."')";
        $platform_shop_ids = $sync_db->getCol($sql);
        $diff_platform_shop_ids = array_diff($diff2, $platform_shop_ids);
        foreach ($diff_platform_shop_ids as $platform_shop_id) {
            echo "[]". (date("Y-m-d H:i:s")). ' 删除pdp没有shop表的店铺 platform_shop_id:'.$platform_shop_id.PHP_EOL;
            deleteUser($platform_shop_id,null);
        }

        $sql = "select s.shop_id,s.access_token,s.app_key from shop s 
                inner join shop_extension se on s.shop_id = se.shop_id 
                where s.platform_shop_id in ('".implode("','", $diff2)."')";
        $shops3 = $sync_db->getAll($sql);
        echo "[]". (date("Y-m-d H:i:s")). " 获取到pdp_sync_{$pdp_rds_id}比数据库多的shops".json_encode($shops3).PHP_EOL;
        foreach($shops3 as $shop){
            $sql = "update shop_extension set shop_mod=255,pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop['shop_id']}";
            echo "[]". (date("Y-m-d H:i:s")). $sql.PHP_EOL;
            $sync_db->query($sql);
        }
    }
  
    // $diff_3 = array_diff($sync_db->getCol("select shop_id from shop_extension where pdp_rds_id = '{$pdp_rds_list[$pdp_rds_id]}'"), $sync_db->getCol("select s.shop_id from shop s 
    //             inner join shop_extension se on s.shop_id = se.shop_id 
    //             where s.platform_shop_id in ('".implode("','", $shops)."')"));
    // if (!empty($diff_3)) {
    //     $sql = "update shop_extension set enabled=0,pdp_rds_id=null,pdp_rds_name=null,shop_mod=mod(shop_id,200) where shop_id in (".implode(",", $diff_3).")";
    //     echo "[]". (date("Y-m-d H:i:s")). $sql.PHP_EOL;
    //     $sync_db->query($sql);
    // }

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
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} mall_id:{$platform_shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
        if ($result->error_code == 50001 && strpos($result->sub_msg,'商家已经存在') !== false) {
            // global $sync_db;
            // $sql = "update shop_extension set shop_mod=255,pdp_rds_id='{$pdp_rds_list[$pdp_rds_id]}',pdp_rds_name='pdp_sync_{$pdp_rds_id}' where shop_id = {$shop_id}";
            // echo "[]". (date("Y-m-d H:i:s"))." ".$sql.PHP_EOL;
            // $sync_db->query($sql);
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
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} mall_id:{$platform_shop_id} token:".$token."调取DdyPdpUserAddRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} mall_id:{$platform_shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
    return false;
}

function getUsers($page=1,$app_key=null,$is_circle=true){
    global $pdd_new_app_config;
    $pddClient  = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUsersGetRequest();
    $request->setPageSize(200);
    $request->setPageNo($page);
    $result = $pddClient->execute($request);
    if (!$is_circle) {
        return json_decode(json_encode($result),true);
    }
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败-error result:".json_encode($result).PHP_EOL;
        // return json_decode(json_encode($result),true);
    }else{
        $response = json_decode(json_encode($result),true);
        if (isset($response['users'])) {
            echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest成功 result:".json_encode($response).PHP_EOL;
            return $response;
        }
    }
    if ($is_circle) {
        $res = [];
        for ($i=1;$i<=10;$i++){
            sleep(1);
            $res = $pddClient->execute($page,null,false);
            echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 重试第{$i}次 res:".json_encode($res).PHP_EOL;
            if (isset($res['users'])) {
                echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest成功 重试第{$i}次 result:".json_encode($res).PHP_EOL;
                return $res;
            }
        }
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 重试已达10次 暂停此次check res:".json_encode($res).PHP_EOL;
        die;
    }
}

function deleteUser($owner_id,$shop_id=null){
    global $pdd_new_app_config;
    $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUserDeleteRequest();
    $request->setOwnerId($owner_id);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 shop_id:{$shop_id} result:".json_encode($result).PHP_EOL;
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest成功 shop_id:{$shop_id} result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 shop_id:{$shop_id} result:".json_encode($result).PHP_EOL;
    return false;
}
