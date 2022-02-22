<?php

date_default_timezone_set("Asia/Shanghai");

require ("includes/ClsPdo.php");
require 'vendor/autoload.php';
$hosts = [
    'host' => 'saas-8c53a240'
];

echo date("Y-m-d H:i:s", time())." move es data to history db".PHP_EOL;

$client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
$erp_ddyun_dbuser_conf = array(
    "host" => "100.65.1.0:32053",
    "name" => "erpuser",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$erp_ddyun_dbhistory_db_conf = array(
    "host" => "100.65.2.183:32058",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);

$params = array(
    'index' => 'print_log',
    'body' => [
        'query' => [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'facility_id' => 200004
                        ]
                    ]
                ],
                'filter' => [
                    'range' => [
                        'created_time' => [
                            'gt' => '2020-04-25 00:00:00',
                            'lte' => '2020-05-24 00:00:00'
                        ]
                    ]
                ]
            ]
        ],
        'from' => 0,
        'size' => 400
    ]
);
$db_user = ClsPdo::getInstance($erp_ddyun_dbuser_conf);
$facility_list = $db_user->getAll("select facility_id, db from user");
$start_time = date("Y-m-d 00:00:00", strtotime("2020-04-28 00:00:00"));
$end_time = date("Y-m-d 00:00:00", strtotime("+1 day", strtotime($start_time)));
$limit = 400;
while(true){
    $params['body']['query']['bool']['filter']['range']['created_time']['gt'] = $start_time;
    $params['body']['query']['bool']['filter']['range']['created_time']['lte'] = $end_time;
    foreach ($facility_list as $facility){
        $params['body']['from'] = 0;
        $erp_ddyun_dbhistory_db_conf['name'] = $facility['db'];
        $erp_ddyun_dbhistory_db = ClsPdo::getInstance($erp_ddyun_dbhistory_db_conf);
        while (true){
            $params['body']['query']['bool']['must'][0]['term']['facility_id'] = $facility['facility_id'];
            $result = $client->search($params);
            $result = $result['hits']['hits'];
            if(empty($result)){
                break;
            }
            $sql = "insert ignore into print_log(id, order_id, shipment_id, platform_order_sn, platform_name, print_type, shipping_id, shipping_name, tracking_number, province_id, province_name, city_id, city_name, district_id
, district_name, shipping_address, receive_name, mobile, print_user, created_time, facility_id, warehouse_id, shop_id, batch_sn, thermal_type, batch_order, tactics_id, tactics_name, print_data, print_source) values ";
            foreach ($result as $data){
                $print_log = $data['_source'];
                $print_log['platform_order_sn'] = addslashes($print_log['platform_order_sn']);
                $print_log['receive_name'] = addslashes($print_log['receive_name']);
                $print_log['shipping_address'] = addslashes($print_log['shipping_address']);
                $print_log['mobile'] = addslashes($print_log['mobile']);
                $print_log['print_data'] = addslashes($print_log['print_data']);
                $print_log['tactics_id'] = empty($print_log['tactics_id']) ? 'null' : "'{$print_log['tactics_id']}'";
                $print_log['tactics_name'] = empty($print_log['tactics_name']) ? 'null' : "'{$print_log['tactics_name']}'";
                $sql .= " ('{$print_log['id']}', '{$print_log['order_id']}', '{$print_log['shipment_id']}', '{$print_log['platform_order_sn']}', '{$print_log['platform_name']}', '{$print_log['print_type']}', '{$print_log['shipping_id']}', '{$print_log['shipping_name']}', '{$print_log['tracking_number']}', '{$print_log['province_id']}', '{$print_log['province_name']}', '{$print_log['city_id']}', '{$print_log['city_name']}', {$print_log['district_id']}, '{$print_log['district_name']}', '{$print_log['shipping_address']}', '{$print_log['receive_name']}', '{$print_log['mobile']}', '{$print_log['print_user']}', '{$print_log['created_time']}', '{$print_log['facility_id']}', '{$print_log['warehouse_id']}', '{$print_log['shop_id']}', '{$print_log['batch_sn']}', '{$print_log['thermal_type']}', '{$print_log['batch_order']}', ".$print_log['tactics_id'].", ".$print_log['tactics_name'].", '{$print_log['print_data']}', '{$print_log['print_source']}'),";
            }
            $sql = substr($sql, 0, -1);
            $erp_ddyun_dbhistory_db->query($sql);
            echo date("Y-m-d H:i:s")." insert ".count($result). " print_log into history db : facility : ".$facility['facility_id']." db : ".$facility['db'].PHP_EOL;
            if(count($result) < 400){
                break;
            }
            $params['body']['from'] += $limit;
        }
        echo date("Y-m-d H:i:s")." facility : ".$facility['facility_id']." db : ".$facility['db']." from ".$start_time." to ".$end_time." has inserted finish".PHP_EOL;
    }
    echo date("Y-m-d H:i:s", time())." data has moved finish".PHP_EOL;
    if($start_time >= "2020-07-29 00:00:00"){
        break;
    }
    $start_time = $end_time;
    $end_time = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($start_time)));
}