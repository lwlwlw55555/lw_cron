<?php
require("includes/init.php");

$bi_db_conf = array(
    // "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    "host" => "47.98.144.22:20001",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi"
);
$bi_db = ClsPdo::getInstance($bi_db_conf);
global $bi_db;

$table_name = 'cal_crawler_task_exec_time';
$java_obj = '';

$sql = "select COLUMN_NAME from information_schema.COLUMNS
where TABLE_NAME = '{$table_name}'
and COLUMN_KEY != 'PRI';";

$columns = $bi_db->getCol($sql);
// var_dump($columns);
foreach ($columns as $column) {
	if ($column == 'create_time') {
		continue;
	}
	if ($column == 'update_time') {
		continue;
	}
    if (empty($java_obj)) {
        echo 'row.put("'.$column.'", '.camelCase($column,false).');'.PHP_EOL;
    }else{
    	echo 'row.put("'.$column.'",'.$java_obj.'.get'.camelCase($column,true).'());'.PHP_EOL;
    }
}
// echo 'rows.add(row);'.PHP_EOL;


function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}


function camelCase($str, $ucfirst = true)
{
    $str = ucwords(str_replace('_', ' ', $str));
    $str = str_replace(' ', '', lcfirst($str));
    return $ucfirst ? ucfirst($str) : $str;
}

// function camelCase($data, $ucfirst = false)
// {
//     $result = [];
//     foreach ($data as $key => $value) {
//         $key1 = convertUnderline($key, $ucfirst);
//         $value1 = camelCase($value);
//         $result[$key1] = $value1;
//     }
//     return $result;
// }
