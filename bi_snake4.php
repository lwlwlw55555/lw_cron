<?php
require("includes/init.php");

$s = '  t.id,
    t.task_info_id,
    t.table_id,
    t.is_all_column,
    e.id,
    e.task_info_id,
    e.type,
    e.data_id,
    e.collection_type,
    e.create_time,
    e.update_time';
$arr = explode(",", $s);
// var_dump($arr);
$pre = isset($argv[1])?$argv[1].'.':'';
foreach ($arr as $value) {
  if (strpos(trim($value), 'sai') === 0){

    echo trim($pre.trim($value).',').PHP_EOL;
  }else{
  	// camelize($value);
  	echo trim($pre.trim($value).' as '.removePoint($value).',').PHP_EOL;
  }
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

function removePoint($str){
  return trim(str_replace('`','',str_replace('.', '_',$str)));
}

function checkPre($str,$pre){
  // echo strpos($str, $pre) === 0;die;
  if (strpos($str, $pre) === 0) {
    return true;
  }
  return false;
}

function startWith($str, $needle) { return strpos($str, $needle) === 0;}