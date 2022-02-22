<?php
require("includes/init.php");
date_default_timezone_set("Asia/Shanghai");

/*
select
(select count(1) from mddoms_0.origin_order oo where oo.party_id = p.party_id limit 1) as oo_count,
(select max(oo.last_updated_time) from mddoms_0.origin_order oo where oo.party_id = p.party_id limit 1) as oo_time,
(select max(oo.expire_time) from mddomsuser.shop oo where oo.party_id = p.party_id limit 1) as shop_time,
ow.party_id, ow.user_name, ow.mobile,
concat('nohup php omsdbmove0to2.php ', p.party_id, ' > omsdbmove0to2_', p.party_id, '.log &') as cron
from owner_user_config o
inner join user ow on o.owner_user_id = ow.user_id
inner join party p on ow.party_id = p.party_id
inner join user u on ow.user_id = u.owner_user_id
left join session s on u.user_id = s.user_id and s.expire_time >= '2021-10-01'
where o.expire_time < '2021-10-01' and p.db = 0 and s.session_id is null
group by ow.party_id
order by oo_count desc
*/
$party_id = 23;
$from = "mddoms_0";
$to = "mddoms_2";

if (isset($argv[1])) {
    $party_id = $argv[1];
}
echo "[]" . date("Y-m-d H:i:s") . " party_id:{$party_id} from:{$from} to:{$to} begin " . PHP_EOL;
//truncateDatabase($to);
//createDatabase($to);
moveDatabase($from, $to, $party_id);
//refreshAutoIncrementDatabase($to);
checkDatabase($from, $to, $party_id);
deleteDatabase($from, $to, $party_id);
updatePartyDb($party_id, substr($to, 7));
refreshRedisCache($party_id);
echo "[]" . date("Y-m-d H:i:s") . " party_id:{$party_id} from:{$from} to:{$to} end " . PHP_EOL;

function createDatabase($to) {
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb($to);

    // 先把to库的表drop掉
    $sql = "select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '{$to}'";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        $hasData = $db->getOne("select 1 from {$to}.{$table_name} ");
        if ($hasData) {
            die("{$to}.{$table_name} hasData die" . PHP_EOL);
        }

        $sql = "drop table {$to}.{$table_name}";
        echo "[]" . date("Y-m-d H:i:s") . " {$sql}" . PHP_EOL;
        $db->query($sql);
    }

    // 再把1库的表like到to库
    $sql = "select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = 'mddoms_1'";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        $sql = "create table {$to}.{$table_name} like mddoms_1.{$table_name}";
        echo "[]" . date("Y-m-d H:i:s") . " {$sql}" . PHP_EOL;
        $db->query($sql);
    }

    // 把没有party_id的表drop掉
    $sql = "
        select 
            t.TABLE_NAME
        from 
            information_schema.TABLES t
            left join information_schema.COLUMNS c on t.TABLE_SCHEMA = c.TABLE_SCHEMA and t.TABLE_NAME = c.TABLE_NAME and c.column_name = 'party_id'
        where 
            t.TABLE_SCHEMA = '{$to}' and c.COLUMN_NAME is null and t.table_name <> 'order_goods_extension'
    ";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        $hasData = $db->getOne("select 1 from {$to}.{$table_name} ");
        if ($hasData) {
            die("{$to}.{$table_name} hasData die" . PHP_EOL);
        }

        $sql = "drop table {$to}.{$table_name}";
        echo "[]" . date("Y-m-d H:i:s") . " {$sql}" . PHP_EOL;
        $db->query($sql);
    }

    // 把没有主键的表drop掉
    $sql = "
        select 
            t.TABLE_NAME
        from 
            information_schema.TABLES t
            left join information_schema.COLUMNS c on t.TABLE_SCHEMA = c.TABLE_SCHEMA and t.TABLE_NAME = c.TABLE_NAME and c.COLUMN_KEY = 'PRI'
        where 
            t.TABLE_SCHEMA = '{$to}' and c.COLUMN_NAME is null
    ";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        $hasData = $db->getOne("select 1 from {$to}.{$table_name} ");
        if ($hasData) {
            die("{$to}.{$table_name} hasData die" . PHP_EOL);
        }

        $sql = "drop table {$to}.{$table_name}";
        echo "[]" . date("Y-m-d H:i:s") . " {$sql}" . PHP_EOL;
        $db->query($sql);
    }

    // 把0库没有的表drop掉
    $sql = "
        select 
            t.TABLE_NAME
        from 
            information_schema.TABLES t
            left join information_schema.TABLES c on c.TABLE_SCHEMA = 'mddoms_0' and t.TABLE_NAME = c.TABLE_NAME
        where 
            t.TABLE_SCHEMA = '{$to}' and c.TABLE_NAME is null
    ";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        $hasData = $db->getOne("select 1 from {$to}.{$table_name} ");
        if ($hasData) {
            die("{$to}.{$table_name} hasData die" . PHP_EOL);
        }

        $sql = "drop table {$to}.{$table_name}";
        echo "[]" . date("Y-m-d H:i:s") . " {$sql}" . PHP_EOL;
        $db->query($sql);
    }
}

