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

// $s = '
//             {
//                 "columnComment": "1 acsacasca",
//                 "isUnique": 1,
//                 "dataType": "文本",
//                 "dataLength": 1123
//             }
        
//     ';

// $s = '{
// 	"businessBaseIdStr":"",
// 	"createUserName":"",
// 	"businessBaseNameStr":"",
// 	"enumTableDetailList":[
// 		{
// 			"id":0,
// 			"enumTableId":0,
// 			"enumText":"",
// 			"enumValue":"",
// 			"enumDesc":"",
// 			"isEff":false
// 		}
// 	],
// 	"mappingNum":0,
// 	"dataType":"",
// 	"dataLength":"",
// 	"dataPrecisionInt":"",
// 	"dataPrecisionDec":"",
// 	"hasNull":false,
// 	"defaultValue":"",
// 	"valueRange":"",
// 	"enumTableIdList":[
// 		0
// 	],
// 	"businessBaseIdList":[
// 		0
// 	],
// 	"attrList":[
// 		{
// 			"id":0,
// 			"dataUnitId":0,
// 			"attrKey":"",
// 			"attrValue":""

// 		}
// 	],
// 	"id":0,
// 	"cnName":"",
// 	"enName":"",
// 	"simpleEnName":"",
// 	"updateTime":"2022-11-23 16:19:25",
// 	"updateUser":0,
// 	"nickName":"",
// 	"busDefine":"",
// 	"isOnline":false,
// 	"onlineTime":"2022-11-23 16:19:25",
// 	"onlineUser":0,
// 	"isDelete":false
// }';


// | 参数名 | 类型   | 说明   |
// | :----- | :----- | ------ |
// | key    | String | 传参用 |
// | name   | String | 展示用 |



$s = '
		{
            "key": "adb3",
            "name": "ABD3"
        }
';
// | 参数名 | 类型   | 说明                                                         |
// | :----- | :----- | ------------------------------------------------------------ |
// | key    | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | id     | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | name   | String | 必有，列表展示的值                                           |

$arr = json_decode($s,true);
echo "| 参数名 | 类型   | 说明                                                         |   \n";
echo "| :----- | :----- | ------------------------------------------------------------ |   \n";
foreach ($arr as $key => $value) {
	if ($key == 'infoId') {
		continue;
	}
	echo "| {$key} | ".(is_numeric($value)?'Integer':(is_bool($value)?'Boolean':(is_array($value)?'Array':'String')))."   |  ".getCommentByColumn($key,$value)."                                                |   \n";
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

function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}