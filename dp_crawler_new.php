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
$py_defines = $db->getAll("select *,count(1) c from crawler_monitor_task_define where id > 49 and crawler_type = 'PY' and result_table is not null group by path_id having c=1");
$rpa_defines = $db->getAll("select *,count(1) c from crawler_monitor_task_define where id > 49 and crawler_type = 'RPA' and result_table is not null group by path_id having c=1");
$paths = $db->getCol("select id from crawler_manage_node_info");
$alerts = $db->getCol("select id from crawler_elihu_alert_detail");
$typeArr = ['PY','RPA'];
// var_dump($py_defines);
// var_dump($rpa_defines);
// die;

for ($i=0;$i<1000;$i++) {
  $count = random_int(4444, 55112);
  $count2 = random_int(4444, 55112);
  $date_str = date('Y-m-d');
  // foreach ($tables as $table) {
    
    $type = $typeArr[random_int(0,1)];
  var_dump($type);
    $define = $rpa_defines[random_int(0,count($rpa_defines)-1)];
    if ($type == 'PY') {
      $define = $py_defines[random_int(0,count($py_defines)-1)];
    }
    // var_dump($define);
    $sql = "replace into crawler_monitor_task_execute_record (id, crawler_type, record_id, path_id, acc_info)
            values ({$count},'".$type."',".$count.",".$define['path_id'].",'lw_test_".$count."')";
    echo $sql.PHP_EOL;
    $db->query($sql);

    $sql = "replace into crawler_monitor_task_execute_result (execute_id, crawler_type,app_id, account_id, result_status, business_unit, task_id, mizar_group_id, shovel_record_id, alert_id, time_range, error_reason, start_time)
  values (".$count.",'{$type}',{$count},".$accounts[random_int(0,count($accounts)-1)].",'FAIL','测试类目',{$count2},{$count2},{$count2},".$alerts[random_int(0,count($alerts)-1)].",'{$date_str}_{$date_str}','lw_test_".$count."','".date("Y-m-d H:i:s")."')";
  echo $sql.PHP_EOL;
  $db->query($sql);
}
// $db->query($sql);
// }