function moveDatabase($from, $to, $party_id) {
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb();
    $temp = $db->getOne("select concat('mddoms_', db) from party where party_id = {$party_id}");
    if ($from != $temp) {
        die("from:{$from} temp:{$temp} party_id:{$party_id} from db error die " . PHP_EOL );
    }

    $sql = "select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '{$to}'";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        if ($table_name == 'order_goods_extension') {
            $hasData = $db->getOne("select 1 from {$to}.order_goods og inner join {$to}.order_goods_extension oge on og.order_goods_id = oge.order_goods_id where party_id = {$party_id}");
        } else {
            $hasData = $db->getOne("select 1 from {$to}.{$table_name} where party_id = {$party_id}");
        }
        if ($hasData) {
            die("{$to}.{$table_name} hasData die" . PHP_EOL);
        }

        echo "[]" . date("Y-m-d H:i:s") . " move party_id:{$party_id} {$to}.{$table_name} begin" . PHP_EOL;
        moveTable($from, $to, $party_id, $table_name);
        echo "[]" . date("Y-m-d H:i:s") . " move party_id:{$party_id} {$to}.{$table_name} end" . PHP_EOL . PHP_EOL;
    }
}

function moveTable($from, $to, $party_id, $table_name) {
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb();
//    $pk = $db->getOne("select COLUMN_NAME from information_schema.COLUMNS where TABLE_SCHEMA = '{$from}' and table_name = '{$table_name}' and COLUMN_KEY = 'PRI'");
//    $total = $db->getOne("select count(1) from {$from}.{$table_name} where party_id = {$party_id}");
//    if (empty($total)) {
//        echo "[]" . date("Y-m-d H:i:s") . "move party_id:{$party_id} {$to}.{$table_name} total:{$total} return " . PHP_EOL;
//        return ;
//    }

    if ($table_name == 'order_goods_extension') {
        $sql = "insert into {$to}.order_goods_extension select oge.* from {$from}.order_goods og inner join {$from}.order_goods_extension oge on og.order_goods_id = oge.order_goods_id where og.party_id = {$party_id}";
    } else {
        $sql = "insert into {$to}.{$table_name} select * from {$from}.{$table_name} where party_id = {$party_id}";
    }
    echo "[]" . date("Y-m-d H:i:s") . " move party_id:{$party_id} {$to}.{$table_name} sql:{$sql}" . PHP_EOL;

    $db->query($sql);
}

function truncateDatabase($to) {
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb($to);

    $sql = "select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '{$to}'";
    $table_names = $db->getCol($sql);
    foreach ($table_names as $table_name) {
        $sql = "truncate {$to}.{$table_name}";
        echo "[]" . date("Y-m-d H:i:s") . " {$sql}" . PHP_EOL;
        $db->query($sql);
    }
}


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
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb();
    $sql = "
      select DISTINCT TABLE_NAME from information_schema.COLUMNS where COLUMN_KEY = 'PRI' AND TABLE_SCHEMA = '{$to}'
        and COLUMN_TYPE like 'bigint%' and EXTRA = 'auto_increment'
    ";
    $table_names = $db->getCol($sql);
    $auto_increment = (intval(substr($to, 7)))*10000000000;
    foreach ($table_names as $table_name) {
        $sql = "alter table {$to}.{$table_name} auto_increment = {$auto_increment}";
        echo "[]" . date("Y-m-d H:i:s") .$sql.PHP_EOL;
        $db->query($sql);
    }
}

