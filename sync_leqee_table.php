<?php

require("includes/init.php");

$lw_conf = array(
    "host" => "127.0.0.1:3306",
    "user" => "root",
    "pass" => "",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "omssync"
);
$to_conf = $lw_conf;
global $to_conf;
$lw_db = ClsPdo::getInstance($lw_conf);

$oms_db_conf = array(
    "host" => "rm-bp10hv462sva1muzk5o.mysql.rds.aliyuncs.com:3306",
    "user" => "admin_omsv2",
    "pass" => "7o%01XSpZPE%",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "omssync"
);
$from_conf = $oms_db_conf;
global $from_conf;
$oms_db = ClsPdo::getInstance($oms_db_conf);

moveDataByPartyId($oms_db,$lw_db);

function moveDataByPartyId($oms_db,$lw_db){
    
    $sql = "select distinct TABLE_NAME from information_schema.COLUMNS  where TABLE_SCHEMA = 'omssync'";
    $tables = $oms_db->getCol($sql);
    var_dump($tables);

    echo "[insert into db2]" .date("Y-m-d H:i:s"). " party_id start" .PHP_EOL;

    $is_ok = false;
    foreach ($tables as $table){
        if (!$is_ok && $table !== 'jd_category_info') {
            continue;
        }
        $is_ok = true;
        moveData($oms_db,$lw_db,$table);
    }

    // moveOrderGoodsExtension($party_id, 'order_goods_extension', $oms_db, $oms_2_db, $oms_db_conf, $oms_2_db_conf, 'sync');
    // echo "[insert into db2, party_id: {$party_id}]" .date("Y-m-d H:i:s"). " sync party_id end" .PHP_EOL;

}

function moveData($from_db,$to_db,$table){
    global $from_conf,$to_conf;
    $primay_key = null;
    if(empty($columns)){
        $columns_infos = execRetry($from_db, $from_conf, "show COLUMNS from omssync.{$table}", 'getAll');
        foreach ($columns_infos as $columns_info) {
            $column = $columns_info['Field'];
            $columns[] = $column;
            if ($columns_info['Key'] == 'PRI') {
                $primay_key = $column;
            }
        }
    }
    $sql_columns = implode('`,`',$columns);
    $start = 0;
    $limit = 1000;
    while (true){
        $sql = "select `{$sql_columns}` from {$table} where {$primay_key} >= {$start} order by {$primay_key} limit {$limit}";
        $values = execRetry($from_db, $from_conf, $sql, 'getAll');
        
        $insert_sql_head = "insert ignore into {$table} (`".implode('`,`',$columns)."`) values";
        // $insert_sql_head = "replace into {$table} (`".implode('`,`',$columns)."`) values";
        $insert_sql_body = "";
        if(!empty($values) && count($values) > 0){
            foreach ($values as $value){
                $insert_sql_body .= " (";
                foreach ($columns as $column){
                    if(is_string($value[$column])) {
                        $insert_sql_body .= "'".addslashes($value[$column])."',";
                    }else{
                        if(empty($value[$column])){
                            $value[$column] = 'null';
                        }
                        $insert_sql_body .= $value[$column].",";
                    }
                }
                $insert_sql_body = substr($insert_sql_body, 0, strlen($insert_sql_body) -1);
                $insert_sql_body .= "),";
            }
            $insert_sql_body = substr($insert_sql_body, 0,strlen($insert_sql_body)-1);
            $insert_sql = $insert_sql_head.$insert_sql_body.";";

            // echo $insert_sql.PHP_EOL;
            execRetry($to_db, $to_conf, $insert_sql, 'query');
            
            $start = $values[count($values) - 1][$primay_key];
            echo $start.PHP_EOL;
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