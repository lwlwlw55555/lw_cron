<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
use Models\OrderModel;
use Exception;
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

global $oms_user_db;
$oms_user_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomsuser"
);
$oms_user = ClsPdo::getInstance($oms_user_db_conf);

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
$cc = 0;
$start = 0;
while($is_begin || $cc == 10000){
    $is_begin = false;
    // $sql = "select oi.order_id from order_info oi
    //     inner join order_extension oe on oi.order_id = oe.order_id
    //  where oi.created_time > '2021-07-01' and order_goods_number_format <> ''
    //   limit {$start},2000";
    $sql = "select order_id from order_info
     where created_time > '2021-09-13'
      limit {$start},10000";
     echo date("Y-m-d H:i:s")." sql".$sql;
    $list = $oms_db->getCol($sql);
    $cc = count($list);
    $start += $cc; 

    $sql = "select * from order_goods where order_id in (".implode(",",$list).")";
    $goods_list = $oms_db->getAll($sql);
    $goods_mapping = refreshArraytoMapping($goods_list,'order_id');
    
    foreach ($goods_mapping as $order_id => $value) {
        if (!empty($oms_db->getOne("select order_goods_number_format from order_extension where order_id  = {$order_id}"))) {
            continue;
        }

        $sku_mapping = refreshArraytoMapping($value,'sku_id');
    
        $format = [];        
        foreach ($sku_mapping as $sku_id => $skus) {
            $count =0;
            $is_contune = true;
            foreach ($skus as $sku) {
                if (!empty($sku['user_gift_config_gift_detail_id'])) {
                    $is_contune = false;
                    break;
                }
                $count+=$sku['goods_number'];
            }
            if ($is_contune) {
                $format[] = $sku_id.'#'.$count;
            }
        }
        
        foreach ($sku_mapping as $sku_id => $skus) {
            $count =0;
            $is_contune = true;
            foreach ($skus as $sku) {
                if (empty($sku['user_gift_config_gift_detail_id'])) {
                    $is_contune = false;
                    break;
                }
                $count+=$sku['goods_number'];
            }
            if ($is_contune) {
                $format[] = $sku_id.'#'.$count;
            }
        }
        if (!empty($format)) {
            $format_str = implode("$", $format);
        }
        if (!empty($format_str)) {
            $sql = "update order_extension set order_goods_number_format = '{$format_str}',last_updated_time = last_updated_time where order_id = {$order_id}";
            echo $sql.PHP_EOL;
            try{
               $oms_db->query($sql);
            }catch(Exception $e){

            }
        }
    }
}

 function refreshArraytoMapping($arr,$column){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[$row[$column]][] = $row;
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