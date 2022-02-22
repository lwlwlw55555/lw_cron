<?php
require("includes/init.php");

require("Services/ExpressApiService.php");
use Services\ExpressApiService;

date_default_timezone_set("Asia/Shanghai");

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
            a.platform_app_secret,
            s.refresh_expire_time
        from shop s 
        inner join app a on s.app_key = a.platform_app_key
        where s.platform_name = '1688' and s.enabled = 1";
    $shops = $erp_ddyun_db->getAll($sql);
    foreach ($shops as $shop) {
        refresh1688Token($shop);
    }
}

function refresh1688Token($shop){
    global $erp_ddyun_db, $sync_db, $db_user;
    $url = "http://erpjst.titansaas.com/1688api/user/alibaba/reOauth";
    $data = array(
        "app_key" => $shop['platform_app_key'],
        "app_secret" => $shop['platform_app_secret'],
        "refresh_token" => $shop['refresh_token']
    );
    $result = postJsonData($url, $data);
    echo("1688 refresh_token，shop_id: {$shop['shop_id']} url:{$url}, result:" . json_encode($result) . PHP_EOL);
    if($result['code']>0){
        return $result;
    }
    $data = $result['data'];
    if (isset($data['access_token']) && isset($data['expires_in'])) {
        $access_token = $data['access_token'];
        $expire_time = date("Y-m-d H:i:s", time()+$data['expires_in']);
        $sql = "update shop set access_token = '{$access_token}' , expire_time = '{$expire_time}', enabled = 1 where shop_id = {$shop['shop_id']}";
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
            echo ("1688 refresh_token refreshOauthShop, platformShopId: ".$shop['platform_shop_id']. " expireTime:".$expire_time. " accessToken:".$access_token." appKey:".$shop['platform_app_key']." refreshToken:".$shop['refresh_token']." refreshExpireTime.".$shop['refresh_expire_time']. "/r/n");
            ExpressApiService::refreshOauthShop( $shop['platform_shop_id'], '1688', $expire_time, $access_token, $shop['platform_app_key'], $shop['refresh_token'], $shop['refresh_expire_time'] );
        } catch ( \Exception $e ) {
            echo("1688 refresh_token refreshOauthShop, request:".json_encode($result)." shop:".json_encode($shop)." exception:".$e->getMessage()."/r/n" );
        }
    }else if(isset($data['error']) && "refresh_token_expired" == $data['error']){
        $sql = "update shop set enabled = 0 where shop_id = {$shop['shop_id']}";
        echo("1688 refresh_token，shop_id: {$shop['shop_id']} . {$sql})");
        $erp_ddyun_db->query($sql);
    }
}

function postJsonData($url, $data){
    $post_data = '';
    foreach ($data as $key => $value){
        $post_data .= $key."=".$value."&";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    //添加变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data,0,-1));

    $return_content = curl_exec($ch);
    $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($http_status_code == 200){
        $result = json_decode($return_content, true);
        return $result;
    }else{
        $result = json_decode($return_content, true);
        $error_info = 'http code :'.$http_status_code;
        if(isset($result['error'])){
            $error_info .= ','.$result['error'];
        }
        if(isset($result['error_description'])){
            $error_info .= ','.$result['error_description'];
        }
        return ['code'=> '1','msg'=>$error_info];
    }
}