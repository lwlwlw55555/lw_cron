<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\OrderModel;
use Models\ShopModel;
use Services\ExpressApiService;

require("Services/ExpressApiService.php");
echo("[]".date("Y-m-d H:i:s") . " send_route_pre_by_data_source  begin \r\n");


$end_time = date('Y-m-d H:i:s');

$sql = "select * from last_sync_time where type = 'pre_ship' and last_send_time is not null";
$datasource_send_time_list = $route_drds_db->getAll($sql);
foreach($datasource_send_time_list as $datasource_send_time){
    send_route_pre_by_datasource($datasource_send_time['datasource_name'], $datasource_send_time['table_name'], $datasource_send_time['last_send_time'],$end_time);
}

echo("[]".date("Y-m-d H:i:s") . " send_route_pre_by_data_source end \r\n");

//调用接口  刷新订单状态
function send_route_pre_by_datasource($datasource_name, $table_name, $start_time,$end_time){
    global $route_drds_db;
    if($start_time == null || $start_time == '' ){
        $start_time = '2019-09-20 00:00:00';
    }
    $time = 30*60;
    $start_time_int = strtotime($start_time);
    $end_time_int = strtotime($end_time);
    for($i = $start_time_int ; $i < $end_time_int; $i = $i+$time){
        $data['datasource'] = $datasource_name;
        $data['table_name'] = $table_name;
        $data['start_date'] = date("Y-m-d H:i:s",$i);
        $data['end_date'] = date("Y-m-d H:i:s",$i+$time);
        if(($i+$time) > $end_time_int){
            $data['end_date'] = date("Y-m-d H:i:s",$end_time_int);
        }       
        $result = ExpressApiService::sendRoutePreByDataSource($data);
        if(isset($result['code']) && $result['code'] == 0){
            $end_date = $data['end_date'];
            $sql = "update last_sync_time set last_send_time = '{$end_date}' where datasource_name = '{$datasource_name}' and type = 'pre_ship' ";
            $route_drds_db->query($sql);
        }
    }
}

