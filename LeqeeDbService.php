<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16-9-19
 * Time: 上午11:29
 */
namespace Services;

class LeqeeDbService
{
    public static function downloadOrder($shop_id) {
        global $master_config;
        $url = $master_config['expressapi_url'].'order/downloadOrder/';
        $data = array(
            "shop_id" => $shop_id
        );
        return LeqeeDbService::postJsonData($url, json_encode($data));
    }

    public static function refreshLeqeeToken($leqee_token,$gyc_token) {
        global $db;
        $sql = "update do_com.common_config set config_value ="'{"leqee":"{$leqee_token}","gyc":"{$gyc_token}"}'" where config_key = 'TOKEN_MAPPING'";
        $db->query($sql);
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
            $str = "[LeqeeDbService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
        }else{
            $str = "[LeqeeDbService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
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
            $str = "[]"."[LeqeeDbService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
        }else{
            $str = "[]"."[LeqeeDbService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
        return  $result;
    }
}