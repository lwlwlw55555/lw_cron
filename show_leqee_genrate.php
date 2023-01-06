<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
include 'request/OrderNumberListGetRequest.php';
include 'PddClient.php';

$lw_conf = array(
    "host" => "127.0.0.1:3306",
    // "host" => "121.40.113.153:3306",
    "user" => "root",
    // "pass" => "123456",
    "charset" => "utf8",
    "pconnect" => "1",
    // "name" => "sys_info"
    "name" => "leqee_sys_info"
);
$lw_db = ClsPdo::getInstance($lw_conf);

$target_db_conf = array(
    "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi"
);
$target_db = ClsPdo::getInstance($target_db_conf);


$sql = "select distinct table_name
from information_schema.COLUMNS
where  TABLE_NAME like '%data_wb_task_model_field%' and TABLE_SCHEMA = 'bi'";

$tables = $target_db->getCol($sql);
// $tables = $target_db->getAll("show tables");
// var_export($tables);
echo PHP_EOL;
foreach ($tables as $table) {
    echo '<table
                tableName="'.$table.'" domainObjectName="'.camelize($table).'"
                enableCountByExample="false" enableUpdateByExample="false"
                enableDeleteByExample="false" enableSelectByExample="false"
                selectByExampleQueryId="false">
        </table>'.PHP_EOL;

    // try{
    //     $sql = "show create table `{$table}`";
    //     $t = $target_db->getAll($sql);
    //     if (isset($t[0]['Create Table'])) {
    //         // var_dump($t[0]['Create Table']);
    //         $sql = $t[0]['Create Table'];
    //         echo $sql.';'.PHP_EOL;
    //         // $lw_db->query($sql);
    //     }
    // }catch(Exception $e){
    //     if (strpos($e->getMessage(), 'already exists') == false) {
    //         // echo $e->getMessage(); die;
    //     }
    //     // echo $e->getMessage(); echo PHP_EOL;
    // }
    // $sql = "SELECT auto_increment FROM information_schema.tables where table_schema='sys_info' and table_name='{$v}'";
    // var_dump($sql);
    // $num = $lw_db->getOne($sql);
    // var_dump($num);
    // $sql = "alter table {$v} auto_increment =".($num+1000000);
    // var_dump($sql);
    // $num = $lw_db->query($sql);
    // die;
    // var_dump($t);die;
}

/**
* 下划线转驼峰
* 思路:
* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
*/
function camelize1($uncamelized_words,$separator='_')
{
    $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
    $s = ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
    return $s;
    // echo str_replace(" ", "", ucwords($uncamelized_words));    
}

/**
* 下划线转驼峰 首字母也要大写
*/
function camelize($uncamelized_words,$separator='_')
{
    $uncamelized_words = str_replace($separator, " ", strtolower($uncamelized_words));
    $s = str_replace(" ", "", ucwords($uncamelized_words));
    return $s;
    // echo str_replace(" ", "", ucwords($uncamelized_words));    
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