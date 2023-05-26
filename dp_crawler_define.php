<?php

require("includes/init.php");

$db_conf = array(
    "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    // "host" => "2.tcp.cpolar.cn:13001",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi"
);

// $db1_conf = array(
//      "host" => "127.0.0.1:3306",
//     // "host" => "121.40.113.153:3306",
//     "user" => "root",
//     "pass" => "aBc@123456",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     // "name" => "bi"
//     "name" => "bi"
// );

$pre = isset($argv[1])?$argv[1]:'dwd';

$db = ClsPdo::getInstance($db_conf);

// $sql = "select * from bi.assets_meta_table where schema_id = 8";
// $tables = $db->getAll($sql);

$tables = $db->getCol("select table_name from assets_meta_table where table_wh_type = 'CRAWLER'");
$accounts = $db->getCol("select id from crawler_manage_account");
$defines = $db->getCol("select * from crawler_monitor_task_define");
$paths = $db->getCol("select id from crawler_manage_node_info");
$alerts = $db->getCol("select id from crawler_elihu_alert_detail");
$typeArr = ['PY','RPA'];

$count = random_int(4444, 55112);
$count2 = random_int(4444, 55112);
$date_str = date('Y-m-d');
foreach ($tables as $table) {
  // $type = $typeArr[random_int(0,1)];
  $type = 'PY';
  if ($type == 'PY') {
    $sql = "insert into crawler_monitor_task_define (crawler_type, class_name, app_id, path_id, exec_period, exec_time, is_eff, result_table, is_del)
  select crawler_type, class_name, app_id, ".$paths[random_int(0,count($paths)-1)].", exec_period, exec_time, 1, '{$table}',0  from crawler_monitor_task_define where id = 47
 ";
  }else{
$sql = "insert into crawler_monitor_task_define (crawler_type, class_name, app_id, path_id, exec_period, exec_time, is_eff, result_table,is_del)
  select crawler_type, class_name, app_id, ".$paths[random_int(0,count($paths)-1)].", exec_period, exec_time, 1, '{$table}',0  from crawler_monitor_task_define where id = 45
 ";
  }

 echo $sql.PHP_EOL;
 $db->query($sql);
}

