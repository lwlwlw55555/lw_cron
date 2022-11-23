<?php
require("includes/init.php");

$s = '{
	"dataType":"",
	"dataLength":0,
	"dataPrecisionInt":0,
	"dataPrecisionDec":0,
	"hasNull":false,
	"defaultValue":"",
	"valueRange":"",
	"enumTableIdList":[
		0
	],
	"attrList":[
		{
			"id":0,
			"dataUnitId":0,
			"attrKey":"",
			"attrValue":""
		}
	],
	"id":0,
	"cnName":"",
	"enName":"",
	"simpleEnName":"",
	"createTime":"2022-11-22 11:03:01",
	"createUser":0,
	"updateTime":"2022-11-22 11:03:01",
	"updateUser":0,
	"nickName":"",
	"busDefine":"",
	"isOnline":false
}';

$arr = json_decode($s,true);
// var_dump($arr);
foreach ($arr as $key => $value) {
	// camelize($value);
	echo uncamelize($key).' as '.$key.','.PHP_EOL;
}

/**
* 下划线转驼峰
* 思路:
* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
*/
function camelize($str)
{
    $array = explode('_', $str);
    $result = $array[0];
    $len=count($array);
    if($len>1)
    {
        for($i=1;$i<$len;$i++)
        {
            $result.= ucfirst($array[$i]);
        }
    }
    return trim($result);
}
 
/**
* 驼峰命名转下划线命名
* 思路:
* 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
*/
function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}