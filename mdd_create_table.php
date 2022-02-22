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
    "user" => "root",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_0"
);
$lw_db = ClsPdo::getInstance($lw_conf);

$omssync_db_conf = array(
    "host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com:3306",
    "user" => "erp_test",
    "pass" => "zgsdi67f8PhQ",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_0"
);
$omssync_db = ClsPdo::getInstance($omssync_db_conf);


$tables = $omssync_db->getAll("show tables");
var_dump($tables);
foreach ($tables as $table) {
    foreach ($table as $v) {
        try{
            $sql = "show create table {$v}";
            $t = $omssync_db->getAll($sql);
            var_dump($t[0]['Create Table']);
            $sql = $t[0]['Create Table'];
           $lw_db->query($sql);
        }catch(Exception $e){
            if (strpos($e->getMessage(), 'already exists') == false) {
                // echo $e->getMessage(); die;
            }
        }
        // $sql = "SELECT auto_increment FROM information_schema.tables where table_schema='omssync' and table_name='{$v}'";
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
