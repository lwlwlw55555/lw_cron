<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

$token_leqee = "6502b6ab3ff6297121d02efbcf0c851062143be28e17d";
$token_gyc = "38c846cc6369ab8cb33baebed2f6c3a862143da9e6bf2";

if (!empty($argv[1])) {
    $token_leqee = $argv[1];   
}

if (!empty($argv[2])) {
    $token_gyc =  $argv[2];   
}

global $db;

$token_mapping = $db->getOne("select config_value from common_config where config_key = 'TOKEN_MAPPING'");
if (!empty($token_mapping)) {
    $tokens = json_decode($token_mapping,true);
    if (!empty($tokens['leqee'])) {
        $token_leqee = $tokens['leqee'];
    }
    if (!empty($tokens['gyc'])) {
        $token_gyc = $tokens['gyc'];
    }
}

$url_leqee = 'https://databasehub.leqee.com/api/QuickQueryController';
$url_gyc = 'https://databasehub.guanyc.cn/api/QuickQueryController';

global $url_leqee,$url_gyc,$token_leqee,$token_gyc;

require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo date("Y-m-d H:i:s").PHP_EOL;

$dbs = getDatabaseList();

foreach ($dbs as $key => $value) {
    $sql = "replace into leqee_tables (database_id, database_name, engine, host, memo, port, status) values ('{$value['database_id']}', '{$value['database_name']}', '{$value['engine']}', '{$value['host']}', '{$value['memo']}', '{$value['port']}', '{$value['status']}')";
    $db->query($sql);
}

