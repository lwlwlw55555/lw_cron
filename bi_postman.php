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
	"ruleList":[
		{
			"id":0,
			"infoId":0,
			"fixedValue":"",
			"enumType":"",
			"enumValue":"",
			"orderIndex":0
		}
	],
	"id":0,
	"cnName":"",
	"simpleEnName":"",
	"storeLocal":"",
	"createTime":"2022-12-01 14:58:37",
	"createUser":0,
	"updateTime":"2022-12-01 14:58:37",
	"updateUser":0,
	"infoDesc":""
}
';

$json = json_decode($s,true);

$url = "/model/design/DesignEtlSeparate/save";

$res = "curl --location --request POST 'http://localhost:8086/controller{$url}' \\".PHP_EOL;

$res = parseJson($json,$res);

echo $res;

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

function getArrName($key){
	if (is_numeric($key)) {
		return "[{$key}]";
	}
	return '.'.$key;
}