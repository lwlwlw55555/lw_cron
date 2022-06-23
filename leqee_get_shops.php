<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

global $db;

$sql = "select shop_id,shop_name,shop_nick from omssync.shop where is_delete = 'N' and platform = 'TAOBAO'";

$shops = $db->getAll($sql);

var_export($shops);

echo json_encode(['code'=>0,'data'=>$shops]);