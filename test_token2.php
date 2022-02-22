<?php
require("includes/init.php");
$test_db_conf = array(
        "host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com:3306",
        "name" => "erp_test",
        "user" => "erp_test",
        "pass" => "zgsdi67f8PhQ",
        "charset" => "utf8",
        "pconnect" => "1",
);

$test_user_db_conf = array(
    "host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com:3306",
    "name" => "erp_user_test",
    "user" => "erp_test",
    "pass" => "zgsdi67f8PhQ",
    "charset" => "utf8",
    "pconnect" => "1",
);

$test_sync_db_conf = array(
        "host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com",
        "name" => "erpsync_test",
        "user" => "erp_test",
        "pass" => "zgsdi67f8PhQ",
        "charset" => "utf8",
        "pconnect" => "1",
);

$test_db = ClsPdo::getInstance($test_db_conf);
$test_user_db = ClsPdo::getInstance($test_user_db_conf);
$test_sync_db = ClsPdo::getInstance($test_sync_db_conf);

$sql = "
    select 
        distinct u.party_id
    from 
        session_date sd 
        inner join user u on sd.user_id = u.user_id 
    where 
        sd.session_date = curdate() and sd.user_id = 99
";
$party_id = $test_user_db->getCol($sql);
if (empty($party_id)) {
    die("party_id empty");
}
$party_id = implode("','", $party_id);

$platform_shop_ids = $test_sync_db->getCol("select platform_shop_id from shop where party_id in ('{$party_id}')");
$platform_shop_ids = implode("','", $platform_shop_ids);


$shops = $db->getAll("select platform_user_id, access_token, enabled, expire_time from shop where app_key = 'c48d390942d248659cc6ecf4aeba0b01' and platform_shop_id in ('{$platform_shop_ids}')");
echo date('Y-m-d H:i:s').' shops:'.json_encode($shops).'begin:'.PHP_EOL;
foreach ($shops as $shop) {
    echo PHP_EOL;
    echo date('Y-m-d H:i:s').' shop:'.json_encode($shop).'begin:'.PHP_EOL;
    $test_db->query("update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}' where platform_user_id = '{$shop['platform_user_id']}'");
    echo date('Y-m-d H:i:s')." update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}'  where platform_user_id = '{$shop['platform_user_id']}'".PHP_EOL;
    $test_sync_db->query("update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}'  where platform_user_id = '{$shop['platform_user_id']}'");
    echo date('Y-m-d H:i:s')." update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}'  where platform_user_id = '{$shop['platform_user_id']}'".PHP_EOL;
    echo date('Y-m-d H:i:s').' shop:'.json_encode($shop).'end:'.PHP_EOL;
}
$oauths = $db->getAll("select platform_user_id, access_token, enabled, expire_time from oauth where platform_app_key = 'c48d390942d248659cc6ecf4aeba0b01'  and platform_user_id in ('{$platform_shop_ids}')");
echo date('Y-m-d H:i:s').' oauths:'.json_encode($oauths).'begin:'.PHP_EOL;
echo ("select platform_user_id, access_token, enabled, expire_time from oauth where platform_app_key = 'c48d390942d248659cc6ecf4aeba0b01'  and platform_user_id in ('{$platform_shop_ids}')");
die;
foreach ($oauths as $oauth) {
    echo PHP_EOL;
    echo date('Y-m-d H:i:s').' oauth:'.json_encode($oauth).'begin:'.PHP_EOL;
    $test_db->query("update oauth set access_token = '{$oauth['access_token']}', enabled = '{$oauth['enabled']}', expire_time = '{$oauth['expire_time']}' where platform_user_id = '{$oauth['platform_user_id']}'");
    echo date('Y-m-d H:i:s')." update oauth set access_token = '{$oauth['access_token']}', enabled = '{$oauth['enabled']}', expire_time = '{$oauth['expire_time']}' where platform_user_id = '{$oauth['platform_user_id']}'".PHP_EOL;
    echo date('Y-m-d H:i:s').' oauth:'.json_encode($oauth).'end:'.PHP_EOL;
}
echo PHP_EOL;
