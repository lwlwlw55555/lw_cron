<?php
require("includes/init.php");

$s = '{
	"taskId":0,
	"pathId":0,
	"taskCode":0,
	"taskDev":0,
	"taskDevName":"",
	"businessDomain":"",
	"theme":"",
	"timePeriodTrans":"",
	"businessDimension":"",
	"collateLogic":"",
	"taskInfoType":"CRAWLER | ETL | DIMENSION | COLLECTION | SYNC | ACCESS | ETL_SUPPLEMENT | CRAWLER_SUPPLEMENT"
}';

// | 参数名 | 类型   | 说明                                                         |
// | :----- | :----- | ------------------------------------------------------------ |
// | key    | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | id     | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | name   | String | 必有，列表展示的值                                           |

$arr = json_decode($s,true);
echo "| 参数名           | 必选 | 类型   | 说明                                                         |  \n";
echo "| :--------------- | :--- | :----- | ------------------------------------------------------------ |   \n";
foreach ($arr as $key => $value) {
	echo "| {$key} | 否   | ".(is_numeric($value)?'Integer':(is_bool($value)?'Boolean':'String'))." | {$value} |   \n";
}