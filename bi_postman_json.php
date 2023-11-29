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
	"platformCodes":[
		""
	],
	"accountIds":[
		0
	],
	"shopIds":[
		0
	],
	"useType":[
		0
	]
}
';

$json = json_decode($s,true);

$url = "/AccountOpenApi/getLoginAcc";

$res = "curl --location --request POST 'http://localhost:8001/controller{$url}' \\".PHP_EOL;

// $res = parseJson($json,$res);

echo $res;

echo PHP_EOL;
echo "--header 'Content-Type: application/json'";
echo PHP_EOL;
echo "--data '".$s."'";



echo PHP_EOL;

// function parseJson($arr,$res){
// 	foreach ($arr as $key => $value) {
// 		if (is_array($value)) {
// 			$res = parseArr($value,$res,$key);
// 		}else{
// 			$res .= "--form '{$key}={$value}' \\".PHP_EOL;
// 		}
// 	}
// 	return $res;

// }

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