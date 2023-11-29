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


$pre = "oss-report";

$s = '
{
	"host":"",
	"accessKey":"",
	"secretKey":"",
	"bucket":""
}
     
';


$arr = json_decode($s,true);
foreach ($arr as $key => $value) {
	if ($key == 'infoId') {
		continue;
	}
	echo "this.{$key} = Keel.config(\"{$pre}.{$key}\");".PHP_EOL;
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
	if ('owner' == $column) {
		return '负责人';
	}
	if ('name' == $column) {
		return '名称';
	}
	if ('type' == $column) {
		return '类别';
	}
	if ('id' == $column) {
		return '唯一标识';
	}
    if (endWith($column,'Str')) {
        return '展示用';
    }
    if ('fullName' == $column) {
        return '全称(展示用)';
    }
	$column = uncamelize($column);
	$sql = "select COLUMN_COMMENT from information_schema.COLUMNS where COLUMN_NAME = '{$column}' and COLUMN_COMMENT <> ''";
	global $bi_db;
	$commnt = $bi_db->getOne($sql);
	return empty($commnt)?$value:$commnt;
}

function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}

function endWith($haystack, $needle) {
    $length = strlen($needle);
    if($length == 0){
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}