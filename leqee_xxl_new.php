<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
// https://databasehub.leqee.com/api/QuickQueryController/permittedDatabases
// https://databasehub.guanyc.cn/api/QuickQueryController/syncExecute

$token_leqee = "6502b6ab3ff6297121d02efbcf0c851062143be28e17d";
$token_gyc = "38c846cc6369ab8cb33baebed2f6c3a862143da9e6bf2";



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

$sql = "select g.*, max(handle_time) handle_time
from `xxl-job`.xxl_job_qrtz_trigger_group g 
left join (select l.job_group,max(handle_time) handle_time 
from `xxl-job`.xxl_job_qrtz_trigger_log l 
where l.trigger_time > date_sub(now(),interval 1 hour)
  and l.trigger_code = 200
  and l.handle_code = 200
  group by l.job_group) t on t.job_group = g.id
group by g.id";


$sql_db = "select db,database_id as databaseId,database_name as databaseName from leqee_tables where database_name like '%xxl%'
union
select db,database_id as databaseId,database_name as databaseName from leqee_tables where database_name like '%XXL%';";
$dbs = refreshArraytoMapping($db->getAll($sql_db),'databaseName');
// var_export($dbs);

if (empty($dbs)) {
    $dbs = getDatabaseList();
}

$db_type = '';
$dbs = ['leqee'=>['oms-v2-xxl-job', 'oms-kx-xxl-prod', 'oms-mz-xxl-prod', 'oms-mz2-xxl-prod'],'gyc'=>['gyc-oms-xxl-job', 'PerfectDiary-Yiran-XXL-Job', 'ruyun-oms-xxl-job']];
if (!empty($_REQUEST) && array_key_exists('db_type', $_REQUEST) && in_array($_REQUEST['db_type'], ['leqee','gyc'])) {
    $db_type = $_REQUEST['db_type'];
}

$result = [];
foreach ($dbs as $key => $value) {
    if (strpos($key, 'xxl') || strpos($key, 'XXL')) {
        // $pre = '------'.$key.'------';
        // echo $pre.PHP_EOL;
        if (!empty($db_type)) {
            if (!in_array($key, $dbs[$db_type])) {
                continue;
            }
        }
        $url = $value['db']=='leqee'?$url_leqee:$url_gyc;
        $token = $value['db']=='leqee'?$token_leqee:$token_gyc;
        // var_dump($value);
        $params = ['database_id'=>strval($value['databaseId']),'token'=>$token,'sql'=>$sql];
        // var_dump($params);
        $res = postJsonData($url.'/syncExecute',$params);
        // var_export($res);
        $result[$key] = $res['data']['data'];
        echo PHP_EOL.PHP_EOL;
    }
}

echo json_encode(['code'=>0,'data'=>$result]);
return;

// $res = getXxlContent($result);
// echo $res;
// // var_export($res);


// var_export($result);
// $erp_report_send_to = array("wliu11@leqee.com");
// $erp_report_send_to = array("wliu11@leqee.com","fjin@leqee.com");
// send_xxl_email($result, 1, $erp_report_send_to);

