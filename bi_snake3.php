<?php
require("includes/init.php");


// <resultMap id="BaseResultMap" type="com.leqee.bi.entity.upload.config.StrategicUploadSource" >
//   <id column="id" property="id" jdbcType="INTEGER" />
//   <result column="name" property="name" jdbcType="VARCHAR" />
//   <result column="host" property="host" jdbcType="VARCHAR" />
//   <result column="user" property="user" jdbcType="VARCHAR" />
//   <result column="port" property="port" jdbcType="INTEGER" />
//   <result column="password" property="password" jdbcType="VARCHAR" />
//   <result column="status" property="status" jdbcType="BOOLEAN" />
//   <result column="create_time" property="createTime" jdbcType="TIMESTAMP" />
//   <result column="last_update_time" property="lastUpdateTime" jdbcType="TIMESTAMP" />
//   <association property="schemaNameList" columnPrefix="schema_" resultMap="schemaMap"/>
// </resultMap>



    // <resultMap id="InfoMap" type="com.leqee.bi.vo.base.RelatedSelectVo" >
    //     <result column="key" property="key" />
    //     <result column="name" property="name" />
    //     <result column="id" property="id" />
    //     <association property="children" columnPrefix="s" resultMap="DetailMap"/>
    // </resultMap>

    // <resultMap id="DetailMap" type="com.leqee.bi.vo.base.RelatedSelectVo" >
    //     <result column="children" property="children" />
    //     <result column="key" property="key" />
    //     <result column="name" property="name" />
    //     <result column="id" property="id" />
    // </resultMap>


$s = '

 {
            "id":0,
            "taskInfoId":0,
            "type":"",
            "dataId":0,
            "collectionType":"",
            "createTime":"2023-05-06 13:55:49",
            "updateTime":"2023-05-06 13:55:49"
        }
';

$arr = json_decode($s,true);
// var_dump($arr);
foreach ($arr as $key => $value) {
	// camelize($value);
	echo uncamelize($key).','.PHP_EOL;
}

echo PHP_EOL.PHP_EOL.PHP_EOL;
echo '<resultMap id="InfoMap" type="xxx" >'.PHP_EOL;
foreach ($arr as $key => $value) {
	// camelize($value);
	echo '<result column="'.uncamelize($key).'" property="'.$key.'" />'.PHP_EOL
	;
}
echo '</resultMap>'.PHP_EOL;

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