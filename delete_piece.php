<?php
require("includes/init.php");


$history_piece = array(
    "port" => "3306",
    "user" => "expresser",
    "pass" => "TDXXaeQm6PVS08jq",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mysql"
);

$hosts = array(
    "rm-bp1d220tvuz2q646z.mysql.rds.aliyuncs.com",
    "rm-bp1u2d5f04ufla0y5.mysql.rds.aliyuncs.com",
);


$history_piece["host"] = $hosts[$argv[1]];

$drds_db = ClsPdo::getInstance($history_piece);

$databases = $drds_db->getAll("show databases");
var_dump($databases);

