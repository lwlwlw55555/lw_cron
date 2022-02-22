<?php

require("includes/init.php");
echo("[]".date("Y-m-d H:i:s") . " job  begin \r\n");
include 'PddClient.php';
include 'request/PmcUserPermit.php';

global $db;

$sql = "
    select 
        shop_id,
        access_token,
        app_key as platform_app_key,
        party_id
    from 
        shop 
    where 
        enabled = 1 and 
        expire_time > date_sub(curdate(), interval 1 month) and 
        app_key = 'c48d390942d248659cc6ecf4aeba0b01'
";
$shop_list = $sync_db->getAll($sql);

$platform_app_secret = $db_user->getOne("select platform_app_secret from app where platform_app_key = 'c48d390942d248659cc6ecf4aeba0b01'");
foreach ($shop_list as $shop) {
    selectRdsByShopId($shop['shop_id']);
    $sql = "select 1 from shop where shop_id = {$shop['shop_id']} and is_notify_pdd = 0";
    $is_exists = $db->getOne($sql);
    if (! $is_exists) {
        continue;
    }
    $shop['platform_app_secret'] = $platform_app_secret;
    for ($i = 0; $i < 3; $i++) {
        $result = notifyRegister($shop);
        if ($result['code'] == 0) {
            $sql = "update shop set is_notify_pdd = 1 where shop_id = {$shop['shop_id']}";
            $db->query($sql);
            break;
        }
    }
}


function notifyRegister($shop){
    $client = new PddClient($shop['platform_app_key'],$shop['platform_app_secret'],$shop['access_token']);
    $pmcUserPermit=new PmcUserPermit();
    $response=$client->execute($pmcUserPermit);
    echo("[]"."notify register, param:" . json_encode($shop) . "response:" . json_encode($response) . "\r\n");
    $result = array('code' => 0,'msg'=>'');
    if (empty($response) || isset($response->error_response) || isset($response->error_code)) {
        $result['code'] = 1;
        $result['msg'] = '(发送短信)拼多多接口返回出错:'.json_encode($response);
    }
    return $result;
}
