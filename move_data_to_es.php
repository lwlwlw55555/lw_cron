<?php

date_default_timezone_set("Asia/Shanghai");
require 'vendor/autoload.php';
$hosts = [
    'host' => 'saas-8c53a240'
];
$client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

require ("includes/ClsPdo.php");

$erp_ddyun_dbuser_conf = array(
    "host" => "100.65.1.0:32053",
	"name" => "erpuser",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);
$db_user = ClsPdo::getInstance($erp_ddyun_dbuser_conf);
$origin_file_name = 'move data ['.date('Y-m-d H:i:s', time()).']';
$sql = "insert into import_center (type, origin_file_name, status) value ('move data to es', '{$origin_file_name}', 'DOING')";
$result = $db_user->query($sql);
$import_center_id = $db_user->getOne("select last_insert_id()");
echo "move data from db to es : table : print_log". PHP_EOL;
$todayTime = date('Y-m-d 00:00:00', time());
$yesterdayTime = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($todayTime)));
$count_sum = 0;
$erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );
for ($i = 0 ; $i <= 255; $i++) {
    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

    $limit = 0;
    do{
        $print_log_list = $erp_ddyun_db->getAll("select * from print_log where created_time < '{$todayTime}' and created_time >= '{$yesterdayTime}' limit {$limit}, 1000");
        echo $erp_ddyun_db_conf['name']." ".count($print_log_list)."  sql: select * from print_log where created_time < '{$todayTime}' and created_time >= '{$yesterdayTime}' limit {$limit}, 1000".PHP_EOL;
        $limit += 1000;
        $j = 0;
        $params = ['body' => []];
        foreach ($print_log_list as $print_log){
            $j++;
            $params['body'][] = array(
                'index' => array(
                    '_index' => 'print_log',
                    '_type' => '_doc',
                    '_id' => $print_log['id']
                )
            );
            $params['body'][] = [
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
            ];
            if ($j % 1000 == 0) {
                $responses = $client->bulk($params);
                $params = ['body' => []];
                unset($responses);
            }
        }
        if (!empty($params['body'])) {
            $responses = $client->bulk($params);
        }
        $count_sum += count($print_log_list);
    }while(count($print_log_list) == 1000);
    echo $erp_ddyun_db_conf['name']." move data end \r\n";
}
echo "move data end ";
$db_user = ClsPdo::getInstance($erp_ddyun_dbuser_conf);
$result = date('Y-m-d', time())." print_log 数据已迁移至es";
$sql = "update import_center set status = 'DONE', result = '{$result}', count_sum = {$count_sum} where id = {$import_center_id}";
$db_user->query($sql);
//$mailnos_list = $user_db->getAll("select * from mailnos");
//foreach ($mailnos_list as $mailnos){
//    $params = [
//        'index' => 'mailnos_test',
//        'id'   =>  $mailnos['id'],
//        'body' => [
//            'id' => $mailnos['id'],
//            'tracking_number'=> $mailnos['tracking_number'],
//            'status'=> $mailnos['status'],
//            'print_type'=> $mailnos['print_type'],
//            'shipping_id'=> $mailnos['shipping_id'],
//            'station'=> $mailnos['station'],
//            'station_no'=> $mailnos['station_no'],
//            'sender_branch_no'=> $mailnos['sender_branch_no'],
//            'sender_branch'=> $mailnos['sender_branch'],
//            'package_no'=> $mailnos['package_no'],
//            'package_name'=> $mailnos['package_name'],
//            'lattice_mouth_no'=> $mailnos['lattice_mouth_no'],
//            'express_type'=> $mailnos['express_type'],
//            'pay_method'=> $mailnos['pay_method'],
//            'origin_code'=> $mailnos['origin_code'],
//            'desc_code'=> $mailnos['desc_code'],
//            'package_id'=> $mailnos['package_id'],
//            'created_time'=> $mailnos['created_time'],
//            'last_update_time'=> $mailnos['last_update_time'],
//            'thermal_type'=> $mailnos['thermal_type'],
//            'facility_id'=> $mailnos['facility_id'],
//            'warehouse_id'=> $mailnos['warehouse_id'],
//            'oauth_id'=> $mailnos['oauth_id'],
//            'oauth_share_id'=> $mailnos['oauth_share_id'],
//            'encrypted_data'=> $mailnos['encrypted_data'],
//            'signature'=> $mailnos['signature']
//        ]
//    ];
//    $response = $client->index($params);
//}

