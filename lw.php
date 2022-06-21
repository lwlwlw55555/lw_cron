<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

// var_dump($_REQUEST);

// var_dump($_POST);

// var_dump($_GET);x

// $datas = file_get_contents("php://input");

// var_dump($datas);

echo json_encode($_REQUEST);

// curl -X POST -H 'content-type: application/x-www-form-urlencoded' -d 'key1=val1&key2=val2&key3=val3' "http://121.40.113.153/lw.php"
// curl -X POST -H "Content-Type: application/json" -d '{"facility_id": 5007350}'  "http://121.40.113.153/lw.php"
// curl -X GET "http://121.40.113.153/lw.php?lw=lw"

