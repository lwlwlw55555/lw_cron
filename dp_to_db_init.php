<?php

require("includes/init.php");

$db_conf = array(
    "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
    // "host" => "2.tcp.cpolar.cn:13001",
    "user" => "bi",
    "pass" => "5*8Vnm&uTEF4",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "bi"
);

// $db1_conf = array(
//      "host" => "127.0.0.1:3306",
//     // "host" => "121.40.113.153:3306",
//     "user" => "root",
//     "pass" => "aBc@123456",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     // "name" => "bi"
//     "name" => "bi"
// );


$db = ClsPdo::getInstance($db_conf);


$json = '[
      {
        "task_id": 1311552,
        "shovel_project": "mizar",
        "shovel_class": "mizar-prod",
        "aspect": "小蜜蜂幕后主使B2B",
        "part_key": "shop_id",
        "part_name": "小蜜蜂采集路径入口1-菜单1-文档1-日",
        "data_storage_type": "OSS_FILE",
        "storage": "leqee-private",
        "location": "test-blqian/1000001666_SALONIA旗舰店-抖音_1682043606",
        "time_range": "2021-10-18_2021-10-18",
        "task_status": "FAILED",
        "task_feedback": null,
        "accept_time": 1689671641298,
        "start_time": 1689859721264,
        "finish_time": 1689859721411,
        "target_database_type": "MYSQL",
        "target_database_code": "bi-test-polar",
        "target_schema": "hermon",
        "target_table": "lee_dy_cmm_live_details_daliy",
        "tp_code": "ETL-blqian",
        "mizar_group_id": 1,
        "epitaph": "DONE",
        "feedback": "推送成功",
        "hook_url": "https://test-hook-url-mizar-v-fc-jwrtrodkxn.cn-hangzhou.fcapp.run, https://test-hook-url-mizar-v-fc-jwrtrodkxn.cn-hangzhou.fcapp.run",
        "hold_on": null,
        "record_id": 455,
        "record_status": null,
        "record_feedback": null,
        "record_start_time": null,
        "record_end_time": null,
        "fc_request_id": null,
        "mizar_version": "2.0",
        "target_machine": "mizar-prod"
      },
      {
        "task_id": 13562,
        "shovel_project": "oc-shovel",
        "shovel_class": "EBChatbotConversation",
        "aspect": "OCChatbotConversation",
        "part_key": "None",
        "part_name": "None",
        "data_storage_type": "OSS_FILE",
        "storage": "leqee-private",
        "location": "oc-shovel/EBChatbotConversation/20230717/97700",
        "time_range": "2023-07-17_2023-07-17",
        "task_status": "ERROR",
        "task_feedback": "Handle Mizar Task Failed, see log for details: (1062, \"Duplicate entry \"孙敏-2023-03-27 17:33:50\" for key \"idx_uk\"\")",
        "accept_time": 1689687690950,
        "start_time": 1689866090843,
        "finish_time": 1689866091141,
        "target_database_type": "MYSQL",
        "target_database_code": "bi-test-polar",
        "target_schema": "hermon",
        "target_table": "oc_zst_train_chatbot_conversation_usage",
        "tp_code": "mizar-oc-test",
        "mizar_group_id": 151,
        "epitaph": "DONE",
        "feedback": "推送oss file成功",
        "hook_url": "",
        "hold_on": null,
        "record_id": 33131,
        "record_status": "ERROR",
        "record_feedback": "Handle Mizar Task Failed, see log for details: (1062, \"Duplicate entry \"孙敏-2023-03-27 17:33:50\" for key \"idx_uk\"\")",
        "record_start_time": 1689866090843,
        "record_end_time": 1689866091141,
        "fc_request_id": "1-64b8deea-2b8c749f4c8745a82c45c636",
        "mizar_version": "2.0",
        "target_machine": "mizar-prod"
      },
      {
        "task_id": 13572,
        "shovel_project": "oc-shovel",
        "shovel_class": "EBChatbotConversation",
        "aspect": "OCChatbotConversation",
        "part_key": "None",
        "part_name": "None",
        "data_storage_type": "OSS_FILE",
        "storage": "leqee-private",
        "location": "oc-shovel/EBChatbotConversation/20230717/97701",
        "time_range": "2023-07-17_2023-07-17",
        "task_status": "INIT",
        "task_feedback": "Handle Mizar Task Failed, see log for details: (1062, \"Duplicate entry \"孙敏-2023-03-27 17:33:50\" for key \"idx_uk\"\")",
        "accept_time": 1689688186507,
        "start_time": 1689864603689,
        "finish_time": 1689864603949,
        "target_database_type": "MYSQL",
        "target_database_code": "bi-test-polar",
        "target_schema": "hermon",
        "target_table": "tiberias_lee_dy_cmm_live_details_daliy",
        "tp_code": "mizar-oc-test",
        "mizar_group_id": 152,
        "epitaph": "DONE",
        "feedback": "推送oss file成功",
        "hook_url": "",
        "hold_on": null,
        "record_id": null,
        "record_status": null,
        "record_feedback": null,
        "record_start_time": null,
        "record_end_time": null,
        "fc_request_id": null,
        "mizar_version": "2.0",
        "target_machine": "mizar-prod"
      },
      {
        "task_id": 13572,
        "shovel_project": "oc-shovel",
        "shovel_class": "EBChatbotConversation",
        "aspect": "OCChatbotConversation",
        "part_key": "None",
        "part_name": "None",
        "data_storage_type": "OSS_FILE",
        "storage": "leqee-private",
        "location": "oc-shovel/EBChatbotConversation/20230717/97701",
        "time_range": "2023-07-17_2023-07-17",
        "task_status": "DONE",
        "task_feedback": "Handle Mizar Task Failed, see log for details: (1062, \"Duplicate entry \"孙敏-2023-03-27 17:33:50\" for key \"idx_uk\"\")",
        "accept_time": 1689688186507,
        "start_time": 1689864603689,
        "finish_time": 1689864603949,
        "target_database_type": "MYSQL",
        "target_database_code": "bi-test-polar",
        "target_schema": "hermon",
        "target_table": "tiberias_lee_dy_cmm_live_details_daliy",
        "tp_code": "mizar-oc-test",
        "mizar_group_id": 152,
        "epitaph": "DONE",
        "feedback": "推送oss file成功",
        "hook_url": "",
        "hold_on": null,
        "record_id": null,
        "record_status": null,
        "record_feedback": null,
        "record_start_time": null,
        "record_end_time": null,
        "fc_request_id": null,
        "mizar_version": "2.0",
        "target_machine": "mizar-prod"
      }
    ]
