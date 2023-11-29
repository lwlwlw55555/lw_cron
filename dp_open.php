<?php
require("includes/init.php");

echo md5('Testuois5221424@1111@dbcdecfansi2597');

die;

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

// | 参数名 | 类型   | 说明   |
// | :----- | :----- | ------ |
// | key    | String | 传参用 |
// | name   | String | 展示用 |



$s = '
{
                        "id": "1149792",
                        "taskId": "79",
                        "shovelRecordId": "79",
                        "timeRange": "2023-07-10_2023-07-10,2023-07-11",
                        "taskStatus": "DONE",
                        "acceptTime": "2023-07-11 15:13:32",
                        "startTime": "2023-07-11 15:15:03",
                        "finishTime": "2023-07-11 15:15:03",
                        "targetSchema": "mizar",
                        "targetTable": "ct_merits_detail_wang_record",
                        "feedback": "",
                        "mizarVersion": "2.0",
                        "createTime": "2023-07-11 15:15:03",
                        "recordId": null,
                        "shovelProject": null,
                        "shovelClass": null,
                        "aspect": null,
                        "partKey": null,
                        "partName": null,
                        "dataStorageType": null,
                        "storage": null,
                        "location": null,
                        "targetDatabaseType": null,
                        "targetDatabaseCode": null,
                        "tpCode": null,
                        "mizarGroupId": null,
                        "epitaph": null,
                        "fcRequestId": null,
                        "targetMachine": null,
                        "endTime": null,
                        "isCrawlerShovelRecord": null,
                        "tableWhType": null
                    }
                
';
// | 参数名 | 类型   | 说明                                                         |
// | :----- | :----- | ------------------------------------------------------------ |
// | key    | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | id     | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | name   | String | 必有，列表展示的值                                           |

$arr = json_decode($s,true);
foreach ($arr as $key => $value) {

	echo "public ".(is_numeric($value)?'Integer':(is_bool($value)?'Boolean':(is_array($value)?'Array':'String')))."   |  ".getCommentByColumn($key,$value)."                                                |   \n";
}


function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}