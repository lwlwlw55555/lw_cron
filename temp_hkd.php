<?php
require("includes/init.php");

$bi_db_conf = array(
    "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi"
);
$bi_db = ClsPdo::getInstance($bi_db_conf);
global $bi_db;

// | 参数名 | 类型   | 说明   |
// | :----- | :----- | ------ |
// | key    | String | 传参用 |
// | name   | String | 展示用 |


for($i=0;$i<5000;$i++){

$task_id = 50000+$i;
$sql = "replace INTO bi.full_monitor_fail_task_crawler_detail (task_info_id, task_id, is_complete, complete_time,
                                                      complete_reason_type, complete_remark, complete_user, time_range,
                                                      error_reason, shop_id, shovel_record_id, account_id, create_time,
                                                      update_time)
VALUES ( 7751,{$task_id} , 0, null, null, null, null, null, null, null, null, null, '2023-08-09 10:18:58',
        '2023-08-09 10:18:58')";

echo $sql.PHP_EOL;
$bi_db->query($sql);
}
