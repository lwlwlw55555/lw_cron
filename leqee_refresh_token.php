<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

global $db;

if (array_key_exists('is_search', $_REQUEST) && $_REQUEST['is_search']) {
	return json_encode(['code'=>0,'data'=>json_decode($db->getOne("select config_value from common_config where config_key = 'TOKEN_MAPPING'"));
}

if (empty($_REQUEST) || !array_key_exists('leqee_token', $_REQUEST) || !array_key_exists('gyc_token', $_REQUEST)) {
	echo json_encode(['code'=>1,'data'=>'error params']);
}


LeqeeDbService::refreshLeqeeToken($_REQUEST['leqee_token'],$_REQUEST['gyc_token']);

echo json_encode(['code'=>0,'data'=>null]);