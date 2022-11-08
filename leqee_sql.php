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
//     // "pass" => "123456",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     // "name" => "bi"
//     "name" => "bi"
// );

$db = ClsPdo::getInstance($db_conf);

$sql = "select task_code,task_type,count(distinct task_type) c from data_wb_task group by task_code having c > 1";
$datas = $db->getAll($sql);
foreach ($datas as $data) {
    // $sql = "update data_wb_task set task_code = null where task_code = {$data['task_code']} and task_type <> '{$data['task_type']}'";
    $sql = "delete from data_wb_task where task_code = {$data['task_code']}";
    echo $sql.PHP_EOL;
    // $db->query($sql);
}