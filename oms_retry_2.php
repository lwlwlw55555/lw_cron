<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
include 'request/LogisticsOnlineSendRequest.php';
use Models\ShopModel;
use Models\OrderModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
include 'request/OrderNumberListGetRequest.php';
include 'PddClient.php';

global $oms_db;
$oms_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_0"
);
$oms_db = ClsPdo::getInstance($oms_db_conf);

global $oms_oms_user_db;
$oms_oms_user_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomsuser"
);
$oms_user_db = ClsPdo::getInstance($oms_oms_user_db_conf);

global $oms_sync_db;
$oms_sync_db_conf = array(
    "host" => "100.65.2.110:32057",
    "user" => "mddomsapi_sync",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomssync"
);
$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);

$is_begin = true;
$count = 0;
$start = 0;
while($is_begin || $count == 2000){
    $is_begin = false;
    $sql = "select o.id,oo.origin_order_id from origin_transform_error_order o
inner join shop s on o.shop_id = s.shop_id
inner join mddoms_0.origin_order oo on oo.platform_order_sn = o.platform_order_sn
where db = 0 and s.is_sync = 1 and s.enabled = 1
and  oo.confirm_time < s.last_oauth_time limit 2000";
    $orders = $oms_user_db->getAll($sql);
    if (!empty($orders)) {
        $ooids = getAllValByCol($orders,'origin_order_id');
        $id_mapping = getSpecValByCol($orders,'origin_order_id','id');
        var_dump($ooids);

        $sql ="select distinct oog.origin_order_id from mddoms_0.origin_order_goods oog where oog.origin_order_id 
in (".implode(",", getAllValByCol($orders,'origin_order_id')).")                                                    
  and platform_order_status = 'WAIT_SELLER_SEND_GOODS' and refund_status <> 'RETURNED'";
        $oo_ids = $oms_user_db->getCol($sql);
        var_dump($oo_ids);die;
        $diff = array_diff($ooids, $oo_ids);
        $ids = [];
        foreach ($id_mapping as $key => $value) {
            if (in_array($key, $diff)) {
                $ids[] = $value;            
            }
        }
        if (!empty($ids)) {
            // var_dump($ids);
             $sql = "replace into mddomsuser.origin_transform_error_order_back (id,platform_order_sn,party_id,shop_id,db,transform_type,transform_level)
    select id,platform_order_sn,party_id,shop_id,db,transform_type,transform_level
    from origin_transform_error_order where id in (".implode(",", $ids).")";
            echo $sql.PHP_EOL;
            // $oms_user_db->query($sql);
            $sql = "delete from origin_transform_error_order where id in (".implode(",", $ids).")";
            echo $sql.PHP_EOL;
            // $oms_user_db->query($sql);
        }
        die;
    }
    $count = count($orders);
}

function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data))
    );
    $time_start = microtime(true);

    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    
    $result = json_decode($return_content, true);
    if(isset($result['code']) && $result['code'] == 0) {
        $str = "[ExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
    }else{
        $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
    }
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}

function refreshArraytoMapping($arr,$column){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[$row[$column]] = $row;
                }
            }
            return $res;
        }
        return [];
    }



function getSpecValByCol($arr,$column,$column1){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[$row[$column]] = $row[$column1];
                }
            }
            return $res;
        }
        return [];
    }


function getAllValByCol($arr,$column){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[] = $row[$column];
                }
            }
            return $res;
        }
        return [];
    }