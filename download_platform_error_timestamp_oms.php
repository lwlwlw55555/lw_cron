<?php
require("includes/init.php");
use Models\PlatformExtensionModel;
require("Services/OMSExpressApiService.php");
use Services\OMSExpressApiService;
echo("[]".date("Y-m-d H:i:s") . " downloadPlatformErrorTimestamp  begin \r\n");

if (!isset($argv[1])){
	echo "[]"."params error: 缺少type参数(order or goods?)".PHP_EOL;
	die;	
}
$type = $argv[1];

$platform_name = null;
if (isset($argv[2])){
    $platform_name = $argv[2];
}else{
    $platform_name = 'TAOBAO';
}

global $oms_sync_db;
$oms_sync_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomssync",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomssync"
);
$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);


$sql_get_error_records = "SELECT platform_extension_error_id,begin_time,end_time FROM platform_extension_error 
						WHERE type = '{$type}' AND platform_name = '{$platform_name}'";
$errors = $oms_sync_db->getAll($sql_get_error_records);
foreach ($errors as $error) {
	echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
	echo "[]".date("Y-m-d H:i:s").' '.json_encode($error)."begin:".PHP_EOL;
	if (empty($error['begin_time']) || empty($error['end_time'])) {
		echo "[]"."params error: 缺少时间参数".PHP_EOL;
		var_export($error);
		echo "[]".date("Y-m-d H:i:s").' '.json_encode($error)."end".PHP_EOL;
		continue;
	}
	$result = null;
	if ('order' == $type) {
		$result = OMSExpressApiService::syncTaobaoPlatformOrderWithTimestamp($error['begin_time'],$error['end_time']);
		if(!isset($result['code']) || $result['code'] > 0){
			echo "[]"."error: ";
	        var_export($result);
	        echo "[]".PHP_EOL;
	        echo "[]".date("Y-m-d H:i:s").' '.json_encode($error)."end".PHP_EOL;
	        continue;
		}
	}elseif('goods' == $type) {
		$begin_time = $error['begin_time'];
		while ($begin_time < $error['end_time']) {
			$end_time = date("Y-m-d H:i:s",strtotime("{$begin_time} + 1 second"));
			$result = OMSExpressApiService::syncTaobaoPlatformGoodsWithTimestamp($begin_time,$end_time);
			if(!isset($result['code']) || $result['code'] > 0){
				echo "[]"."error: ";
		        var_export($result);
		        echo "[]".PHP_EOL;
		        echo "[]".date("Y-m-d H:i:s").' '.json_encode($error)."end".PHP_EOL;
		        continue 2;
			}
			$begin_time = $end_time;
		}
	}
	
	$sql_delete = "DELETE FROM platform_extension_error where platform_extension_error_id = {$error['platform_extension_error_id']}";
	echo "[]".date("Y-m-d H:i:s").' '.$sql_delete.PHP_EOL;
	$sync_db->query($sql_delete);
	echo "[]".date("Y-m-d H:i:s").' '.json_encode($error)."end".PHP_EOL;
}

echo "[]".PHP_EOL.PHP_EOL.PHP_EOL;
echo("[]".date("Y-m-d H:i:s") . " downloadPlatformErrorTimestamp  end \r\n");