function getDatabaseList(){
    global $url_leqee,$url_gyc,$token_leqee,$token_gyc;
    $dbs_leqee = postJsonData($url_leqee.'/permittedDatabases',['token'=>$token_leqee]);
    $dbs_gyc = postJsonData($url_gyc.'/permittedDatabases',['token'=>$token_gyc]);
    return array_merge(getSpecValByColDb($dbs_leqee['data']['list'],'databaseName','databaseId','leqee'),getSpecValByColDb($dbs_gyc['data']['list'],'databaseName','databaseId','gyc'));
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


function getSpecValByColDb($arr,$column,$column1,$db){
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


// {"code":0,"data":{"oms-v2-xxl-job":[{"id":"2","app_name":"oms-v2-server","title":"V2Server","order":"2","address_type":"0","address_list":"10.8.21.180:7788","handle_time":"2022-06-28 18:43:32"},{"id":"3","app_name":"oms-v2-sync","title":"V2Sync","order":"1","address_type":"0","address_list":"10.8.21.178:7788","handle_time":"2022-06-28 18:43:32"},{"id":"4","app_name":"oms-v2-wmsclient","title":"V2Wmsc","order":"3","address_type":"0","address_list":"10.8.21.177:7788","handle_time":"2022-06-28 18:43:32"},{"id":"5","app_name":"oms-v2-sync-cluster","title":"V2Sync\u96c6\u7fa4","order":"1","address_type":"0","address_list":"10.8.21.174:7788,10.8.21.175:7788,10.8.21.184:7788","handle_time":"2022-06-28 18:43:32"},{"id":"6","app_name":"oms-v2-server-cluster","title":"V2Server\u96c6\u7fa4","order":"2","address_type":"0","address_list":"10.8.21.181:7788,10.8.22.226:7788,10.8.22.228:7788,10.8.25.252:7788","handle_time":"2022-06-28 18:43:32"},{"id":"7","app_name":"oms-v2-server-push-cluster","title":"v2serverpush","order":"2","address_type":"0","address_list":"10.8.20.142:7788,10.8.20.143:7788,10.8.22.227:7788","handle_time":"2022-06-28 18:43:31"},{"id":"8","app_name":"oms-v2-analyze","title":"V2Analyze","order":"3","address_type":"0","address_list":"10.8.20.141:7788","handle_time":"2022-06-28 18:43:23"},{"id":"9","app_name":"oms-shop","title":"omsshop","order":"4","address_type":"0","address_list":"10.8.23.169:7788","handle_time":null},{"id":"11","app_name":"oms-v2-wmsclient-front","title":"oms-v2-wmscl","order":"1","address_type":"0","address_list":"10.8.23.177:7788,10.8.23.218:7788,10.8.25.20:7788,10.8.25.32:7788,10.8.25.33:7788,10.8.25.34:7788,10.8.25.35:7788,10.8.25.42:7788,10.8.25.50:7788","handle_time":null},{"id":"12","app_name":"finc-server","title":"\u8d22\u52a1\u4e2d\u5fc3","order":"1","address_type":"0","address_list":null,"handle_time":null}],"oms-kx-xxl-prod":[{"id":"2","app_name":"oms-v2-server","title":"V2Server","order":"2","address_type":"0","address_list":"10.8.24.85:7788","handle_time":"2022-06-28 18:43:32"},{"id":"4","app_name":"oms-v2-wmsclient","title":"V2Wmsc","order":"3","address_type":"0","address_list":"10.8.24.78:7788","handle_time":"2022-06-28 18:43:32"},{"id":"6","app_name":"oms-v2-server-cluster","title":"V2Server\u96c6\u7fa4","order":"2","address_type":"0","address_list":"10.8.25.24:7788,10.8.25.25:7788,10.8.25.26:7788,10.8.25.39:7788","handle_time":"2022-06-28 18:43:32"},{"id":"7","app_name":"oms-v2-server-pushcluster","title":"v2serverpush","order":"2","address_type":"0","address_list":"10.8.25.41:7788,10.8.25.46:7788,10.8.25.52:7788","handle_time":"2022-06-28 18:43:32"},{"id":"8","app_name":"oms-v2-analyze","title":"V2Analyze","order":"3","address_type":"0","address_list":"10.8.24.81:7788","handle_time":"2022-06-28 18:43:28"}],"oms-mz-xxl-prod":[{"id":"2","app_name":"oms-v2-server","title":"V2Server","order":"2","address_type":"0","address_list":"10.8.24.84:7788","handle_time":"2022-06-28 18:43:32"},{"id":"4","app_name":"oms-v2-wmsclient","title":"V2Wmsc","order":"3","address_type":"0","address_list":"10.8.24.75:7788","handle_time":"2022-06-28 18:43:31"},{"id":"6","app_name":"oms-v2-server-cluster","title":"V2Server\u96c6\u7fa4","order":"2","address_type":"0","address_list":"10.8.25.27:7788,10.8.25.28:7788,10.8.25.29:7788,10.8.25.48:7788","handle_time":"2022-06-28 18:43:32"},{"id":"7","app_name":"oms-v2-server-pushcluster","title":"v2serverpush","order":"2","address_type":"0","address_list":"10.8.25.19:7788,10.8.25.43:7788,10.8.25.47:7788","handle_time":"2022-06-28 18:43:33"},{"id":"8","app_name":"oms-v2-analyze","title":"V2Analyze","order":"3","address_type":"0","address_list":"10.8.24.80:7788","handle_time":"2022-06-28 18:43:33"}],"oms-mz2-xxl-prod":[{"id":"2","app_name":"oms-v2-server","title":"V2Server","order":"2","address_type":"0","address_list":"10.8.25.10:7788","handle_time":"2022-06-28 18:43:32"},{"id":"4","app_name":"oms-v2-wmsclient","title":"V2Wmsc","order":"3","address_type":"0","address_list":"10.8.25.5:7788","handle_time":"2022-06-28 18:43:31"},{"id":"6","app_name":"oms-v2-server-cluster","title":"V2Server\u96c6\u7fa4","order":"2","address_type":"0","address_list":"10.8.25.21:7788,10.8.25.22:7788,10.8.25.23:7788,10.8.25.45:7788","handle_time":"2022-06-28 18:43:32"},{"id":"7","app_name":"oms-v2-server-pushcluster","title":"v2serverpush","order":"2","address_type":"0","address_list":"10.8.25.36:7788,10.8.25.40:7788,10.8.25.49:7788","handle_time":"2022-06-28 18:43:31"},{"id":"8","app_name":"oms-v2-analyze","title":"V2Analyze","order":"3","address_type":"0","address_list":"10.8.25.4:7788","handle_time":"2022-06-28 18:43:32"}],"gyc-oms-xxl-job":[{"id":"2","app_name":"gyc-oms-sync","title":" \u89c2\u4e91\u957foms sync","order":"1","address_type":"0","address_list":"172.16.159.177:7788","handle_time":"2022-06-28 18:43:33"},{"id":"3","app_name":"gyc-oms-server","title":"\u89c2\u4e91\u957fomsserver","order":"3","address_type":"0","address_list":"172.16.159.183:7788","handle_time":"2022-06-28 18:43:33"},{"id":"4","app_name":"gyc-wms-client","title":"\u89c2\u4e91\u957fwmsclient","order":"4","address_type":"0","address_list":"172.16.159.184:7788","handle_time":"2022-06-28 18:43:32"},{"id":"5","app_name":"gyc-oms-analyze","title":"\u89c2\u4e91\u957fanalyze","order":"5","address_type":"0","address_list":"172.16.159.203:7788","handle_time":"2022-06-28 18:43:33"},{"id":"6","app_name":"oms-customer","title":"oms-customer","order":"1","address_type":"0","address_list":"172.16.159.206:7790","handle_time":"2022-06-28 18:43:32"},{"id":"7","app_name":"gyc-oms-sync-cluster","title":"\u89c2\u4e91\u957fsync\u96c6\u7fa4","order":"6","address_type":"0","address_list":"172.16.174.96:7788,172.16.174.97:7788","handle_time":"2022-06-28 18:43:31"},{"id":"8","app_name":"gyc-oms-server-cluster","title":"\u89c2\u4e91\u957fserver\u96c6\u7fa4","order":"7","address_type":"0","address_list":"172.16.174.101:7788,172.16.174.102:7788,172.16.174.94:7788,172.16.174.95:7788","handle_time":"2022-06-28 18:43:33"}],"PerfectDiary-Yiran-XXL-Job":[{"id":"1","app_name":"wmrjyr-server-cluster","title":"server\u96c6\u7fa4","order":"1","address_type":"0","address_list":"10.110.1.3:7788,10.110.1.7:7788","handle_time":"2022-06-28 18:43:33"},{"id":"2","app_name":"wmrjyr-oms-server","title":"server","order":"2","address_type":"0","address_list":"10.110.1.10:7788","handle_time":"2022-06-28 18:43:32"},{"id":"3","app_name":"wmrjyr-sync","title":"sync","order":"3","address_type":"0","address_list":"10.110.1.5:7788","handle_time":"2022-06-28 18:43:31"},{"id":"4","app_name":"wmrjyr-wms-client","title":"wms-client","order":"4","address_type":"0","address_list":"10.110.1.9:7788","handle_time":"2022-06-28 18:43:31"},{"id":"5","app_name":"wmrjyr-analyze","title":"analyze","order":"5","address_type":"0","address_list":"10.110.1.8:7788","handle_time":"2022-06-28 18:43:32"}],"ruyun-oms-xxl-job":[{"id":"1","app_name":"oms-server","title":"oms-server","order":"1","address_type":"0","address_list":"10.7.12.116:7788","handle_time":"2022-06-28 18:43:34"},{"id":"2","app_name":"oms-server-cluster","title":"server\u96c6\u7fa4","order":"2","address_type":"0","address_list":"10.7.12.108:7788","handle_time":"2022-06-28 18:43:34"},{"id":"3","app_name":"oms-sync","title":"oms-sync","order":"3","address_type":"0","address_list":"10.7.12.114:7788","handle_time":"2022-06-28 18:43:33"},{"id":"4","app_name":"oms-analyze","title":"oms-analyze","order":"4","address_type":"0","address_list":"10.7.12.113:7788","handle_time":"2022-06-28 18:43:34"},{"id":"5","app_name":"wms-client","title":"wms-client","order":"5","address_type":"0","address_list":"10.7.12.109:7788","handle_time":"2022-06-28 18:43:34"},{"id":"6","app_name":"oms-customer","title":"oms-customer","order":"1","address_type":"0","address_list":"10.7.12.111:7790","handle_time":"2022-06-28 18:43:34"}]}}
