<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16-9-19
 * Time: 上午11:29
 */
namespace Services;

class OMSExpressApiService
{
    /**
     * @param $template
     * @return mixed
     * @throws \Exception
     */
    public static function downloadOrder($shop_id) {
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/downloadOrder/';
        $data = array(
            "shop_id" => $shop_id
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data));
    }
    public static function syncShopWithTimestamp($shop_id,$start_time,$end_time,$is_record_mongo='0',$order_type=''){
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/syncShopWithTimestamp';
        $start_time_second = strtotime($start_time);
        // if(time() - $start_time_second < 15*60){
        //     $start_time_second = time() - 15 * 60;//和拼多多服务器时间差
        // }
        $data = array(
            "shop_id"     => $shop_id,
            "start_time"  => date("Y-m-d H:i:s", $start_time_second),
            "end_time"    => $end_time,
            "is_record_mongo" => 0,
            "platform_order_status" => $order_type
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function syncTaobaoPlatformOrderWithTimestamp($start_time,$end_time){
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/syncTaobaoWithTimestamp';
        $start_time_second = strtotime($start_time);
        $data = array(
            "start_time"  => $start_time,
            "end_time"    => $end_time
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function syncTaobaoPlatformGoodsWithTimestamp($start_time,$end_time){
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'good/downloadPlatFormTaobao';
        $start_time_second = strtotime($start_time);
        $data = array(
            "start_time"  => $start_time,
            "end_time"    => $end_time
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function syncNewTaobaoShopByApi($start_time,$end_time,$shop_id){
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/syncNewTaobaoShopByApi';
        $start_time_second = strtotime($start_time);
        $data = array(
            "start_time"  => $start_time,
            "end_time"    => $end_time,
            "shop_id"     => $shop_id
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function downloadGoods($shop_id,$page=null) {
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'good/download/';
        $data = array(
            "shop_id" => $shop_id
        );
        if (!empty($page)) {
            $data['page'] = strval($page);
        }
        return OMSExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function downloadGoodsNotSkus($shop_id,$page=null) {
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'good/download/';
        $data = array(
            "shop_id" => $shop_id,
            "is_contain_sku" => '0'
        );
        if (!empty($page)) {
            $data['page'] = strval($page);
        }
        return OMSExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function downloadSingleOrder($shop_id,$platform_name,$order_sn) {
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/downloadSingleOrder/';
        $data = array(
            "platform_order_sn" => $order_sn,
            "platform_name"     => $platform_name,
            "platform_shop_id"  => $shop_id,
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function downloadTaobaoSingleOrderByApi($shop_id,$order_sn) {
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/syncSingleOrderByJstApi/';
        $data = array(
            "order_sn" => $order_sn,
            "shop_id"  => $shop_id,
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function getPlatformStatus($platform_shop_id,$platform_name,$order_sns){
        global $master_config;
        $url = 'http://100.65.130.195:11353/'.'order/getPlatformOrderStatus/';
        $data = array(
            "platform_shop_id"  => $platform_shop_id,
            "platform_name"     => $platform_name,
            "order_sns"         => $order_sns,
        );
        return OMSExpressApiService::postJsonData($url, json_encode($data));
    }

    /**
     * @param $url
     * @param $data
     * @return string
     */
    public static function postJsonDataWithTimeOut($url, $data,$timeout) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
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
            $str = "[OMSExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
        }else{
            $str = "[OMSExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
        return  $result;
    }

    /**
     * @param $url
     * @param $data
     * @return string
     */
    public static function postJsonData($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
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
            $str = "[]"."[OMSExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
        }else{
            $str = "[]"."[OMSExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
        return  $result;
    }
}