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

$sql = "select * from bi.assets_meta_table where schema_id = 8";
$tables = $db->getAll($sql);

$relations = $db->getAll("select * from assets_table_column_relation where is_result = 0 and parent_id = 657");

$count = random_int(0, 99999999);
foreach ($tables as $table) {
  // try{
    $relation = $relations[random_int(0, count($relations))];
  // $relation = $relations[0];
     $count++;
     $sql = "insert into bi.assets_meta_table (schema_id, table_name, last_table_ddl, current_table_ddl, create_time, update_time, table_wh_type, column_example, sync_date) values (18,'lw_test_".$pre."_".$count."','".addslashes($table['last_table_ddl'])."','".addslashes($table['current_table_ddl'])."','{$table['create_time']}','{$table['update_time']}','".strtoupper($pre)."','".addslashes($table['column_example']).")','{$table['sync_date']}')";
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


// $sql = "select * from dp_out.sync_eb_col_detail where collection_type = 'NATIVE' and id <> 3450";
// $tables = $db->getAll($sql);

// foreach ($tables as $table) {
//   try{
//       $sql = "INSERT INTO dp_out.sync_eb_col_detail (collection_id,collection_type, collection_name, collection_desc, user_name, real_name, owner_user_name, owner_real_name, col_create_time, col_update_time) SELECT 
//     collection_id, 'DERIVE',collection_name, collection_desc, user_name, real_name, owner_user_name, owner_real_name, col_create_time, col_update_time from sync_eb_col_detail where id =  {$table['id']}";


//        echo $sql.PHP_EOL;
//        $db->query($sql);


//        $sql = "INSERT INTO dp_out.sync_eb_col_column_info (collection_type, collection_id, column_id, column_name, show_name, source_table, data_source, column_definition, extra_note, hidden, deleted) VALUES ( 'DERIVE', {$table['collection_id']},".random_int(1000000,2000000).", 'lw_test_drive', 'lw_test_drive', '', 'lw_test_drive', null, null, 0, 0), ('DERIVE',{$table['collection_id']} ,".random_int(1000000,2000000).", 37371577890175, 'lw_test_drive', '', 'lw_test_drive', null, null, 0, 0)
//     ";
//     echo $sql;
//     $db->query($sql);
//  }catch(Exception $e){
//         // if (strpos($e->getMessage(), 'already exists') == false) {
//             // echo $e->getMessage(); die;
//         // }
//         // echo $e->getMessage(); echo PHP_EOL;
//     }

// // die;
//     // $db->query($sql);
//    // die;
//         // die;
//     // die;
// }

// $enum_type = ['column_change','type_change','structure_change'];

// foreach ($tables as $table) {
//     $index = random_int(0, 2);
//     $sql = "replace into assets_meta_table_modify (table_id, mod_date, mod_info, current_ddl)
//     values ({$table['id']},'".(date("Y-m-d"))."','".$enum_type[$index]."','".addslashes($table['current_table_ddl'])."')";
//     echo $sql.PHP_EOL;

//     $db->query($sql);
    
//     $sql = "replace into assets_meta_column_modify (mod_date, table_id, column_name, mod_info) 
//     values ('".(date("Y-m-d"))."',{$table['id']},'id','字段类型由[bigint(20)]修改为[int(11)]'),".
//         "('".(date("Y-m-d"))."',{$table['id']},'update_time','字段注释由[修改时间]修改为[更新时间]')";
//         echo $sql.PHP_EOL;
//         $db->query($sql);
//         // die;
//     // die;
// }

// $sql = "select task_code,task_type,count(distinct task_type) c from data_wb_task group by task_code having c > 1";
// $datas = $db->getAll($sql);
// foreach ($datas as $data) {
//     // $sql = "update data_wb_task set task_code = null where task_code = {$data['task_code']} and task_type <> '{$data['task_type']}'";
//     $sql = "delete from data_wb_task where task_code = {$data['task_code']}";
//     echo $sql.PHP_EOL;
//     // $db->query($sql);
// }