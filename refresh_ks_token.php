<?php
require("includes/init.php");

date_default_timezone_set("Asia/Shanghai");

require("Services/ExpressApiService.php");
use Services\ExpressApiService;

global $erp_ddyun_db, $sync_db, $db_user;
$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

for($i = 0; $i < 256; $i++){
    $erp_ddyun_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $sql = "select 
            s.shop_id,
            s.refresh_token,
            s.platform_shop_id,
            a.platform_app_key,
            a.platform_app_secret 
        from shop s 
        inner join app a on s.app_key = a.platform_app_key
        where s.platform_name = 'kuaishou' and s.enabled = 1";
    $shops = $erp_ddyun_db->getAll($sql);
    foreach ($shops as $shop){
        refreshKuaiShouToken($shop);
    }
}

function refreshKuaiShouToken($shop){
    global $erp_ddyun_db, $sync_db, $db_user;
    $url = "http://erpjst.titansaas.com/kuaishou/oauth2/refresh_token?app_id={$shop['platform_app_key']}&grant_type=refresh_token&refresh_token={$shop['refresh_token']}&app_secret={$shop['platform_app_secret']}";
    $result = postJsonData($url);
    echo("快手refresh_token，shop_id: {$shop['shop_id']} url:{$url}, result:" . json_encode($result) . PHP_EOL);
    if($result['code']>0){
        return $result;
    }
    $data = $result['data'];
    if (isset($data['result']) && $data['result'] == 1) {
        $access_token = $data['access_token'];
        $refresh_token = $data['refresh_token'];
        $expire_time = date("Y-m-d H:i:s", time()+$data['expires_in']);
        $refresh_expire_time = date("Y-m-d H:i:s", strtotime("+30 day"));
        $sql = "update shop set access_token = '{$access_token}' , refresh_token = '{$refresh_token}', expire_time = '{$expire_time}',refresh_expire_time = '{$refresh_expire_time}', enabled = 1 where shop_id = {$shop['shop_id']}";
        echo "update prod and sync shop ".$sql.PHP_EOL;
        $erp_ddyun_db->query($sql);
        $sync_db->query($sql);
        $sql = "update shop_oauth set expire_time = '{$expire_time}' where platform_shop_id = '{$shop['platform_shop_id']}'";
        echo "update prod shop oauth ".$sql.PHP_EOL;
        $db_user->query($sql);
        $sql = "update shop_extension set enabled = 1 where shop_id = {$shop['shop_id']}";
        echo "update sync shop_extension ".$sql.PHP_EOL;
        $sync_db->query($sql);

        try {
            echo ("快手refresh_token refreshOauthShop, platformShopId: ".$shop['platform_shop_id']. " expireTime:".$expire_time. " accessToken:".$access_token." appKey:".$shop['platform_app_key']." refreshToken:".$refresh_token." refreshExpireTime.".$refresh_expire_time. "/r/n");
            ExpressApiService::refreshOauthShop( $shop['platform_shop_id'], 'kuaishou', $expire_time, $access_token, $shop['platform_app_key'], $refresh_token, $refresh_expire_time );
        } catch ( \Exception $e ) {
            echo("快手refresh_token refreshOauthShop, request:".json_encode($result)." appKey:".$shop['platform_app_key']." exception:".$e->getMessage()."/r/n" );
        }
    }else if(isset($data['error']) && "refresh_token_expired" == $data['error']){
        $sql = "update shop set enabled = 0 where shop_id = {$shop['shop_id']}";
        echo("快手refresh_token，shop_id: {$shop['shop_id']} . {$sql}) /r/n");
        $erp_ddyun_db->query($sql);
    }
}

function postJsonData($url){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $return_content = curl_exec($ch);
    $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($http_status_code == 200){
        $result = json_decode($return_content, true);
        return ['code'=>0,'data'=>$result];
    }else{
        $result = json_decode($return_content, true);
        $error_info = 'http code :'.$http_status_code;
        if(isset($result['error'])){
            $error_info .= ','.$result['error'];
        }
        if(isset($result['error_description'])){
            $error_info .= ','.$result['error_description'];
        }
        return ['code'=>ErrorConstant::CURL_ERROR,'msg'=>$error_info];
    }

}