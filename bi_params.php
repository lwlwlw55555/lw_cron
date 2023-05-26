<?php
require("includes/init.php");

$bi_db_conf = array(
    "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    // "host" => "47.98.144.22:20001",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi"
);
$bi_db = ClsPdo::getInstance($bi_db_conf);
global $bi_db;

$s = '
{
	"id":0,
	"taskId":0,
	"shovelRecordId":0,
	"timeRange":"",
	"taskStatus":"",
	"startTime":"2023-05-10 10:56:17",
	"finishTime":"2023-05-10 10:56:17",
	"targetMachine":"",
	"targetDatabaseType":"",
	"targetDatabaseCode":"",
	"targetSchema":"",
	"targetTable":"",
	"mizarGroupId":0,
	"createTime":"2023-05-10 10:56:17",
	"updateTime":"2023-05-10 10:56:17"
}
';

// | 参数名 | 类型   | 说明                                                         |
// | :----- | :----- | ------------------------------------------------------------ |
// | key    | String | key和id必有且只有一个，用于后续接口传参（有id传id，有key传key) |
// | id     | String | key和id必有且只有一个，用于后续接口传参（有id传id，有key传key) |
// | name   | String | 必有，列表展示的值                                           |

$arr = json_decode($s,true);
echo "| 参数名           | 必选 | 类型   | 说明                                                         |  \n";
echo "| :--------------- | :--- | :----- | ------------------------------------------------------------ |   \n";
foreach ($arr as $key => $value) {
	if ($key == 'pageStart') {
		continue;
	}
	echo "| {$key} | ".getMustByColumn($key,'否')."   | ".(is_numeric($value)?'Long':(is_bool($value)?'Boolean':(is_array($value)?'Array':'String')))." | ".getCommentByColumn($key,$value)." |   \n";
}


function getCommentByColumn($column,$value){
	if ('pageSize' == $column) {
		return '每页条数';
	}
	if ('pageNum' == $column) {
		return '当前页数';
	}
	if ('searchInfo' == $column) {
		return '查询内容';
	}
	if ('createTime' == $column) {
		return '创建时间';
	}
	if ('name' == $column) {
		return '名称';
	}
	if ('type' == $column) {
		return '类型';
	}
	if ('desc' == $column) {
		return '描述';
	}
	if ('owner' == $column) {
		return '负责人';
	}
	if ('action' == $column) {
		return '方式';
	}
	if ('value' == $column) {
		return '内容';
	}
	if ('taskId' == $column) {
		return '任务id';
	}
	$column = uncamelize($column);
	$sql = "select COLUMN_COMMENT from information_schema.COLUMNS where COLUMN_NAME = '{$column}' and COLUMN_COMMENT <> ''";
	global $bi_db;
	$commnt = $bi_db->getOne($sql);
	return empty($commnt)?$value:$commnt;
}

function getMustByColumn($column,$value){
	if ('pageSize' == $column) {
		return '是';
	}
	if ('pageNum' == $column) {
		return '是';
	}
	$column = uncamelize($column);
	$sql = "select IS_NULLABLE from information_schema.COLUMNS where COLUMN_NAME = '{$column}'";
	global $bi_db;
	$is_null = $bi_db->getOne($sql);
	if ($is_null == 'NO') {
		if (is_null($bi_db->getOne("select COLUMN_DEFAULT from information_schema.COLUMNS where COLUMN_NAME = '{$column}'"))) {
			return '是';
		}

	}
	return $value;
}

function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}