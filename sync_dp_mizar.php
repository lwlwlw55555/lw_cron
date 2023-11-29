<?php

require("includes/init.php");
// echo mb_strtoupper('dakhdiahi_dadih');die;

$lw_conf = array(
     "host" => "127.0.0.1:3306",
    // "host" => "121.40.113.153:3306",
    "user" => "root",
    "pass" => "aBc@123456",
    "charset" => "utf8",
    "pconnect" => "1",
    // "name" => "bi"
    "name" => "mizar"
);

// $lw_conf = array(
//     "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
//     "user" => "bi",
//     "pass" => "5*8Vnm&uTEF4",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     "name" => "dp_out"
// );


$to_conf = $lw_conf;
global $to_conf;
$lw_db = ClsPdo::getInstance($lw_conf);

// $test = $lw_db->getAll("select * from mizar_task");
// var_dump($test);

// $oms_db_conf = array(
//     "host" => "rm-bp10hv462sva1muzk5o.mysql.rds.aliyuncs.com:3306",
//     "user" => "admin_omsv2",
//     "pass" => "7o%01XSpZPE%",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     "name" => "bi"
// );
// $oms_db_conf = array(
//     "host" => "rm-bp1kxg882g197xnnvxo.mysql.rds.aliyuncs.com:3306",
//     "user" => "bi",
//     "pass" => "5*8Vnm&uTEF4",
//     "charset" => "utf8",
//     "pconnect" => "1",
//     "name" => "dp_out"
// );

// $from_conf = $oms_db_conf;
// global $from_conf;
// $oms_db = ClsPdo::getInstance($oms_db_conf);

moveDataByPartyId($oms_db,$lw_db);

function moveDataByPartyId($oms_db,$lw_db){

    
    // $sql = "select distinct TABLE_NAME from information_schema.COLUMNS  where TABLE_SCHEMA = 'mizar' and table_name in ('mizar_task','mizar_task_record')";
    // $tables = postFormData('https://bidp-test.leqee.com/bi/controller/monitor/sync/toDbTask/diySql',['sql'=>$sql]);
    // var_dump($values);die;

    $tables = ['mizar_task','mizar_task_record'];
    // $tables = $oms_db->getCol($sql);
    // var_dump($tables);

    echo "[insert into db2]" .date("Y-m-d H:i:s"). " party_id start" .PHP_EOL;

    $is_ok = false;
    foreach ($tables as $table){
        // if (!$is_ok && $table !== 'shop') {
            // continue;
        // }
        $is_ok = true;
        moveData($lw_db,$table);
    }

    // moveOrderGoodsExtension($party_id, 'order_goods_extension', $oms_db, $oms_2_db, $oms_db_conf, $oms_2_db_conf, 'sync');
    // echo "[insert into db2, party_id: {$party_id}]" .date("Y-m-d H:i:s"). " sync party_id end" .PHP_EOL;

}

