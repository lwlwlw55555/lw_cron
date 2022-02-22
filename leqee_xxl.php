<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
// https://databasehub.leqee.com/api/QuickQueryController/permittedDatabases
// https://databasehub.guanyc.cn/api/QuickQueryController/syncExecute

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

$sql = "select g.*, max(handle_time) handle_time
from `xxl-job`.xxl_job_qrtz_trigger_group g 
left join (select l.job_group,max(handle_time) handle_time 
from `xxl-job`.xxl_job_qrtz_trigger_log l 
where l.trigger_time > date_sub(now(),interval 1 hour)
  and l.trigger_code = 200
  and l.handle_code = 200
  group by l.job_group) t on t.job_group = g.id
group by g.id";


$dbs = getDatabaseList();
// var_dump($dbs);

$result = [];
foreach ($dbs as $key => $value) {
    if (strpos($key, 'xxl') || strpos($key, 'XXL')) {
        // $pre = '------'.$key.'------';
        // echo $pre.PHP_EOL;
        $url = $value['db']=='leqee'?$url_leqee:$url_gyc;
        $token = $value['db']=='leqee'?$token_leqee:$token_gyc;
        // var_dump($token);
        $res = postJsonData($url.'/syncExecute',['database_id'=>$value['databaseId'],'token'=>$token,'sql'=>$sql]);
        // var_export($res);
        $result[$key] = $res['data']['data'];
        echo PHP_EOL.PHP_EOL;
    }
}

$res = getXxlContent($result);
var_export($res);


// var_export($result);
// $erp_report_send_to = array("wliu11@leqee.com");
// $erp_report_send_to = array("wliu11@leqee.com","fjin@leqee.com");
// send_xxl_email($result, 1, $erp_report_send_to);

function getDatabaseList(){
    global $url_leqee,$url_gyc,$token_leqee,$token_gyc;
    $dbs_leqee = postJsonData($url_leqee.'/permittedDatabases',['token'=>$token_leqee]);
    $dbs_gyc = postJsonData($url_gyc.'/permittedDatabases',['token'=>$token_gyc]);
    return array_merge(getSpecValByCol($dbs_leqee['data']['list'],'databaseName','databaseId','leqee'),getSpecValByCol($dbs_gyc['data']['list'],'databaseName','databaseId','gyc'));
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
