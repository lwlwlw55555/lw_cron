<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16-9-19
 * Time: 上午11:29
 */
namespace Services;

class ExpressApiService
{
    /**
     * @param $template
     * @return mixed
     * @throws \Exception
     */
    public static function downloadOrder($shop_id) {
        global $master_config;
        $url = $master_config['expressapi_url'].'order/downloadOrder/';
        $data = array(
            "shop_id" => $shop_id
        );
        return ExpressApiService::postJsonData($url, json_encode($data));
    }
    public static function syncShopWithTimestamp($shop_id,$start_time,$end_time,$is_record_mongo='0',$order_type=''){
        global $master_config;
        $url = $master_config['expressapi_url'].'order/syncShopWithTimestamp';
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
        return ExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function syncTaobaoPlatformOrderWithTimestamp($start_time,$end_time){
        global $master_config;
        $url = $master_config['expressapi_url'].'order/syncTaobaoWithTimestamp';
        $start_time_second = strtotime($start_time);
        $data = array(
            "start_time"  => $start_time,
            "end_time"    => $end_time
        );
        return ExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function syncTaobaoPlatformGoodsWithTimestamp($start_time,$end_time){
        global $master_config;
        $url = $master_config['expressapi_url'].'good/downloadPlatFormTaobao';
        $start_time_second = strtotime($start_time);
        $data = array(
            "start_time"  => $start_time,
            "end_time"    => $end_time
        );
        return ExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function syncNewTaobaoShopByApi($start_time,$end_time,$shop_id){
        global $master_config;
        $url = $master_config['expressapi_url'].'order/syncNewTaobaoShopByApi';
        $start_time_second = strtotime($start_time);
        $data = array(
            "start_time"  => $start_time,
            "end_time"    => $end_time,
            "shop_id"     => $shop_id
        );
        return ExpressApiService::postJsonData($url, json_encode($data),0);
    }

    public static function downloadGoods($shop_id,$page=null) {
        global $master_config;
        $url = $master_config['expressapi_url'].'good/download/';
        $data = array(
            "shop_id" => $shop_id
        );
        if (!empty($page)) {
            $data['page'] = strval($page);
        }
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function downloadGoodsNotSkus($shop_id,$page=null) {
        global $master_config;
        $url = $master_config['expressapi_url'].'good/download/';
        $data = array(
            "shop_id" => $shop_id,
            "is_contain_sku" => '0'
        );
        if (!empty($page)) {
            $data['page'] = strval($page);
        }
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function downloadSingleOrder($shop_id,$platform_name,$order_sn) {
        global $master_config;
        $url = $master_config['expressapi_url'].'order/downloadSingleOrder/';
        $data = array(
            "platform_order_sn" => $order_sn,
            "platform_name"     => $platform_name,
            "platform_shop_id"  => $shop_id,
        );
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function downloadTaobaoSingleOrderByApi($shop_id,$order_sn) {
        global $master_config;
        $url = $master_config['expressapi_url'].'order/syncSingleOrderByJstApi/';
        $data = array(
            "order_sn" => $order_sn,
            "shop_id"  => $shop_id,
        );
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function getPlatformStatus($platform_shop_id,$platform_name,$order_sns){
        global $master_config;
        $url = $master_config['expressapi_url'].'order/getPlatformOrderStatus/';
        $data = array(
            "platform_shop_id"  => $platform_shop_id,
            "platform_name"     => $platform_name,
            "order_sns"         => $order_sns,
        );
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function resendQueueData($data){
        global $master_config;
        $url = $master_config['route_service_url'].'syncMailnos/moveResendToQueue';
        return ExpressApiService::postJsonData($url, json_encode($data),600);
    }

    public static function saveFqueueailedData($data){
        global $master_config;
        $url = $master_config['route_service_url'].'syncMailnos/failedResend';
        return ExpressApiService::postJsonData($url, json_encode($data),600);
    }

    public static function sendRoutePreByDataSource($data){
        global $master_config;
        $url = $master_config['route_service_url'].'syncMailnos/syncErpOrder';
        return ExpressApiService::postJsonDataWithTimeOut($url, json_encode($data),600);
    }

    public static function sendRouteByDataSource($data){
        global $master_config;
        $url = $master_config['route_service_url'].'syncMailnos/syncSyncOrder';
        return ExpressApiService::postJsonDataWithTimeOut($url, json_encode($data),600);
    }

    public static function sendRouteByPlatformName($data){
        global $master_config;
        $url = $master_config['route_service_url'].'syncMailnos/sendOrder';
        return ExpressApiService::postJsonDataWithTimeOut($url, json_encode($data),600);
    }

    public static function sendPinduoduoPrintLog($data)
    {
        global $master_config;
        $url = $master_config['route_service_url'] . 'printLog/pushPinduoduoPrintLog';
        return ExpressApiService::postJsonDataWithTimeOut($url, json_encode($data),600);
    }

    public static function refreshRouteDatasource($data){
        global $master_config;
        $url = $master_config['route_service_url'].'route/refreshLogistic';
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function getEncryptAndDecryptBatch($data){
        global $master_config;
        $url = $master_config['erp_api_url'].'shipment/getEncryptAndDecryptBatch';
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function encryptHistoryData($data){
        global $master_config;
        $url = $master_config['erp_api_url'].'shipment/encryptHistoryData';
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function encryptErpShipment($data){
        global $master_config;
        $url = $master_config['erp_api_url'].'shipment/encryptErpShipment';
        return ExpressApiService::postJsonData($url, json_encode($data));
    }

    public static function refreshOauthShop( $platform_shop_id, $platform_name, $expire_time, $access_token, $app_key, $refresh_token, $refresh_expire_time ) {
        global $master_config;
        $url = $master_config['mdd_oms_api_url']."shop/refresh/oauth/shop";
        $data = [
            'platformShopId'        => $platform_shop_id,
            'platformName'          => $platform_name,
            'expireTime'            => $expire_time,
            'accessToken'           => $access_token,
            'appKey'                => $app_key,
            'refreshToken'          => $refresh_token,
            'refreshExpireTime'     => $refresh_expire_time
        ];
        return ExpressApiService::postJsonData( $url, json_encode($data) );
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
            $str = "[ExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
        }else{
            $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
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
            $str = "[]"."[ExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
        }else{
            $str = "[]"."[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
        return  $result;
    }
}