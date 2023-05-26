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

$s = '
{
	"collectionMapIdList":[
		0
	],
	"id":0,
	"name":"",
	"dingTalkId":0,
	"taskId":0,
	"isEnable":false,
	"beginDate":"2023-05-06 15:58:16",
	"endDate":"2023-05-06 15:58:16",
	"createTime":"2023-05-06 15:58:16",
	"updateTime":"2023-05-06 15:58:16",
	"createUser":0,
	"updateUser":0,
	"desc":""
}
';

$json = json_decode($s,true);

$url = "/assets/ColumnRelation/byKey";

$res = "curl --location --request POST 'http://localhost:8086/controller{$url}' \\".PHP_EOL;

$res = parseJson($json,$res);

echo $res;

echo PHP_EOL.PHP_EOL.PHP_EOL;

$res2 = parseJson2($json,$res2);

echo $res2;

function parseJson($arr,$res){
	foreach ($arr as $key => $value) {
		if (is_array($value)) {
			$res = parseArr($value,$res,$key);
		}else{
			$res .= "--form '{$key}={$value}' \\".PHP_EOL;
		}
	}
	return $res;

}

function parseJson2($arr,$res){
	foreach ($arr as $key => $value) {
		if (is_array($value)) {
			$res = parseArr2($value,$res,$key);
		}else{
			$res .= "{$key}:{$value}".PHP_EOL;
		}
	}
	return $res;

}

function parseArr($arr,$res,$prefix){
	foreach ($arr as $key => $value) {
		if (is_array($value)) {
			$res = parseArr($value,$res,$prefix.getArrName($key));
		}else{
			$res .= "--form '{$prefix}".getArrName($key)."={$value}' \\".PHP_EOL;
		}
	}
	return $res;
}

function parseArr2($arr,$res,$prefix){
	foreach ($arr as $key => $value) {
		if (is_array($value)) {
			$res = parseArr($value,$res,$prefix.getArrName($key));
		}else{
			$res .= "{$prefix}".getArrName($key).":{$value}".PHP_EOL;
		}
	}
	return $res;
}

function getArrName($key){
	if (is_numeric($key)) {
		return "[{$key}]";
	}
	return '.'.$key;
}