';

$res = json_decode($json,true);


for($i=0;$i<50;$i++){
  $count = random_int(4444, 55112);
  $count1 = random_int(4444, 55112);
  $count2 = random_int(4444, 55112);
  $time = microtime(true)-random_int(33, 99999999);
  $time1 = microtime(true)-random_int(33, 99999999);
  $time_range = date('Y-m-d',$time).'_'.date('Y-m-d',$time1);
  $date = microtime(true);
  // echo microtime(true);die;

  // echo getMillisecond();
  // echo PHP_EOL;
  // echo getMillisecond1();
  // echo PHP_EOL;
  // echo microtime(1);
  // echo strtotime('2023-07-21 16:00:19')
  // die;

  $res[0]['task_id'] = $count;
  $res[0]['record_id'] = random_int(4444, 55112);
  $res[0]['accept_time'] = getMillisecond()-24*60*60*1000;
  $res[0]['time_range'] = $time_range;
  $res[1]['task_id'] = $count;
  $res[1]['record_id'] = random_int(4444, 55112);
  $res[1]['accept_time'] = getMillisecond()-24*60*60*1000;
  $res[0]['time_range'] = $time_range;
  $res[2]['task_id'] = $count1;
  $res[2]['accept_time'] = getMillisecond()-24*60*60*1000;
  $res[3]['task_id'] = $count2;
  $res[3]['accept_time'] = getMillisecond()-24*60*60*1000;
  $res[0]['time_range'] = $time_range;
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  // die;
  $result = postJsonData('http://localhost:8086/controller/full/monitor/syncToDbTaskRecord', json_encode($res),0);
  var_dump($result);
  // die;
}






function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)
        )
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

function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}


function getMillisecond1() { 
    return intval(microtime(1) * 1000); 
}