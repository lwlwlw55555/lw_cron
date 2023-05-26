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

$db = ClsPdo::getInstance($db_conf);

$sql = "SELECT id, table_name, table_wh_type
FROM assets_meta_table
WHERE ((table_name not like 'lw_test%'))";
$tables = $db->getAll($sql);

foreach ($tables as $table) {
    var_dump($table);
    if ($db->getOne("select 1 from assets_meta_column where table_id = {$table['id']}")) {
        continue;
    }

    $sql = "select distinct t_column from dp_out.sync_etl_column_relation where t_table = '{$table['table_name']}' ";
    echo $sql.PHP_EOL;
    $columns = $db->getCol($sql);
    if (empty($columns)) {
      $sql = "select distinct s_column from dp_out.sync_etl_column_relation where s_table = '{$table['table_name']}' ";
      echo $sql.PHP_EOL;
      $columns = $db->getCol($sql);
    }

    var_dump($columns);
    if (empty($columns)) {
      continue;
    }
    foreach ($columns as $column) {
          $sql = "insert into assets_meta_column (table_id, column_name, data_type, column_comment, default_const, other_info, sync, deleted, mod_info, is_unique)
          select {$table['id']} ,'{$column}', data_type, '{$column}', default_const, other_info, sync, deleted, mod_info, is_unique
          from assets_meta_column where id = 7141";
          echo $sql.PHP_EOL;
          $db->query($sql);
        // die;
    }
}

