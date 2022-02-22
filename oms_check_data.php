<?php
require("includes/init.php");

global $oms_db,$oms_1_db,$oms_2_db,$oms_db_conf,$oms_1_db_conf,$oms_db2_conf;
 $oms_db_conf = array(
     "host" => "100.65.1.202:32001",
     "user" => "mddomsapi",
     "pass" => "123JoisnD0",
     "charset" => "utf8",
     "pconnect" => "1",
     "name" => "mddoms_0"
 );
 $oms_db = ClsPdo::getInstance($oms_db_conf);

 $oms_1_db_conf = array(
     "host" => "100.65.1.202:32001",
     "user" => "mddomsapi",
     "pass" => "123JoisnD0",
     "charset" => "utf8",
     "pconnect" => "1",
     "name" => "mddoms_1"
 );
 $oms_1_db = ClsPdo::getInstance($oms_1_db_conf);

 $oms_2_db_conf = array(
     "host" => "100.65.1.202:32001",
     "user" => "mddomsapi",
     "pass" => "123JoisnD0",
     "charset" => "utf8",
     "pconnect" => "1",
     "name" => "mddoms_2"
 );
 $oms_2_db = ClsPdo::getInstance($oms_2_db_conf);


if (empty($argv[1])) {
    echo 'ERROR params!';
    die;
}
$party_id = $argv[1];


moveDataByPartyId($party_id,$oms_db,$oms_2_db,$oms_db_conf,$oms_2_db_conf);

function moveDataByPartyId($party_id,$oms_db,$oms_2_db,$oms_db_conf,$oms_2_db_conf){
    $sql = "select distinct TABLE_NAME from information_schema.COLUMNS  where COLUMN_NAME ='party_id' and TABLE_SCHEMA = 'mddoms_1' and TABLE_NAME not like '%back%' and TABLE_NAME not like 'jwang%' and TABLE_NAME not like 'new%' and TABLE_NAME <> 'user_inventory_location_manage_config'";
    $tables = $oms_db->getCol($sql);
    var_dump($tables);
    $tables[] = 'order_goods_extension';

    echo "[insert into db2, party_id: {$party_id}]" .date("Y-m-d H:i:s"). " party_id start" .PHP_EOL;

    foreach ($tables as $table){
        checkData($party_id, $table, $oms_db, $oms_2_db, $oms_db_conf, $oms_2_db_conf, 'sync');
    }

    echo "[insert into db2, party_id: {$party_id}]" .date("Y-m-d H:i:s"). " sync party_id end" .PHP_EOL;
}

function checkData($party_id, $table, $from_db, $to_db, $from_conf, $to_conf, $type){
    $sql = "select count(1) from {$table} where party_id = {$party_id}";
    if ($table == 'order_goods_extension'){
        $sql = "select count(1) from order_goods_extension oge inner join order_goods og on og.order_goods_id = oge.order_goods_id where og.party_id = {$party_id}";
    }
    $from_count = execRetry($from_db, $from_conf, $sql, 'getOne');
    $from_count = empty($from_count)?0:$from_count;

    $to_count = execRetry($to_db, $to_conf, $sql, 'getOne');
    $to_count = empty($to_count)?0:$to_count;


    if ($from_count != $to_count) {
        echo "[CATCH-DIFF] table:{$table} from_count:{$from_count} to_count:{$to_count}".PHP_EOL;
    }else{
        echo "[NOMAL] table:{$table} from_count:{$from_count} to_count:{$to_count}".PHP_EOL;
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
        // echo "[insert data to db2, party_id :{$party_id}] cost: ". ((microtime(true) - $start_time )* 1000) . "ms, " . date("Y-m-d H:i:s"). $sql .PHP_EOL;
        if(!empty($result)){
            return $result;
        }
    }catch (\Exception $e){
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
            // echo "[insert data to db2, party_id: {$party_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry success try1 " . $e->getMessage() . $sql .  PHP_EOL;
            if (!empty($result)) {
                return $result;
            }
        }catch (\Exception $e2){
            echo "[insert data to db2, party_id: {$party_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try1 " . $e->getMessage() . $sql .  PHP_EOL;
            echo "[insert data to db2, party_id: {$party_id}] cost:" . ((microtime(true) - $start_time) * 1000) . "ms, " . date("Y-m-d H:i:s") . " insert catch retry fail try2 " . $e2->getMessage() . $sql .  PHP_EOL;
        }
    }
}