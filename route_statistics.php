<?php

require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

global $sync_db, $stat_drds_db;

$erp_ddyun_route_db_conf = array(
    "host" => "100.65.1.118",
    "port" => "32054",
    "name" => "erp_route_stat",
    "user" => "erp_route",
    "pass" => "Titanerproute2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$route_db = ClsPdo::getInstance($erp_ddyun_route_db_conf);

$sync_shipped_order_count = $sync_db->getOne("select count(1) from sync_pinduoduo_order_info where order_status in (2, 3) and shipping_time >= CURDATE()");

$route_shipped_order_count = $route_db->getOne("select count(1) from mailnos where shipped_time >= CURDATE()");

$route_accept_order_count = $route_db->getOne("select count(1) from mailnos where shipped_time >= CURDATE() and accept_time >= CURDATE()");

$route_station_order_count = $route_db->getOne("select count(1) from mailnos where shipped_time >= CURDATE() and accept_time >= CURDATE() and station_time >= CURDATE()");

$title = Date("Y-m-d", time())."物流发货揽收率";
$header = array(array('', '当天发货量', '当天揽收量', '当天产生第一条物流量'));
$report_data = array(
    array(
        'type' => '数量',
        'shipped' => $route_shipped_order_count,
        'accept' => $route_accept_order_count,
        'station' => $route_station_order_count
    ),
    array(
        'type' => '比例',
        'shipped' => '100%' ,
        'accept'  => ($route_accept_order_count/$route_shipped_order_count) * 100 .'%',
        'station' => ($route_station_order_count/$route_shipped_order_count) * 100 .'%'
    )
);
$key_array = array('type', 'shipped', 'accept', 'station');
send_erp_email($title, $header, $key_array, $report_data, 1, ['jwang@titansaas.com','hbgeng@titansaas.com']);