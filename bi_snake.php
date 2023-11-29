<?php
require("includes/init.php");

$s = 't.id, t.schema_id, t.table_name, t.table_comment, t.last_table_ddl, t.current_table_ddl, t.create_time, t.update_time, t.data_length, t.table_rows, t.is_model_build, t.table_wh_type';
$arr = explode(",", $s);
// var_dump($arr);
$pre = isset($argv[1])?$argv[1].'.':'';
foreach ($arr as $value) {
	// camelize($value);
  if (strpos($value, '_')) {
    echo trim($pre.trim($value).' as '.camelize($value)).','.PHP_EOL;
  }else{
    echo $pre.trim($value).','.PHP_EOL;
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
    $arr = explode('.', $str);
    if(!empty($arr) && count($arr) == 2){
      $str = $arr[1];
    }

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
  if (strpos($str, '.')) {
      return substr($str, strpos($str, '.'));
  }
}