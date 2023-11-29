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

$db = ClsPdo::getInstance($db_conf);

$ids = $db->getCol("select id from full_monitor_fail_task_info where type = 'TO_DB'");
var_dump($ids);
if (!empty($ids)) {
  // echo "delete from full_monitor_fail_task_crawler_detail where task_info_id in (".implode(",", $ids).")";
  // $db->query("delete from full_monitor_fail_task_crawler_detail where task_info_id in (".implode(",", $ids).")");
  // $db->query("delete from full_monitor_fail_task_crawler where task_info_id in (".implode(",", $ids).")");
  $db->query("delete from full_monitor_fail_task_to_db where task_info_id in (".implode(",", $ids).")");
  $db->query("delete from full_monitor_fail_task_table where task_info_id in (".implode(",", $ids).")");
  $db->query("delete from full_monitor_fail_task_effect where task_info_id in (".implode(",", $ids).")");
  $db->query("delete from full_monitor_fail_task_info where id in (".implode(",", $ids).")");
}

