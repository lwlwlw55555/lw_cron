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
    "pass" => "aBc@123456",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi_api"
    // "name" => "leqee_sys_info"
);
$lw_db = ClsPdo::getInstance($lw_conf);

// $omssync_db_conf = array(
//     "host" => "rm-bp1igiu97gc79oc5yo.mysql.rds.aliyuncs.com:3306",
//     "user" => "oms",
//     "pass" => "cNlFy%MtcoQR",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     "name" => "sys_info"
// );
$omssync_db_conf = array(
    "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi_api"
);

$omssync_db = ClsPdo::getInstance($omssync_db_conf);


$tables = $omssync_db->getAll("show tables");
var_dump($tables);
foreach ($tables as $table) {
    foreach ($table as $v) {
        try{
            $sql = "show create table `{$v}`";
            $t = $omssync_db->getAll($sql);
            if (isset($t[0]['Create Table'])) {
                var_dump($t[0]['Create Table']);


                $sql = $t[0]['Create Table'];
                $lw_db->query($sql);
            }
        }catch(Exception $e){
            if (strpos($e->getMessage(), 'already exists') == false) {
                // echo $e->getMessage(); die;
            }

            if (strpos($e->getMessage(), 'already exists') !== false) {
                $lw_table = $lw_db->getAll("show create table `{$v}`");
                $lw_table_sql = $lw_table[0]['Create Table'];
                // if (strlen($sql) !== strlen($lw_table_sql) + 3) {
                //     var_dump($t[0]['Create Table']);
                //     echo $lw_table_sql.PHP_EOL;
                //     // die;
                // }
                $arr1 = explode(',', $sql);
                $arr2 = explode(',', $lw_table_sql);
                // var_dump($arr1);
                // var_dump($arr2);
                if (count($arr1) !== count($arr2)) {
                    // var_dump($arr1);
                        // var_dump($arr2);
                    // echo $sql.PHP_EOL;
                    // echo $lw_table_sql.PHP_EOL;
                    // $lw_db->query("drop table `{$v}`");
                    // $lw_db->query($sql);
                    // die;
                }

            }
            // echo $e->getMessage(); echo PHP_EOL;
        }
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
}
