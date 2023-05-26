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

// $sql = "select * from bi.assets_meta_table where schema_id = 8";
// $tables = $db->getAll($sql);

$relations = $db->getAll("select * from assets_table_column_relation where is_result = 1 ");
var_dump($relations);
foreach ($relations as $relation) {
     $count++;
    $sql = "insert into bi.assets_collection_column_relation (collection_column_id, collection_id, collection_column_name, collection_name, table_id, column_id, table_name, column_name)
    select column_id,collection_id,column_name,(select collection_name from dp_out.sync_eb_col_detail where collection_id = c.collection_id),{$relation['table_id']},{$relation['column_id']},'{$relation['table_name']}','{$relation['column_name']}'
    from dp_out.sync_eb_col_column_info c where column_id in (101155070220596,101155069833652)";
    echo $sql.PHP_EOL;
    $db->query($sql);

}


// update bi.assets_collection_column_relation set table_id = 9178,column_id = 314205 where collection_column_id = 101155069885364;
