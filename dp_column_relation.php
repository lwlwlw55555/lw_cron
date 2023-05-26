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

$sql = "select * from bi.assets_meta_table where schema_id = 8";
$tables = $db->getAll($sql);

$relations = $db->getAll("select * from assets_table_column_relation where table_name = 'lw_test_ads_45520'");

// $relations = $db->getAll("select * from assets_table_column_relation where parent_id in (
// select id from assets_table_column_relation where 
// parent_id in (
//  select id from assets_table_column_relation where table_name = 'lw_test_ods_6128472852')
// )");

$count = random_int(42113, 9999999999);
foreach ($tables as $table) {
  // try{
    // $relation = $relations[random_int(0, count($relations))];
  $relation = $relations[0];
     $count++;
     $sql = "insert into bi.assets_meta_table (schema_id, table_name, last_table_ddl, current_table_ddl, create_time, update_time, table_wh_type, column_example, sync_date) values (18,'lw_test_businsess_".$count."','".addslashes($table['last_table_ddl'])."','".addslashes($table['current_table_ddl'])."','{$table['create_time']}',now(),'BUSINESS','".addslashes($table['column_example']).")','{$table['sync_date']}')";
    echo $sql.PHP_EOL;
    $db->query($sql);

    // $sql = "select * from assets_meta_column where table_id = {$table['id']}";
    // $columns = $db->getAll($sql);
    $sql = "select LAST_INSERT_ID()";
    $last_insert_id = $db->getOne($sql);

    $sql = "insert into assets_meta_column (table_id, column_name, data_type, column_comment, default_const, other_info, sync, deleted, mod_info, is_unique)
select {$last_insert_id} ,column_name, data_type, column_comment, default_const, other_info, sync, deleted, mod_info, is_unique
from assets_meta_column where table_id = {$table['id']}";
echo $sql.PHP_EOL;
$db->query(addslashes($sql));

    $sql = "insert into assets_table_column_relation (column_id, column_name, table_id, table_name,parent_id,is_result,table_wh_type)
select c.id,c.column_name,t.id,t.table_name,{$relation['id']},0,table_wh_type from assets_meta_column c
inner join assets_meta_table t on c.table_id = t.id
where t.id = {$last_insert_id}";
  echo $sql.PHP_EOL;
  $db->query($sql);

  $sql = "select result_column_id from assets_table_column_relation_result_mapping where column_id = {$relation['column_id']}";
  $result_column_id = $db->getOne($sql);

  $sql = "insert into assets_table_column_relation_result_mapping (relation_id, column_id, result_column_id) 
  select id,column_id,{$result_column_id} from assets_table_column_relation where table_id = {$last_insert_id}";
  echo $sql.PHP_EOL;
  $db->query($sql);
  // die;

    // die;
 // }catch(Exception $e){

 //    }
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