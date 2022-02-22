<?php
/*1、如果进程总数超过8000，就sms报警
2、进程超8000的把Sleep 超过120s的 kill掉。
3、如果执行中的sql超过20个就短信报警，不kill，把process输出到日志
4、如果执行中的sql超过5个执行10秒 就短信报警，不kill，记录process志
5、如果执行中的sql超过20个并且执行20秒，就短信报警 并且kill，记录process日志

PS1、注意要分用户去kill，erp、erpapi、erpsyncinner 3个用户
PS2、这个事情王俊做，其他人也知道一下，7点到21点每分钟cron定时跑*/
require("includes/init.php");
$db_conf = array(
    "host" => "100.65.1.244:32052",
    "user" => "erp_sync",
    "pass" => "Titanerpsync2020",
    "charset" => "utf8",
    "pconnect" => "1",
    "name"  =>'erpsync'
);
$sql = "
      show full processlist
    ";
$db = ClsPdo::getInstance($db_conf);
$sql_list = $db->getAll($sql);
$sum_process = count($sql_list);
$not_sleep_process =  [];
$slow1_process =  [];
$slow2_process =  [];
foreach ($sql_list as $item) {
    if($item['Command'] != 'Sleep' ) {
        $not_sleep_process[] = $item;
        if($item['Time'] > 10) {
            $slow1_process[] = $item;
            if($item['Time'] > 20) {
                $slow2_process[] = $item;
            }
        }
        
    }
}
$mgs = '';
if($sum_process >= 8000){
    $mgs .= "ERP_SYNC报警 check_process 生产库总进程数{$sum_process}。";
    foreach ($sql_list as $ss) {
        if ($ss['Command'] == 'Sleep' && $ss['Time'] >120) {
            $db_conf['name'] =  $ss['db'];
            $db_conf['user'] = $ss['User'];
            try{
                $db = ClsPdo::getInstance($db_conf);
                $db->query("kill {$ss['Id']}");
                echo ("kill {$ss['Id']}\n");
            }catch (Exception $e){
                continue;
            }
        }
    }
    
}
if(count($not_sleep_process) > 20){
    $count_not_sleep_process = count($not_sleep_process);
    $mgs .= "ERP_SYNC报警 check_process 执行中的sql超过有{$count_not_sleep_process}个。";
    echo  "[]".date("Y-m-d H:i:s").'check_process'.json_encode($not_sleep_process);
}
if(count($slow1_process) > 5){
    $count_slow1_process = count($slow1_process);
    $mgs .= "ERP_SYNC报警 check_process 执行中的sql超过有{$count_slow1_process}个执行10秒。";
    echo  "[]".date("Y-m-d H:i:s").'check_process'.json_encode($slow1_process);
}
if(count($slow2_process) > 20){
    $count_slow2_process =  count($slow2_process);
    $mgs .= "ERP_SYNC报警 check_process 执行中的sql有{$count_slow2_process}个并且执行20秒。";
    foreach ($slow2_process as $ss) {                                          
        $db_conf['name'] =  $ss['db'];  
        $db_conf['user'] = $ss['User'];       
        try{
            $db = ClsPdo::getInstance($db_conf);
            $db->query("kill {$ss['Id']}");
            echo ("kill {$ss['Id']}\n");                  
        }catch (Exception $e){
            continue;
        }
    }
    echo "[]".date("Y-m-d H:i:s").'check_process'.json_encode($slow2_process);
}
if($mgs != ''){
    send_sms($mgs,'13567177855');
}else{
    echo  "[]"."ERP_SYNC正常 check_process";
}
function send_sms($msg, $mobile = '13567177855') {
    global $sync_db;
    $sql = "INSERT into send_msg (receiver_mobiles, msg) VALUES ('{$mobile}','{$msg}');";
    $sync_db->query($sql);
    echo "[] " . date("Y-m-d H:i:s")." send_msg sql:{$sql}".PHP_EOL;
}