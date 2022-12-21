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

// $s = '{
// 	"taskId":0,
// 	"pathId":0,
// 	"taskCode":0,
// 	"taskDev":0,
// 	"taskDevName":"",
// 	"businessDomain":"",
// 	"theme":"",
// 	"timePeriodTrans":"",
// 	"businessDimension":"",
// 	"collateLogic":"",
// 	"taskInfoType":"CRAWLER | ETL | DIMENSION | COLLECTION | SYNC | ACCESS | ETL_SUPPLEMENT | CRAWLER_SUPPLEMENT"
// }';

$s = '
{
	"wordRootId":0,
	"wordId":0,
	"cnName":"",
	"order":0
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
	$column = uncamelize($column);
	$sql = "select COLUMN_COMMENT from information_schema.COLUMNS where COLUMN_NAME = '{$column}' and COLUMN_COMMENT <> ''";
	global $bi_db;
	$commnt = $bi_db->getOne($sql);
	return empty($commnt)?$value:$commnt;
}

function getMustByColumn($column,$value){
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