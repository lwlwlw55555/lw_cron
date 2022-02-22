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
    // "pass" => "123456",
    "charset" => "utf8",
    "pconnect" => "1",
    // "name" => "omssync"
    "name" => "omsshop"
);
$lw_db = ClsPdo::getInstance($lw_conf);

$omssync_db_conf = array(
    "host" => "rm-bp10hv462sva1muzk5o.mysql.rds.aliyuncs.com:3306",
    "user" => "admin_omsv2",
    "pass" => "7o%01XSpZPE%",
    "charset" => "utf8",
    "pconnect" => "1",
    // "name" => "omssync"
     "name" => "omsshop"
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
