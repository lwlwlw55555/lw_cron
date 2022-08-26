<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

global $db;

// http://121.40.113.153/leqee_refresh_token.php?is_search=true
if (array_key_exists('is_search', $_REQUEST) && $_REQUEST['is_search'] == 'true') {
	$config_value = $db->getOne("select config_value from common_config where config_key = 'TOKEN_MAPPING'");
	//!!!! 用echo + return 不要直接用return !!!!
	echo json_encode(['code'=>0,'data'=>json_decode($config_value)]);
	return;
}

if (empty($_REQUEST) || (!array_key_exists('leqee_token', $_REQUEST) && !array_key_exists('gyc_token', $_REQUEST))) {
	echo json_encode(['code'=>1,'data'=>'error params']);
	//!!!! 用echo + return 不要直接用return !!!!
	return;
}

if (!isset($_REQUEST['leqee_token']) || !isset($_REQUEST['gyc_token'])) {
	$config_value = $db->getOne("select config_value from common_config where config_key = 'TOKEN_MAPPING'");
	$config_arr = json_decode($config_value,true);
	if (!isset($_REQUEST['leqee_token'])) {
		$_REQUEST['leqee_token'] = isset($config_arr['leqee'])?$config_arr['leqee']:'';
	}else{
		$_REQUEST['gyc_token'] = isset($config_arr['gyc'])?$config_arr['gyc']:'';
	}
}

LeqeeDbService::refreshLeqeeToken($_REQUEST['leqee_token'],$_REQUEST['gyc_token']);

echo json_encode(['code'=>0,'data'=>null]);
//!!!! 用echo + return 不要直接用return !!!!
return;