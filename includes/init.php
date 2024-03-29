<?php
/* 初始化数据库类 */
define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));
$ROOT_PATH = ROOT_PATH;
date_default_timezone_set("Asia/Shanghai");

// ini_set("display_warning", 0);
// error_reporting(E_ALL & ~E_NOTICE);

// error_reporting(E_ALL & ~E_WARNING);
//仅显示错误 去掉notice
error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);

require(ROOT_PATH . 'config/master_config.php');
require(ROOT_PATH . "includes/cls_mysql.php");
require(ROOT_PATH . "includes/ClsPdo.php");
require(ROOT_PATH . "includes/function.php");
require(ROOT_PATH . "includes/bytes.php");
require(ROOT_PATH . "includes/class.phpmailer.php");
require(ROOT_PATH . "includes/class.smtp.php");
require(ROOT_PATH . "includes/PHPExcel/PHPExcel.php");
global $db, $db_user, $sync_db, $route_drds_db, $stat_drds_db;
global $opt_params;
global $url_leqee,$url_gyc;

$url_leqee = 'https://databasehub.leqee.com/api/QuickQueryController';
$url_gyc = 'https://databasehub.guanyc.cn/api/QuickQueryController';

$opt_params = getopt('', ['rds:', 'db:', 'route_rds:', 'route_db:', 'facility_id:', 'shop_id:', 'mod:', 'mod_index:', 'order_sn:', 'start:', 'end:', 'limit:']);
// echo date("Y-m-d H:i:s") . " getopt:" . json_encode($opt_params) . PHP_EOL;

if(isset($db_conf)){
    $db = ClsPdo::getInstance($db_conf);
}

if(isset($db_user_conf)){
    $db_user = ClsPdo::getInstance($db_user_conf);
}

if(isset($sync_db_conf)){
    $sync_db = ClsPdo::getInstance($sync_db_conf);
}

if(isset($route_db_conf)){
    $route_drds_db = ClsPdo::getInstance($route_db_conf);
}

if(isset($stat_drds_conf)){
    $stat_drds_db = ClsPdo::getInstance($stat_drds_conf);
}



?>