function moveData($to_db,$table){
    global $to_conf;
    // $primay_key = null;
    if(empty($columns)){
        // $columns_infos = execRetry($from_db, $from_conf, "show COLUMNS from {$table}", 'getAll');
        $columns_infos = postFormData('https://bidp-test.leqee.com/bi/controller/monitor/sync/toDbTask/diySql',['sql'=>"show COLUMNS from {$table}"]);
        // die;

        foreach ($columns_infos as $columns_info) {
            $column = $columns_info['FIELD'];
            $columns[] = $column;
            if ($columns_info['KEY'] == 'PRI') {
                $primay_key = $column;
            }
        }
    }
    // var_dump($columns);die;
    if (empty($columns)) {
        return;
    }
    $sql_columns = implode('`,`',$columns);
    $start = 0;
    $limit = 1000;
    $total = 0;
    while (true){
        $sql = "select `{$sql_columns}` from {$table} where {$primay_key} >= {$start} order by {$primay_key} limit {$limit}";
        // $values = execRetry($from_db, $from_conf, $sql, 'getAll');
        $values = postFormData('https://bidp-test.leqee.com/bi/controller/monitor/sync/toDbTask/diySql',['sql'=>$sql]);
        // var_dump($values);die;
        
        $insert_sql_head = "insert ignore into {$table} (`".implode('`,`',$columns)."`) values";
        // $insert_sql_head = "replace into {$table} (`".implode('`,`',$columns)."`) values";
        $insert_sql_body = "";
        if(!empty($values) && count($values) > 0){
            foreach ($values as $value){
                $insert_sql_body .= " (";
                foreach ($columns as $column){
                    // echo  $value[mb_strtoupper($column)];die;
                    if(is_string($value[mb_strtoupper($column)])) {
                        $insert_sql_body .= "'".addslashes($value[mb_strtoupper($column)])."',";
                    }else{
                        if(empty($value[mb_strtoupper($column)])){
                            $value[mb_strtoupper($column)] = 'null';
                        }
                        $insert_sql_body .= $value[mb_strtoupper($column)].",";
                    }
                }
                $insert_sql_body = substr($insert_sql_body, 0, strlen($insert_sql_body) -1);
                $insert_sql_body .= "),";
            }
            $insert_sql_body = substr($insert_sql_body, 0,strlen($insert_sql_body)-1);
            $insert_sql = $insert_sql_head.$insert_sql_body.";";

            echo $insert_sql.PHP_EOL;
            // var_dump($to_conf);die;
            execRetry($to_db, $to_conf, $insert_sql, 'query');
            
            $start = $values[count($values) - 1][mb_strtoupper($primay_key)];
            echo $start.PHP_EOL;
            $total += count($values);
            if ($total > 10000

            ) {
                echo $table.' total > 100000'.PHP_EOL;
                break;
            }
        }else{
            break;
        }
        if(count($values) < $limit){
            break;
        }
    }
}

function execRetry($db, $db_conf, $sql, $type){
    global $party_id;
    $start_time = microtime(true);
    $result = null;
    try{
        switch ($type){
            case 'query':
                $result = $db->query($sql);
                break;
            case 'getAll':
                $result = $db->getAll($sql);
                break;
            case 'getOne':
                $result = $db->getOne($sql);
                break;
            case 'getCol':
                $result = $db->getCol($sql);
                break;
        }
        echo "[insert data to db2, party_id :{$party_id}] cost: ". ((microtime(true) - $start_time )* 1000) . "ms, " . date("Y-m-d H:i:s"). $sql .PHP_EOL;
        if(!empty($result)){
            return $result;
        }
    }catch (Exception $e){
        sleep(30);
        try {
            $db = ClsPdo::getInstanceRetry($db_conf);
            switch ($type) {
                case 'query':
                    $db->query($sql);
                    break;
                case 'getAll':
                    $result =  $db->getAll($sql);
                    break;
                case 'getOne':
                    $result = $db->getOne($sql);
                    break;
                case 'getCol':
                    $result = $db->getCol($sql);
                    break;
            }
            echo "[insert data to db2, party_id: {$party_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry success try1 " . $e->getMessage() . $sql .  PHP_EOL;
            if (!empty($result)) {
                return $result;
            }
        }catch (Exception $e2){
            echo "[insert data to db2, party_id: {$party_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try1 " . $e->getMessage() . $sql .  PHP_EOL;
            echo "[insert data to db2, party_id: {$party_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try2 " . $e2->getMessage() . $sql .  PHP_EOL;
        }
    }
}

function postFormData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ3bGl1MTEiLCJpYXQiOjE2OTA4NzUzMDgsImV4cCI6MTY5MDkwNDEwOH0.OgO5zoorflAzEWEqPVJq2gyJNJFDrkJpnP70MXl9NdVx_tmLw33w_ezjbWUX7CgNCFygj9XO3BQsnWjEQWm3WQ'
        )
    );

    $time_start = microtime(true);

    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    
    $result = json_decode($return_content, true);
    // i


    $time_end = microtime(true);
    $time = $time_end - $time_start;

    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result['obj'];
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