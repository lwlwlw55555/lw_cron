<?php
require("includes/init.php");

// $s = '
//             {
//                 "columnComment": "1 acsacasca",
//                 "isUnique": 1,
//                 "dataType": "文本",
//                 "dataLength": 1123
//             }
        
//     ';

$s = ' {
            "packageId": "1",
            "packageName": "数据银行1(Databank-Shovel)",
            "platformGroupCode": "DATABANK",
            "platformCode": "DATABANK"
        }';

// | 参数名 | 类型   | 说明                                                         |
// | :----- | :----- | ------------------------------------------------------------ |
// | key    | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | id     | String | key和id必有且只有一个，用户后续接口传参（有id传id，有key传key) |
// | name   | String | 必有，列表展示的值                                           |

$arr = json_decode($s,true);
echo "| 参数名 | 类型   | 说明                                                         |   \n";
echo "| :----- | :----- | ------------------------------------------------------------ |   \n";
foreach ($arr as $key => $value) {
	echo "| {$key} | ".(is_numeric($value)?'Integer':(is_bool($value)?'Boolean':'String'))."   |  {$value}                                                |   \n";
}