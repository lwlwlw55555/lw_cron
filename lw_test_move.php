<?php
require("includes/init.php");
date_default_timezone_set("Asia/Shanghai");

$party_id = 141;
$from = "mddoms_0";
$to = "mddoms_2";
//truncateDatabase($to);
// createDatabase($to);
// moveDatabase($from, $to, $party_id);
refreshAutoIncrementDatabase($to);
// checkDatabase($from, $to, $party_id);
// deleteDatabase($from, $to, $party_id);
refreshRedisCache($party_id);
// TODO refreshAutoIncrementDatabase 刷新自增主键，统一调到最大值。比如xx亿+10亿，保证每张表的起始值一致，并且亿后面都是0
// TODO checkDatabase 检查该party 每张表的 from和to的 count和max(last_updated_time) 一致
// TODO deleteDatabase
// TODO refreshRedisCache 刷新redis缓存的db
// TODO

function getDb (){
    $mdd_oms_db_conf = array(
        "host" => "10.1.14.61:3306",
        "name" => "mddomsuser",
        "user" => "mddomsapi",
        "pass" => "123JoisnD0",
        "charset" => "utf8",
        "pconnect" => "1",
    );
    return ClsPdo::getInstance( $mdd_oms_db_conf );
}


function refreshAutoIncrementDatabase($to){
    $db = getDb();
    $sql = "
      select DISTINCT TABLE_NAME from information_schema.COLUMNS where COLUMN_KEY = 'PRI' AND TABLE_SCHEMA = '{$to}'
        and COLUMN_TYPE like 'bigint%' and EXTRA = 'auto_increment'
    ";
    $table_names = $db->getCol($sql);
    $auto_increment = (intval(substr($to, 7)))*1000000000;
    // echo $auto_increment;die;
    foreach ($table_names as $table_name) {
        $sql = "alter table {$to}.{$table_name} auto_increment = {$auto_increment}";
        echo "[]" . date("Y-m-d H:i:s") .$sql.PHP_EOL;
        // $db->query($sql);
    }
}

function checkDatabase($from, $to, $party_id){
    $db = getDb();
    $sql = "
      select DISTINCT TABLE_NAME from information_schema.COLUMNS where COLUMN_KEY = 'PRI' AND TABLE_SCHEMA = '{$to}'
        and COLUMN_TYPE like 'bigint%' and EXTRA = 'auto_increment'
    ";
    $table_names = $db->getCol($sql);
    $table_names[] = 'order_goods_extension';
    foreach ($table_names as $table_name) {
        $sql = "select count(1) from {$from}.{$table_name} where party_id = {$party_id}";
        $from_count = $db->getOne($sql);

        $sql = "select count(1) from {$to}.{$table_name} where party_id = {$party_id}";
        $to_count = $db->getOne($sql);

        if ($from_count != $to_count) {
            echo "[]" . date("Y-m-d H:i:s") ."[NUM-CATCH-DIFF] table:{$table_name} from_count:{$from_count} to_count:{$to_count}".PHP_EOL;
            // die;
        } else {
            echo "[]" . date("Y-m-d H:i:s") ."[NUM-NOMAL] table:{$table_name} from_count:{$from_count} to_count:{$to_count}".PHP_EOL;
        }

        $sql = "select max(last_updated_time) from {$from}.{$table_name} where party_id = {$party_id}";
        $from_date = $db->getOne($sql);

        $sql = "select max(last_updated_time) from {$to}.{$table_name} where party_id = {$party_id}";
        $to_date = $db->getOne($sql);

        if ($from_date != $to_date) {
            echo "[]" . date("Y-m-d H:i:s") ."[DATE-CATCH-DIFF] table:{$table_name} from_date:{$from_date} to_date:{$to_date}".PHP_EOL;
            // die;
        } else {
            echo "[]" . date("Y-m-d H:i:s") ."[DATE-NOMAL] table:{$table_name} from_date:{$from_date} to_date:{$to_date}".PHP_EOL;
        }
    }
}

function deleteDatabase($from, $to, $party_id){
    $db = getDb();
   $sql = "
      select DISTINCT TABLE_NAME from information_schema.COLUMNS where COLUMN_NAME = 'party_id' AND TABLE_SCHEMA = '{$to}'
    ";
    $table_names = $db->getCol($sql);

    foreach ($table_names as $table_name) {
       $pk = $db->getOne("select COLUMN_NAME from information_schema.COLUMNS where TABLE_SCHEMA = '{$from}' and table_name = '{$table_name}' and COLUMN_KEY = 'PRI'");
       echo "select count(1) from {$from}.{$table_name} where party_id = {$party_id}".PHP_EOL;
       $total = $db->getOne("select count(1) from {$from}.{$table_name} where party_id = {$party_id}");

       if (!empty($total)) {
            $limit = 1000;
            while (true){
                $sql = "select {$pk} from {$from}.{$table_name} where party_id = {$party_id} limit {$limit}";
                $ids = $db->getCol($sql);
                if (!empty($ids)) {
                    $sql = "delete from {$from}.{$table_name} where {$pk} in (".implode(",", $ids).")";
                    echo "[]" . date("Y-m-d H:i:s") .$sql.PHP_EOL;

                    // $db->query($sql);
                    $total = count($ids);
                    echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} {$from}.{$table_name} count:{$total} ..." . PHP_EOL;

                    if ($table_name == 'order_goods') {
                        $sql = "delete from {$from}.order_goods_extension where {$pk} in (".implode(",", $ids).")";
                        echo "[]" . date("Y-m-d H:i:s") .$sql.PHP_EOL;
                        echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} {$from}.order_goods_extension count:{$total} ..." . PHP_EOL;
                        // $db->query($sql);
                    }
                    echo $sql.PHP_EOL;
                    // die;
                }

                if (empty($ids) || count($ids) < $limit) {
                    break;
                }
            }
       }
    }
}

function refreshRedisCache($party_id){
    $redis = getOmsRedis();
    $redis->select('0');
    $redis->del('partyId:'.$party_id);
    echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} redis db 0" . PHP_EOL;
    $redis->select('1');
    $redis->del('partyId:'.$party_id);
    echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} redis db 1" . PHP_EOL;
}

function getOmsRedis() {
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => '100.65.5.234',
          'port' => '6379'
    ]);
    $redis->auth('123JoisnD0');
    return $redis;
}