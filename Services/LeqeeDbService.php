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
   public static function refreshLeqeeToken($leqee_token,$gyc_token) {
        global $db;
        // {"leqee":"{$leqee_token}","gyc":"{$gyc_token}"}
        $value = json_encode(['leqee'=>$leqee_token,'gyc'=>$gyc_token]);
        $sql = "update do_com.common_config set config_value ='{$value}' where config_key = 'TOKEN_MAPPING'";
        $db->query($sql);
    }

    public static function getLeqeeToken() {
        global $db;
        $config_value = $db->getOne("select config_value from common_config where config_key = 'TOKEN_MAPPING'");
        return json_decode($config_value,true);
    }

    public static function getSystemToken($system){
        $tokens = LeqeeDbService::getLeqeeToken();
        return $tokens[$system];
    }

    public static function getLeqeeDbs(){
        global $db;
        $sql_db = "select db,database_id as databaseId,database_name as databaseName from leqee_tables";
        $dbs = LeqeeDbService::refreshArraytoMapping($db->getAll($sql_db),'databaseName');
        // var_dump($dbs);
        if (empty($dbs)) {
            $dbs = LeqeeDbService::getDatabaseList();
        }
        return $dbs;
    }

    public static function getDatabaseList(){
        $tokens = LeqeeDbService::getLeqeeToken();
        $token_leqee = $tokens['leqee'];
        $token_gyc= $tokens['gyc'];
        global $url_leqee,$url_gyc;
        $dbs_leqee = LeqeeDbService::postJsonData($url_leqee.'/permittedDatabases',['token'=>$token_leqee]);
        $dbs_gyc = LeqeeDbService::postJsonData($url_gyc.'/permittedDatabases',['token'=>$token_gyc]);
        return array_merge(getSpecValByColDb($dbs_leqee['data']['list'],'databaseName','databaseId','leqee'),getSpecValByColDb($dbs_gyc['data']['list'],'databaseName','databaseId','gyc'));
    }

    public static function query($db,$sql){
        global $url_leqee,$url_gyc;
        $url = $url_leqee;
        if ($db['db'] == 'gyc') {
            $url = $url_gyc;
        }
        // echo $sql.PHP_EOL;die;
        // var_dump($db);die;
        $params = ['database_id'=>strval($db['databaseId']),'token'=>LeqeeDbService::getSystemToken($db['db']),'sql'=>$sql];
        echo $sql.PHP_EOL;
        // var_dump($params);die;

        $res = LeqeeDbService::postJsonData($url.'/syncExecute',json_encode($params));
        // var_export($res);die;
        return $res['data']['data'];
    }

    // array(1) {
    //   [0]=>
    //   array(2) {
    //     ["code"]=>
    //     string(2) "OK"
    //     ["data"]=>
    //     array(5) {
    //       ["done"]=>
    //       bool(true)
    //       ["data"]=>
    //       array(1) {
    //         [0]=>
    //         array(1) {
    //           ["shop_name"]=>
    //           string(28) "乐其数码专营店-天猫"
    //         }
    //       }
    //       ["error"]=>
    //       array(0) {
    //       }
    //       ["query_time"]=>
    //       float(0.0026340484619140625)
    //       ["total_time"]=>
    //       float(0.008301019668579102)
    //     }
    //   }
    // }

    public static function getStropsDbs($strpos){
        $need_dbs = [];
        $dbs = LeqeeDbService::getLeqeeDbs();
        foreach ($dbs as $key => $db) {
            if (strpos($key, $strpos) || strpos($key, strtoupper($strpos))) {
                $need_dbs[$key] = $db;
            }
        }
        return $need_dbs;
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
        // echo(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
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
        // echo(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
        return  $result;
    }

    public static function refreshArraytoMapping($arr,$column){
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


    public static function getSpecValByCol($arr,$column,$column1){
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


    public static function getSpecValByColDb($arr,$column,$column1,$db){
        $res = [];
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                if (isset($row[$column])) {
                    $res[$row[$column]] = [$column1=>$row[$column1],'db'=>$db];
                }
            }
            return $res;
        }
        return [];
    }

    public static function getAllValByCol($arr,$column){
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

    public static function checkNull($temp){
        if(!is_null($temp)){
            $temp = addslashes($temp);
            $temp = "'{$temp}'";
        }else{
            $temp = 'null';
        }
        return $temp;
    }
}