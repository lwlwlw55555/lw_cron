<?php
require("includes/init.php");
use Models\PlatformExtensionModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo("[]".date("Y-m-d H:i:s") . " downloadPlatformGoodsIncrement  begin \r\n");

global $sync_db;
$platform_name = null;
if (isset($argv[1])){
    $platform_name = $argv[1];
}else{
    $platform_name = 'taobao';
}

$sql = 
"SELECT last_plan_sync_time_1 as last_plan_sync_time
FROM platform_extension 
WHERE platform_name = '{$platform_name}'
and type = 'goods'
";

$last_plan_sync_time = $sync_db->getOne($sql);
if(empty($last_plan_sync_time)){
    echo "[]"."error : last_plan_sync_time is null".PHP_EOL;
    die;
}

$start_time = strtotime($last_plan_sync_time)-10;
$last_time = time();
if($start_time > $last_time){
    echo "[]"."error : start_time > last_time".PHP_EOL;
    die;
}
for(;$start_time<$last_time;$start_time +=1*10){
    $end_time = ($start_time + 1*10) > $last_time?$last_time:($start_time + 1*10);
    $start_time_str = date("Y-m-d H:i:s", $start_time);
    $end_time_str = date("Y-m-d H:i:s", $end_time);
    $result = ExpressApiService::syncTaobaoPlatformGoodsWithTimestamp($start_time_str,$end_time_str);
    if(!isset($result['code']) || $result['code'] > 0){
        echo "[]"."error: ";
        var_export($result);
        echo "[]".PHP_EOL;
        $error_json = addslashes(json_encode($result));
        $sql_error = "INSERT INTO platform_extension_error (platform_name, begin_time, end_time, error_msg, type)
                VALUES ('taobao','{$start_time_str}','{$end_time_str}','','goods')";        
        $sync_db->query($sql_error);
        // die;
        continue;
    }
}
$new_last_plan_sync_time = date("Y-m-d H:i:s", $end_time);
updatePlatformExtension($new_last_plan_sync_time);
echo("[]".date("Y-m-d H:i:s") . " downloadPlatformGoodsIncrement end \r\n");

function updatePlatformExtension($new_last_plan_sync_time){
    global $sync_db;
    $sql_update_time = 
    "UPDATE platform_extension 
    SET last_plan_sync_time_1 = '{$new_last_plan_sync_time}'
    WHERE platform_name = 'taobao' and type = 'goods'";
    $sync_db->query($sql_update_time);
}

