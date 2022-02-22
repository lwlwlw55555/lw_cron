<?php
require("includes/init.php");
use Services\ExpressApiService;
require("Services/ExpressApiService.php");
echo("[]".date("Y-m-d H:i:s") . " refresh_route_by_datasource  begin \r\n");

global $db,$db_user;

$shipping_id = null;
$logistic_status = null;
$batch = false;
$group = null;
$mod = null;

$param_arr = getopt('s:l:b:g:m:');
if(isset($param_arr['s'])){
    $shipping_id = $param_arr['s'];
}
if(isset($param_arr['l'])){
    $logistic_status = $param_arr['l'];
}
if(isset($param_arr['b'])){
    $batch = $param_arr['b'] == 1 ? true:false;
}
if(isset($param_arr['g'])){
    $group = $param_arr['g'];
}
if(isset($param_arr['m'])){
    $mod = $param_arr['m'];
}

$cond = "";
if (!empty($group)) {
   $cond .= " AND mod(u.facility_id, {$group}) = {$mod}";
}
$sql = "
    select 
        u.facility_id
    from 
        user u 
        inner join pay_oauth po on u.user_id = po.user_id 
    where 
        po.expire_time > date_sub(curdate(), interval 7 DAY)
        {$cond}
";

$facility_ids = $db_user->getCol($sql);

foreach ($facility_ids as $facility_id) {
    refresh_route_datasource($facility_id,$shipping_id, $logistic_status,$batch);
}


echo("[]".date("Y-m-d H:i:s") . " refresh_route_by_datasource end \r\n");

function refresh_route_datasource($facility_id, $shipping_id, $logistic_status,$batch) {
    $data = array(
        'facility_id' => $facility_id,
        'shipping_id' => $shipping_id,
        'logistic_status' => $logistic_status,
        'batch' => $batch
    );
    ExpressApiService::refreshRouteDatasource($data);
}