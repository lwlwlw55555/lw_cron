<?php

date_default_timezone_set("Asia/Shanghai");
require 'vendor/autoload.php';
$hosts = [
    'host' => 'saas-8c53a240'
];
global $client;
$client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

require ("includes/ClsPdo.php");

echo "inspection data from db to es : table : print_log  " . date('Y-m-d H:i:s') . PHP_EOL;
$params = [
    'index' => 'print_log',
    'body' => [
        'query' => [
            'term' => [
                'id' => 0
            ]
        ]
    ]
];
$todayTime = date('Y-m-d 00:00:00', time());
$yesterdayTime = date('Y-m-d 00:00:00', strtotime("-1day", strtotime($todayTime)));
for ($i = 0 ; $i <= 255; $i++) {
    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );

    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    global $erp_ddyun_db;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

    $limit = 0;
    do{
        $print_log_list = $erp_ddyun_db->getAll("select * from print_log where created_time < '{$todayTime}' and created_time >= '{$yesterdayTime}' limit {$limit}, 1000");
        echo $erp_ddyun_db_conf['name']." ".count($print_log_list)."  sql: select * from print_log where created_time < '{$todayTime}' and created_time >= '{$yesterdayTime}' limit {$limit}, 1000".PHP_EOL;
        $limit += 1000;
        foreach ($print_log_list as $print_log){
            $params['body']['query']['term']['id'] = $print_log['id'];
            $result = $client->search($params);
            $data = $result['hits']['hits'];
            if(count($data) == 1){
                $data = $data[0]['_source'];
                if(!($data['id'] == $print_log['id']
                    && $data['order_id'] == $print_log['order_id']
                    && $data['shipment_id'] == $print_log['shipment_id']
                    && $data['platform_order_sn'] == $print_log['platform_order_sn']
                    && $data['platform_name'] == $print_log['platform_name']
                    && $data['print_type'] == $print_log['print_type']
                    && $data['shipping_id'] == $print_log['shipping_id']
                    && $data['shipping_name'] == $print_log['shipping_name']
                    && $data['tracking_number'] == $print_log['tracking_number']
                    && $data['province_id'] == $print_log['province_id']
                    && $data['province_name'] == $print_log['province_name']
                    && $data['city_id'] == $print_log['city_id']
                    && $data['city_name'] == $print_log['city_name']
                    && $data['district_id'] == $print_log['district_id']
                    && $data['district_name'] == $print_log['district_name']
                    && $data['shipping_address'] == $print_log['shipping_address']
                    && $data['receive_name'] == $print_log['receive_name']
                    && $data['mobile'] == $print_log['mobile']
                    && $data['print_user'] == $print_log['print_user']
                    && $data['created_time'] == $print_log['created_time']
                    && $data['facility_id'] == $print_log['facility_id']
                    && $data['warehouse_id'] == $print_log['warehouse_id']
                    && $data['shop_id'] == $print_log['shop_id']
                    && $data['batch_sn'] == $print_log['batch_sn']
                    && $data['thermal_type'] == $print_log['thermal_type']
                    && $data['batch_order'] == $print_log['batch_order']
                    && $data['tactics_id'] == $print_log['tactics_id']
                    && $data['tactics_name'] == $print_log['tactics_name']
                    && $data['print_data'] == $print_log['print_data']
                    && $data['print_source'] == $print_log['print_source'])){
                    echo $erp_ddyun_db_conf['name']."  id : ".$print_log['id']." 数据不一致 \r\n";
                    die;
                }
            }else{
                echo $erp_ddyun_db_conf['name']."  id : ".$print_log['id']." count : ". count($data)." id数量不一致\r\n";
                die;
            }
        }
    }while(count($print_log_list) == 1000);
    echo $erp_ddyun_db_conf['name'] ." inspection finish ".date('Y-m-d H:i:s')."\r\n";
}
echo "inspection finish from {$yesterdayTime} to {$todayTime}";


function insertData($id){
    global $erp_ddyun_db;
    global $client;
    $print_log = $erp_ddyun_db->getRow("select * from print_log where id = {$id}");
    $params = [
        'index' => 'print_log',
        'type'  => '_doc',
        'id'    => $id,
        'body' => [
            'id'                    =>  $print_log['id'],
            'order_id'              =>  $print_log['order_id'],
            'shipment_id'           =>  $print_log['shipment_id'],
            'platform_order_sn'     =>  $print_log['platform_order_sn'],
            'platform_name'         =>  $print_log['platform_name'],
            'print_type'            =>  $print_log['print_type'],
            'shipping_id'           =>  $print_log['shipping_id'],
            'shipping_name'         =>  $print_log['shipping_name'],
            'tracking_number'       =>  $print_log['tracking_number'],
            'province_id'           =>  $print_log['province_id'],
            'province_name'         =>  $print_log['province_name'],
            'city_id'               =>  $print_log['city_id'],
            'city_name'             =>  $print_log['city_name'],
            'district_id'           =>  $print_log['district_id'],
            'district_name'         =>  $print_log['district_name'],
            'shipping_address'      =>  $print_log['shipping_address'],
            'receive_name'          =>  $print_log['receive_name'],
            'mobile'                =>  $print_log['mobile'],
            'print_user'            =>  $print_log['print_user'],
            'created_time'          =>  $print_log['created_time'],
            'facility_id'           =>  $print_log['facility_id'],
            'warehouse_id'          =>  $print_log['warehouse_id'],
            'shop_id'               =>  $print_log['shop_id'],
            'batch_sn'              =>  $print_log['batch_sn'],
            'thermal_type'          =>  $print_log['thermal_type'],
            'batch_order'           =>  $print_log['batch_order'],
            'tactics_id'            =>  $print_log['tactics_id'],
            'tactics_name'          =>  $print_log['tactics_name'],
            'print_data'            =>  $print_log['print_data'],
            'print_source'          =>  $print_log['print_source']
        ]
    ];
    $response = $client->index($params);
    echo 'id: '.$id ." has insert into es .\r\n";
}