function checkDatabase($from, $to, $party_id){
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb();
    $sql = "select DISTINCT TABLE_NAME from information_schema.COLUMNS where COLUMN_KEY = 'PRI' AND TABLE_SCHEMA = '{$to}'";
    $table_names = $db->getCol($sql);
    $table_names[] = 'order_goods_extension';


    foreach ($table_names as $table_name) {
        if ($table_name == 'order_goods_extension') {
            $sql = "select count(1) data_count, max(oge.last_updated_time) last_updated_time from {$from}.order_goods og inner join {$from}.order_goods_extension oge on og.order_goods_id = oge.order_goods_id where og.party_id = {$party_id}";
            $from_data = $db->getRow($sql);

            $sql = "select count(1) data_count, max(oge.last_updated_time) last_updated_time from {$to}.order_goods og inner join {$to}.order_goods_extension oge on og.order_goods_id = oge.order_goods_id where og.party_id = {$party_id}";
            $to_data = $db->getRow($sql);
        } else {
            if(in_array($table_name, ['qmctemp_cost_tracking_consume','qmctemp_report_finance_order_item','system_update_temp_cost_tracking_consume','system_update_temp_report_finance_order_item','user_update_temp_cost_tracking_consume','user_update_temp_report_finance_order_item'])) {
                $last_updated_time = "'1992-06-19'";
            } else {
                $last_updated_time = 'max(last_updated_time)';
            }

            $sql = "select count(1) data_count, {$last_updated_time} last_updated_time from {$from}.{$table_name} where party_id = {$party_id}";
            $from_data = $db->getRow($sql);

            $sql = "select count(1) data_count, {$last_updated_time} last_updated_time from {$to}.{$table_name} where party_id = {$party_id}";
            $to_data = $db->getRow($sql);
        }

        if ($from_data['data_count'] != $to_data['data_count'] || $from_data['last_updated_time'] != $to_data['last_updated_time']) {
            echo "[]" . date("Y-m-d H:i:s") ." party_id:{$party_id} table:{$table_name} from_count:{$from_data['data_count']} to_count:{$to_data['data_count']} from_date:{$from_data['last_updated_time']} to_date:{$to_data['last_updated_time']} die".PHP_EOL;
            die;
        } else {
            echo "[]" . date("Y-m-d H:i:s") ." party_id:{$party_id} table:{$table_name} from_count:{$from_data['data_count']} to_count:{$to_data['data_count']} from_date:{$from_data['last_updated_time']} to_date:{$to_data['last_updated_time']} ok".PHP_EOL;
        }
    }
}

function deleteDatabase($from, $to, $party_id){
    if ($to == "mddoms_0" || $to == "mddoms_1" ) {
        die("。。。。。。。防一手");
    }
    $db = getDb();
    $sql = "select TABLE_NAME, COLUMN_NAME from information_schema.COLUMNS where TABLE_SCHEMA = '{$to}' and table_name <> 'order_goods_extension' and COLUMN_KEY = 'PRI'";
    $table_list = $db->getAll($sql);

    foreach ($table_list as $table) {
        $pk = $table['COLUMN_NAME'];
        $table_name = $table['TABLE_NAME'];
        $total = 0;
        while (true){
            $sql = "select {$pk} from {$from}.{$table_name} where party_id = {$party_id} limit 1000";
            $ids = $db->getCol($sql);
            if (empty($ids)) {
                echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} {$from}.{$table_name} total:{$total} done " . PHP_EOL;
                break;
            }
            $total += count($ids);
            $ids = implode(",", $ids);

            if ($table_name == 'order_goods') {
                $sql = "delete oge.* from {$from}.order_goods og inner join {$from}.order_goods_extension oge on og.order_goods_id = oge.order_goods_id where og.party_id = {$party_id} and og.{$pk} in ";
                echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} {$from}.order_goods_extension total:{$total} {$sql} ".PHP_EOL;
                $db->query($sql . "({$ids})");
            }

            $sql = "delete from {$from}.{$table_name} where party_id = {$party_id} and {$pk} in ";
            echo "[]" . date("Y-m-d H:i:s") . "delete party_id:{$party_id} {$from}.{$table_name} total:{$total} {$sql} ".PHP_EOL;
            $db->query($sql . "({$ids})");
        }
    }
}

function updatePartyDb($party_id, $party_db){
    $db = getDb();
    $sql = "update party set db = {$party_db} where party_id = {$party_id}";
    echo "[]" . date("Y-m-d H:i:s") . " updatePartyDb party_id:{$party_id} db:{$party_db}" . PHP_EOL;
    $db->query($sql);
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