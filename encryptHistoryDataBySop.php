<?php

require("includes/init.php");
date_default_timezone_set("Asia/Shanghai");
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

global $sync_db;

$shop_id = $argv[1];

$erp_ddyun_db_user_conf = array(
    "host" => "100.65.1.0:32053",
    "name" => "erpuser",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_user_db = ClsPdo::getInstance($erp_ddyun_db_user_conf);
$facility_id = $sync_db->getOne("select default_facility_id from shop where shop_id = {$shop_id}");
$erp_ddyun_db_conf['name'] = $erp_ddyun_user_db->getOne("select db from user where facility_id = {$facility_id}");
$erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

$app = $erp_ddyun_db->getRow("select
            s.shop_id,
            s.access_token,
            a.platform_app_key,
            a.platform_app_secret
        from shop s
        inner join app a on s.app_key = a.platform_app_key
        where s.shop_id = {$shop_id}");

$date = date("Y-m-d H:i:s");
echo $date;
$erp_ddyun_user_db->query("insert into encrypt_shop_dandan(shop_id, state, doing_time) values ({$shop_id}, 'TODO', '{$date}')");
$data = array(
    'facility_id' => $facility_id,
    'shop_id'  => $app['shop_id']
);
ExpressApiService::encryptHistoryData($data);
