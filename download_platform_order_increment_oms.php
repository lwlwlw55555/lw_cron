<?php
require("includes/init.php");
use Models\PlatformExtensionModel;
require("Services/OMSExpressApiService.php");
use Services\OMSExpressApiService;
echo("[]".date("Y-m-d H:i:s") . " downloadPlatformOrderIncrement  begin \r\n");

$platform_name = 'TAOBAO';
// if (isset($argv[1])){
//     $platform_name = $argv[1];
// }else{
//     $platform_name = 'TAOBAO';
// }

$time_rank = 1;
if (isset($argv[1])){
    $time_rank = $argv[1];
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


$sql = 
"SELECT last_plan_sync_time_{$time_rank} 
FROM platform_extension 
WHERE platform_name = '{$platform_name}'
and type = 'order'
";
 print_r($sql);
$last_plan_sync_time = $oms_sync_db->getOne($sql);
if(empty($last_plan_sync_time)){
    echo "[]"."error : last_plan_sync_time is null".PHP_EOL;
    die;
}

// $start_time = strtotime($last_plan_sync_time)-10;
$start_time = strtotime($last_plan_sync_time);
$start_time  = strtotime(date("Y-m-d H:i:00", $start_time));
$last_time = strtotime(date("Y-m-d H:i:00", time()));
if($start_time > $last_time){
    echo "[]"."error : start_time > last_time".PHP_EOL;
    die;
}
// $start_add_time = ($time_rank-1)*($last_time-$start_time)/6;
// $end_add_time = $time_rank*($last_time-$start_time)/6;
echo "[]".$start_time."\r\n";
echo "[]".$last_time."\r\n";
for(;$start_time<$last_time;$start_time +=60){
	for($i=10*($time_rank-1);$i<10*$time_rank;$i++){
		$start_time_str = date("Y-m-d H:i:s", $start_time+$i);
        $end_time_str = date("Y-m-d H:i:s", $start_time+$i+1);
		$result = OMSExpressApiService::syncTaobaoPlatformOrderWithTimestamp($start_time_str,$end_time_str);
		if(!isset($result['code']) || $result['code'] > 0){
            echo "[]"."error: ";
            var_export($result);
            echo "[]".PHP_EOL;
            $error_json = addslashes(json_encode($result));
            $sql_error = "INSERT INTO platform_extension_error (platform_name, begin_time, end_time, error_msg, type)
                    VALUES ('TAOBAO','{$start_time_str}','{$end_time_str}','{$error_json}','order')";        
            $sync_db->query($sql_error);
            // die;
            continue;
        }
	}
    $new_last_plan_sync_time = date("Y-m-d H:i:00", $start_time+60);
    updatePlatformExtension($new_last_plan_sync_time,$time_rank);
}

// if ($time_rank == 6) {

// }
echo("[]".date("Y-m-d H:i:s") . " downloadPlatformOrderIncrement end \r\n");

function updatePlatformExtension($new_last_plan_sync_time,$time_rank){
    global $oms_sync_db;
    $sql_update_time = 
    "UPDATE platform_extension 
    SET last_plan_sync_time_{$time_rank} = '{$new_last_plan_sync_time}'
    WHERE platform_name = 'TAOBAO' and type = 'order'";
    $oms_sync_db->query($sql_update_time);
}