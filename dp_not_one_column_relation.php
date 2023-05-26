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

// $sql = "select * from bi.assets_meta_table where schema_id = 8";
// $tables = $db->getAll($sql);

$relations = $db->getAll("select * from assets_table_column_relation where parent_id = 2114");


// $relationss = $db->getAll("select * from assets_table_column_relation where parent_id in (select id from assets_table_column_relation where parent_id = 2114 and column_name = 'ask_order_success_rate')");


// $relationss = $db->getAll("select * from assets_table_column_relation where parent_id = 3948948");

$ids = $db->getCol("select id from assets_table_column_relation where parent_id = 3948948");
// $ids = $db->getCol("select id from assets_table_column_relation where parent_id in (select id from assets_table_column_relation where parent_id = 3948948)");

// $ids = $db->getCol("select id from assets_table_column_relation where parent_id in (".implode(",", $ids).")");
// $ids = $db->getCol("select id from assets_table_column_relation where parent_id in (".implode(",", $ids).")");
// $ids = $db->getCol("select id from assets_table_column_relation where parent_id in (".implode(",", $ids).")");

$sql = "select * from assets_table_column_relation where id in (".implode(",", $ids).")";
$relationss = $db->getAll($sql);
// echo $sql;die;
// $relationss = $db->getAll("select * from bi.assets_table_column_relation where id = 3948948");


$ids = getAllValByCol($relationss,'id');
if (!empty($ids)) {
    $sql = "delete from bi.assets_table_column_relation
  where parent_id in (".implode(",", $ids).")";
  echo $sql.PHP_EOL;
}

// die;
$db->query($sql);

$relation = $relations[0];
// var_dump($relationss);

// foreach ($relations as $relation) {

  foreach ($relationss as $r) {
      $sql = "replace into assets_table_column_relation (column_id, column_name, table_id, table_name,parent_id,is_result,table_wh_type)
  select column_id, column_name, table_id, table_name,{$r['id']},0,table_wh_type from assets_table_column_relation where id = {$relation['id']}";
    echo $sql.PHP_EOL;
    $db->query($sql);

      $sql = "select LAST_INSERT_ID()";
      $last_insert_id = $db->getOne($sql);

    $sql = "select result_column_id from assets_table_column_relation_result_mapping where relation_id = {$relation['id']}";
    $result_column_id = $db->getOne($sql);

    $sql = "replace into assets_table_column_relation_result_mapping (relation_id, column_id, result_column_id) 
    select {$last_insert_id},column_id,{$result_column_id} from assets_table_column_relation_result_mapping where relation_id = {$relation['id']}";
    echo $sql.PHP_EOL;
    $db->query($sql);
  }

// }

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


function getSpecValByColDb($arr,$column,$column1,$db){
    $res = [];
    if (is_array($arr) && !empty($arr)) {
        foreach ($arr as $row) {
            if (isset($row[$column])) {
                $res[$row[$column]] = [$column1=>$row[$column1],'db'=>$db];
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