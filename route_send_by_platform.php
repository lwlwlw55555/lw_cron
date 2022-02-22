<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Services\ExpressApiService;

require("Services/ExpressApiService.php");
echo("[]".date("Y-m-d H:i:s") . " send_route_by_platform_name  begin \r\n");


global $route_drds_db;
$end_time = date('Y-m-d H:i:s');

$sql = "select * from last_sync_time where type = 'shipped'";
$platform_send_time_list = $route_drds_db->getAll($sql);
foreach($platform_send_time_list as $platform_send_time){
    send_route_by_platform($platform_send_time['datasource_name'],$platform_send_time['last_send_time'],$end_time);
}

echo("[]".date("Y-m-d H:i:s") . " send_route_by_platform_name end \r\n");

//调用接口  刷新订单状态
function send_route_by_platform($platform_name,$start_time,$end_time){
    global $route_drds_db;
    if($start_time == null || $start_time == '' ){
        $start_time = '2020-04-04 00:00:00';
    }


    $time = 5*60;
    $start_time_int = strtotime($start_time);
    $end_time_int = strtotime($end_time);
    //将请求切分成每5分钟为一个时间段
    for($i = $start_time_int ; $i < $end_time_int; $i = $i+$time){
        $data['platform_name'] = $platform_name;
        $data['start_date'] = date("Y-m-d H:i:s",$i);
        $data['end_date'] = date("Y-m-d H:i:s",$i+$time);
        if(($i+$time) > $end_time_int){
            $data['end_date'] = date("Y-m-d H:i:s",$end_time_int);
        }       
        $result = ExpressApiService::sendRouteByDataSource($data);
        if(isset($result['code']) && $result['code'] == 0){
            $end_time = $data['end_date'];
            //执行完了则将店铺的last_refresh_time修改
            $sql = "update last_sync_time set last_send_time = '{$end_time}' where type = 'shipped' and datasource_name = '{$platform_name}'";
            $route_drds_db->query($sql);;
        }else{
            echo("[]".date("Y-m-d H:i:s") . " platforn_name:{$platform_name} 推送失败 \r\n");
        }
        sleep(2);
    }
}

