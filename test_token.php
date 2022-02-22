<?php
require("includes/init.php");
$test_db_conf = array(
    "host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com:3306",
    "name" => "erp_test_new",
    "user" => "erp_test",
    "pass" => "zgsdi67f8PhQ",
    "charset" => "utf8",
    "pconnect" => "1",
);

$test_user_db_conf = array(
"host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com:3306",
"name" => "erp_user_test_new",
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
        sd.session_date = curdate()
";
$party_id = $test_user_db->getCol($sql);
if (empty($party_id)) {
    die("party_id empty");
}
$party_id = implode("','", $party_id);

$platform_shop_ids = $test_sync_db->getCol("select platform_shop_id from shop where party_id in ('{$party_id}')");
$platform_shop_ids = implode("','", $platform_shop_ids);

$sql = "
    select 
        distinct u.facility_id
    from 
        session_date sd 
        inner join user u on sd.user_id = u.user_id 
    where 
        sd.session_date = curdate()
";
$facility_id = $test_user_db->getCol($sql);
if (empty($facility_id)) {
    die("facility_id empty");
}
$facility_id = implode("','", $facility_id);

$sql = "select platform_user_id from oauth where facility_id in ('{$facility_id}')";
$oauth_platform_shop_ids = $test_db->getCol($sql);
$oauth_platform_shop_ids = implode("','", $oauth_platform_shop_ids);

$shops = $sync_db->getAll("select platform_user_id, access_token, enabled, expire_time from shop where app_key = 'c48d390942d248659cc6ecf4aeba0b01' and platform_shop_id in ('{$platform_shop_ids}')");
echo date('Y-m-d H:i:s').' shops:'.json_encode($shops).'begin:'.PHP_EOL;
foreach ($shops as $shop) {
    echo PHP_EOL;
    echo date('Y-m-d H:i:s').' shop:'.json_encode($shop).'begin:'.PHP_EOL;
    $test_db->query("update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}' where platform_user_id = '{$shop['platform_user_id']}'");
    echo date('Y-m-d H:i:s')." update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}'  where platform_user_id = '{$shop['platform_user_id']}'".PHP_EOL;
    $test_sync_db->query("update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}'  where platform_user_id = '{$shop['platform_user_id']}'");
    echo date('Y-m-d H:i:s')." update shop set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}'  where platform_user_id = '{$shop['platform_user_id']}'".PHP_EOL;

    $test_db->query("update oauth set access_token = '{$shop['access_token']}', enabled = '{$shop['enabled']}', expire_time = '{$shop['expire_time']}' where platform_user_id = '{$shop['platform_user_id']}'");
    echo date('Y-m-d H:i:s').' shop:'.json_encode($shop).'end:'.PHP_EOL;
}
echo PHP_EOL;
