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

global $oms_db;
$oms_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_0"
);
$oms_db = ClsPdo::getInstance($oms_db_conf);

$oms_1_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_1"
);
$oms_1_db = ClsPdo::getInstance($oms_1_db_conf);


$tables = $oms_db->getAll("show tables");
var_dump($tables);
foreach ($tables as $table) {
    foreach ($table as $v) {
        $sql = "show create table {$v}";
        $t = $oms_db->getAll($sql);
        var_dump($t[0]['Create Table']);
        $sql = $t[0]['Create Table'];
        $oms_1_db->query($sql);
        $sql = "SELECT auto_increment FROM information_schema.tables where table_schema='mddoms_1' and table_name='{$v}'";
        var_dump($sql);
        $num = $oms_1_db->getOne($sql);
        var_dump($num);
        $sql = "alter table {$v} auto_increment =".($num+1000000);
        var_dump($sql);
        $num = $oms_1_db->query($sql);
        // die;
        // var_dump($t);die;
    }
}
// <?php
// require("includes/init.php");
// require("Models/ShopModel.php");
// require("Models/OrderModel.php");
// use Models\ShopModel;
// use Models\OrderModel;
// require("Services/ExpressApiService.php");
// use Services\ExpressApiService;
// include 'request/OrderNumberListGetRequest.php';
// include 'PddClient.php';

// global $oms_db;
// $oms_db_conf = array(
//     "host" => "100.65.1.202:32001",
//     "user" => "mddomsapi",
//     "pass" => "123JoisnD0",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     "name" => "mddoms_0"
// );
// $oms_db = ClsPdo::getInstance($oms_db_conf);

// $oms_1_db_conf = array(
//     "host" => "100.65.1.202:32001",
//     "user" => "mddomsapi",
//     "pass" => "123JoisnD0",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     "name" => "mddoms_1"
// );
// $oms_1_db = ClsPdo::getInstance($oms_1_db_conf);


// $tables = $oms_db->getAll("show tables");
// // var_dump($tables);
// foreach ($tables as $table) {
//     foreach ($table as $v) {
//         if ($v == 'backup_old_shipment_exception_flag') {
//             continue;
//         }
//         if ($v == 'shipment_exception_flag') {
//             continue;
//         }
//         // $sql = "show create table {$v}";
//         // $t = $oms_db->getAll($sql);
//         // var_dump($t[0]['Create Table']);
//         // $sql = $t[0]['Create Table'];
//         // $oms_1_db->query($sql);
//         $sql = "SELECT auto_increment FROM information_schema.tables where table_schema='mddoms_0' and table_name='{$v}'";
//         // var_dump($sql);
//         $num = $oms_1_db->getOne($sql);
//         // var_dump($num);
//         $inteval = 1000000;
//         if ($num/1000000 > 1) {
//             $inteval = 10000000;
//             $sql = "alter table {$v} auto_increment =".($num+$inteval);
//             var_dump($sql);
//             $oms_1_db->query($sql);
//         }
//         // die;
//         // var_dump($t);die;
//     }
// }