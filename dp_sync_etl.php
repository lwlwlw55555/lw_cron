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

// $relations = $db->getAll("select * from assets_table_column_relation");

// $relation_mapping = refreshArraytoMapping($relations,'id');

$index = 1228;
$page = isset($argv[1])?$argv[1]:22;
$limit = 5000;
$is_start = true;
while($is_start || count($relations) == $limit){
    $is_start = false;
    $sql = "select * from assets_table_column_relation_back limit ".$page*$limit.",".$limit;
    echo $sql.PHP_EOL;
    $relations = $db->getAll($sql);
    foreach ($relations as $relation) {
    if ($relation['parent_id'] != -1) {
          $index++;
          $parent_relation = $db->getRow("select * from assets_table_column_relation_back where id = {$relation['parent_id']}");
          $sql = "replace into dp_out.sync_etl_column_relation (t_table, t_column, s_table, s_column, file_name)
    values ('{$parent_relation['table_name']}','{$parent_relation['column_name']}','{$relation['table_name']}','{$relation['column_name']}','{$index}')";
          // echo $sql.PHP_EOL;
          $db->query($sql);
          // die;
      }
    }
    $page--;
    // echo count($relations);die;
}
  


function refreshArraytoMapping($arr,$column){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[$row[$column]] = $row;
                }
            }
            return $res;
        }
        return [];
    }



function getSpecValByCol($arr,$column,$column1){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[$row[$column]] = $row[$column1];
                }
            }
            return $res;
        }
        return [];
    }


function getAllValByCol($arr,$column){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[] = $row[$column];
                }
            }
            return $res;
        }
        return [];
}