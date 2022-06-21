<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

if (empty($_REQUEST) || !array_key_exists('leqee_token', $_REQUEST) || !array_key_exists('gyc_token', $_REQUEST)) {
	return json_encode(['code'=>1,'data'=>'error params']);
}

LeqeeDbService::refreshLeqeeToken($_REQUEST['leqee_token'],$_REQUEST['gyc_token']);

return json_encode(['code'=>0,'data'=>null]);