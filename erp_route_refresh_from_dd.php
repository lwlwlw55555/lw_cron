<?php
require("includes/init.php");

echo(date("Y-m-d H:i:s") . "job begin \r\n");
global $db, $dd_stat_db, $stat_drds_db, $dd_print_tool_db,$dd_print_tool_db_conf,$db_user;

$sql = "
    select 
        distinct u.party_id
    from 
        user u 
        inner join pay_oauth  p on u.user_id = p.user_id
        inner join session_date sd on u.user_id = sd.user_id
    where
        p.expire_time > now() and 
        sd.session_date > DATE_SUB(curdate(),INTERVAL 3 day)
";
$party_ids = $db_user->getCol($sql);
$party_ids = implode(",", $party_ids);

$sql = "
    select 
        s.shop_id, s.default_facility_id as facility_id, s.platform_shop_id
    from 
        shop s
    where 
        s.party_id in ({$party_ids}) and 
        s.app_key = '4f5ebddcc15749ba8177cfd1ab59a4fd'
";

$shops = $sync_db->getAll($sql);
$day7ago = date("Y-m-d H:i:s", time() - 7 * 24 * 60 * 60);

foreach ($shops as $shop) {
    $mailnos_sql = "select
                        mailno_id,
                        shop_id,
                        tracking_number,
                        facility_id,
                        last_route_time
                    from mailnos
                    where shop_id = {$shop['shop_id']}
                    and facility_id = {$shop['facility_id']}
                    and logistic_status in ('INIT', 'WAIT_ACCEPT', 'ACCEPT')
                    and (last_route_time < DATE_SUB(now(), INTERVAL 2 HOUR ) or last_route_time is null)
                    and created_time > '{$day7ago}'";
    echo(date("Y-m-d H:i:s") .$mailnos_sql . "\r\n");
    $erp_stat_mailnos = $stat_drds_db->getAll($mailnos_sql);
    if (count($erp_stat_mailnos) == 0) {
        continue;
    }
    $dd_shop_sql = "select shop_id from shop where platform_shop_id = '{$shop['platform_shop_id']}'";
    echo(date("Y-m-d H:i:s") .$dd_shop_sql . "\r\n");
    try {
        $dd_shop = $dd_print_tool_db->getOne($dd_shop_sql);
    } catch (Exception $e) {
        $dd_print_tool_db = ClsPdo::getInstance($dd_print_tool_db_conf);
        echo(date("Y-m-d H:i:s") . "重置数据源dd_user" . PHP_EOL);
        $dd_shop = $dd_print_tool_db->getOne($dd_shop_sql);
    }
    if (empty($dd_shop)) {
        continue;
    }
    $tracking_number_map = array();
    foreach ($erp_stat_mailnos as $mailnos) {
        $tracking_number_map[$mailnos['tracking_number']] = $mailnos;
        if (count($tracking_number_map) % 4000 === 0 && count($tracking_number_map) !== 0) {
            updateErpMailnos($tracking_number_map,$dd_shop);
            $tracking_number_map = array();
        }
    }
    updateErpMailnos($tracking_number_map,$dd_shop);
}

function updateErpMailnos($tracking_number_map, $dd_shop)
{
    global $stat_drds_db, $dd_stat_db, $dd_stat_db_conf;
    if (empty($tracking_number_map)) {
        return;
    }
    $tracking_numbers_str = implode("','",array_keys($tracking_number_map));
    $dd_mailnos_sql = "select * from route_mailnos where shop_id = {$dd_shop} and tracking_number in ('{$tracking_numbers_str}')";
    echo(date("Y-m-d H:i:s") .$dd_mailnos_sql . "\r\n");
    try {
        $result = $dd_stat_db->getAll($dd_mailnos_sql);
    } catch (Exception $exception) {
        $dd_stat_db = ClsPdo::getInstance($dd_stat_db_conf);
        echo(date("Y-m-d H:i:s") . "重置数据源dd_history" . PHP_EOL);
        $result = $dd_stat_db->getAll($dd_mailnos_sql);
    }

    foreach ($result as $dd_mailnos) {
        if (!array_key_exists($dd_mailnos['tracking_number'], $tracking_number_map)) {
            continue;
        }
        $erp_mailnos = $tracking_number_map[$dd_mailnos['tracking_number']];
        if ($dd_mailnos['last_route_time'] > $erp_mailnos['last_route_time']) {
            $sql_set = array();
            if (!empty($dd_mailnos['logistic_status'])) {
                $sql_set[] = "`logistic_status` = '{$dd_mailnos['logistic_status']}'";
            }
            if (!empty($dd_mailnos['exception_status'])) {
                $sql_set[] = "`exception_status` = '{$dd_mailnos['exception_status']}'";
            }
            if (!empty($dd_mailnos['accept_time'])) {
                $sql_set[] = "`accept_time` = '{$dd_mailnos['accept_time']}'";
            }
            if (!empty($dd_mailnos['station_time'])) {
                $sql_set[] = "`station_time` = '{$dd_mailnos['station_time']}'";
            }
            if (!empty($dd_mailnos['delivering_time'])) {
                $sql_set[] = "`delivering_time` = '{$dd_mailnos['delivering_time']}'";
            }
            if (!empty($dd_mailnos['signed_time'])) {
                $sql_set[] = "`signed_time` = '{$dd_mailnos['signed_time']}'";
            }
            if (!empty($dd_mailnos['last_route_context'])) {
                $last_route_context = dryQuote($dd_mailnos['last_route_context']);
                $sql_set[] = "`last_route_context` = '$last_route_context'";
            }
            if (!empty($dd_mailnos['last_route_time'])) {
                $sql_set[] = "`last_route_time` = '{$dd_mailnos['last_route_time']}'";
            }
            if (!empty($dd_mailnos['last_query_time'])) {
                $sql_set[] = "`last_query_time` = '{$dd_mailnos['last_query_time']}'";
            }
            if (empty($sql_set)) {
                continue;
            }
            $sql_set_str = implode(',', $sql_set);
            $update_sql = "UPDATE `mailnos` SET
                              {$sql_set_str}
                           WHERE
                              `facility_id` = {$erp_mailnos['facility_id']}
                              and `mailno_id` = {$erp_mailnos['mailno_id']};

                           ";
            echo(date("Y-m-d H:i:s") .$update_sql . "\r\n");
            $stat_drds_db->query($update_sql);
        }
    }

}

function dryQuote($inp)
{
    if (!empty($inp) && is_string($inp)) {
        $x = str_replace(array('\\', "\0", "'", '"', "\x1a"), array('\\\\', '\\0', "\\'", '\\"', '\\Z'), $inp);
        return $x;
    }
    return $inp;
}
