<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

$token_leqee = "6502b6ab3ff6297121d02efbcf0c851062143be28e17d";
$token_gyc = "38c846cc6369ab8cb33baebed2f6c3a862143da9e6bf2";

if (!empty($argv[1])) {
    $token_leqee = $argv[1];   
}

if (!empty($argv[2])) {
    $token_gyc =  $argv[2];   
}

global $db;

$token_mapping = $db->getOne("select config_value from common_config where config_key = 'TOKEN_MAPPING'");
if (!empty($token_mapping)) {
    $tokens = json_decode($token_mapping,true);
    if (!empty($tokens['leqee'])) {
        $token_leqee = $tokens['leqee'];
    }
    if (!empty($tokens['gyc'])) {
        $token_gyc = $tokens['gyc'];
    }
}

$url_leqee = 'https://databasehub.leqee.com/api/QuickQueryController';
$url_gyc = 'https://databasehub.guanyc.cn/api/QuickQueryController';

global $url_leqee,$url_gyc,$token_leqee,$token_gyc;

require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo date("Y-m-d H:i:s").PHP_EOL;

$dbs = getDatabaseList();
// var_export($dbs);die;
foreach ($dbs as $key => $v) {
    foreach ($v as $value) {

        $sql = "replace into leqee_tables (database_id, db,database_name, engine, host, memo, port, status) values ('{$value['databaseId']}','{$key}', '{$value['databaseName']}', '{$value['engine']}', '{$value['host']}', '{$value['memo']}', '{$value['port']}', '{$value['status']}')";
        $db->query($sql);
    }
}


function getDatabaseList(){
    global $url_leqee,$url_gyc,$token_leqee,$token_gyc;
    $dbs_leqee = postJsonData($url_leqee.'/permittedDatabases',['token'=>$token_leqee]);
    $dbs_gyc = postJsonData($url_gyc.'/permittedDatabases',['token'=>$token_gyc]);
    return array_merge(getSpecValByLw($dbs_leqee['data']['list'],'leqee'),getSpecValByLw($dbs_gyc['data']['list'],'gyc'));
}


function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen(json_encode($data)))
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
    // echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
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


// function getSpecValByCol($arr,$column,$column1){
//     $res = [];
//     if (is_array($arr) && !empty($arr)) {
//         foreach ($arr as $row) {
//             if (isset($row[$column])) {
//                 $res[$row[$column]] = $row[$column1];
//             }
//         }
//         return $res;
//     }
//     return [];
// }


function getSpecValByCol($arr,$column,$column1,$db){
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

function getSpecValByLw($arr,$db){
    return [$db=>$arr];
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

