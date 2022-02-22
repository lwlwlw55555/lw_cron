<?php
require("includes/init.php");
date_default_timezone_set("Asia/Shanghai");

require("Services/ExpressApiService.php");
use Services\ExpressApiService;

global $sync_db;
$shop_id = $argv[1];
$erp_ddyun_db_user_conf = array(
    "host" => "100.65.1.0:32053",
    "name" => "erpuser",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);
$erp_ddyun_db_conf = array(
    "host" => "100.65.1.0:32053",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);
global $erp_ddyun_db, $erp_ddyun_user_db;
$erp_ddyun_user_db = ClsPdo::getInstance($erp_ddyun_db_user_conf);
$facility_id = $sync_db->getOne("select default_facility_id from shop where shop_id = {$shop_id}");
$erp_ddyun_db_conf['name'] = $erp_ddyun_user_db->getOne("select db from user where facility_id = {$facility_id}");

$erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
$app = $erp_ddyun_db->getRow("select 
            s.shop_id, 
            s.access_token, 
            a.platform_app_key, 
            a.platform_app_secret ,
                es.state
        from shop s 
        inner join app a on s.app_key = a.platform_app_key 
        left join erpuser.encrypt_shop es on s.shop_id = es.shop_id 
        where s.shop_id = {$shop_id}");
if ($app['state'] == 'DONE') {
        echo " encrypt_shop is DONE diediedie";die;
}
$erp_ddyun_user_db->query("replace into encrypt_shop(shop_id, state, is_encrypt_finish) values ({$shop_id}, 'TODO', 0)");


encryptDataByShop($shop_id, $app);
$erp_ddyun_user_db->query("update encrypt_shop set is_encrypt_finish = 1 , state = 'DONE' where shop_id = {$shop_id}");

function encryptDataByShop($shop_id, $app){
    global $erp_ddyun_db, $erp_ddyun_user_db;

    $max_shipment_id = 0;
    while(true){
        $beginsss = microtime(true);
        $sql = "select 
                mgs.shipment_id,
                mgs.platform_order_sn, 
                mgs.shop_id, 
                mgs.district_id, 
                mgs.shipping_address, 
                mgs.receive_name, 
                mgs.mobile ,
                '{$app['platform_app_key']}' as 'app_key',
                '{$app['platform_app_secret']}' as 'app_secret',
                '{$app['access_token']}' as 'access_token'
            from 
                multi_goods_shipment mgs
            left join multi_goods_shipment_extension mgse on mgs.facility_id = mgse.facility_id and mgs.shipment_id = mgse.shipment_id 
            where mgs.shop_id = {$shop_id} 
            and mgse.mobile_encrypt is null
            and mgs.shipment_id > {$max_shipment_id}
            order by mgs.shipment_id 
            limit 100";
        $shipment_list = $erp_ddyun_db->getAll($sql);
        if (empty($shipment_list)) {
                echo " done done done";
                break;
        }
        $param['encrypt_data_list'] = array();
        foreach ($shipment_list as $key => $shipment){
            if($shipment['shipment_id'] > $max_shipment_id){
                $max_shipment_id = $shipment['shipment_id'];
            }
                if (! $shipment['shipping_address'] || ! $shipment['mobile'] || !$shipment['receive_name'] || $shipment['mobile'] == '1999999999' 
        || strpos($shipment['mobile'], '****') !== false) {
                        echo " shipment validddd continue continue\n";
                        continue;
                }
            $param['encrypt_data_list'][] = $shipment_list[$key];
        }
        $response = ExpressApiService::getEncryptAndDecryptBatch($param);
        if(! $response || $response['code'] > 0){
            echo date("Y-m-d H:i:s")." request ".json_encode($param)."  response : ".json_encode($response).PHP_EOL;
                echo $sql;
            $erp_ddyun_user_db->query("update encrypt_shop set state = 'FAIL', message = 'loglog' where shop_id = {$shop_id}");
            die;
        }
        $result = $response['data']['decrypt_and_encrypt_list'];
        foreach ($result as $encrypt_item){
            $shipment = $erp_ddyun_db->getRow("select shipment_id, shop_id, facility_id from multi_goods_shipment where platform_order_sn = '{$encrypt_item['platform_order_sn']}'");
            if(empty($encrypt_item['mobile_encrypt'])
            || empty($encrypt_item['mobile_search_text'])
            || empty($encrypt_item['mobile_decrypt_mask'])
            || empty($encrypt_item['receive_name_decrypt_mask'])
            || empty($encrypt_item['shipping_address_decrypt_mask'])
            || empty($encrypt_item['receive_name_encrypt'])
            || empty($encrypt_item['receive_name_search_text'])
            || empty($encrypt_item['shipping_address_encrypt'])
            || empty($encrypt_item['shipping_address_search_text'])){
                echo " shop_id : ".$shop_id." shipment_id : ".$shipment['shipment_id']." 该订单有数据已加密 或 加密失败".PHP_EOL;
                echo date("Y-m-d H:i:s")." request ".json_encode($param)."  response : ".json_encode($response).PHP_EOL;
                continue;
            }

            $insert_sql = "insert into 
                    multi_goods_shipment_extension(shipment_id, encrypt_shop_id, facility_id, mobile_encrypt, mobile_search_text, receive_name_encrypt, receive_name_search_text, shipping_address_encrypt, shipping_address_search_text)
                    value({$shipment['shipment_id']}, {$shipment['shop_id']}, {$shipment['facility_id']}, '{$encrypt_item['mobile_encrypt']}', '{$encrypt_item['mobile_search_text']}', '{$encrypt_item['receive_name_encrypt']}', 
                    '{$encrypt_item['receive_name_search_text']}', '{$encrypt_item['shipping_address_encrypt']}', '{$encrypt_item['shipping_address_search_text']}')";

                $encrypt_item['shipping_address_decrypt_mask'] = addslashes($encrypt_item['shipping_address_decrypt_mask']);
            $encrypt_item['receive_name_decrypt_mask'] = addslashes($encrypt_item['receive_name_decrypt_mask']);
            $encrypt_item['mobile_decrypt_mask'] = addslashes($encrypt_item['mobile_decrypt_mask']);
            $update_sql = "update multi_goods_shipment 
                set address_id = '{$encrypt_item['address_id']}', 
                shipping_address = '{$encrypt_item['shipping_address_decrypt_mask']}', 
                receive_name = '{$encrypt_item['receive_name_decrypt_mask']}',
                mobile = '{$encrypt_item['mobile_decrypt_mask']}' 
                where shipment_id = {$shipment['shipment_id']}";
            $erp_ddyun_db->query($update_sql);
            $erp_ddyun_db->query($insert_sql);
        }

        echo date("Y-m-d H:i:s") .  "  shop_id : ".$shop_id. " cost " . ((microtime(true) - $beginsss) * 1000) . " ms\n";
        echo "  shop_id : ".$shop_id." has encrypted  max shipment_id". $max_shipment_id.PHP_EOL;
    }
    echo "  shop_id : ".$shop_id." encrypt finish ".PHP_EOL;
}