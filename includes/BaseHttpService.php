<?php
class BaseHttpService
{
    
    /**
     * @param $url
     * @param $data
     * @return string
     */
    public static function getJsonData($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $return_content = curl_exec($ch);
        $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($http_status_code == 200){
            $result = json_decode($return_content, true);
            return ['code'=>0,'data'=>$result];
        }else{
            $result = json_decode($return_content, true);
            $error_info = 'http code :'.$http_status_code;
            if(isset($result['error'])){
                $error_info .= ','.$result['error'];
            }
            if(isset($result['error_description'])){
                $error_info .= ','.$result['error_description'];
            }
            return ['code'=>ErrorConstant::CURL_ERROR,'msg'=>$error_info];
        }
    }

    /** 
     * CURL下载文件 成功返回文件名，失败返回false 
     * @param $url 
     * @param string $savePath 
     * @return bool|string 
     */  
    public static function downFile($url, $savePath = './uploads',$filename)  
    {   
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
      
        curl_setopt($ch, CURLOPT_HEADER, TRUE);    //需要response header  
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);    //需要response body  
        
        $response = curl_exec($ch);  
      
        //分离header与body  
        $header = '';  
        $body = '';  
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {  
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE); //头信息size  
            $header = substr($response, 0, $headerSize);  
            $body = substr($response, $headerSize);  
        }  
        curl_close($ch);  
      
        //文件名  
        $arr = array();   
        $fullName = rtrim($savePath, '/') . '/' . $file;  
        $re = file_put_contents("./".$file, $body);
        //创建目录并设置权限    
  
        if (file_put_contents($fullName, $body)) {  
            return $file;  
        }   
      
        return false;  
    }

    public  static function downloadFile($server_url, $local_url){
        $fp_output = fopen($local_url, 'w');
        $ch = curl_init($server_url);
        curl_setopt($ch, CURLOPT_FILE, $fp_output);
        curl_exec($ch);
        curl_close($ch);
    }
}