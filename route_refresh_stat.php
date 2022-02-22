<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
require_once 'includes/predis-1.1/autoload.php';

require("Services/ExpressApiService.php");
echo("[]" . date("Y-m-d H:i:s") . " refresh_route_stat  begin \r\n");

global $stat_drds_db;

$start_time = date("Y-m-d H:i:s");
refresh_stat_exception_status_version2();
$end_time = date("Y-m-d H:i:s");
echo("[]" . date("Y-m-d H:i:s") . " refresh_route_stat  cost:" . (strtotime($end_time) - strtotime($start_time)) . "S \r\n");

function refresh_stat_exception_status_version2()
{
    global $stat_drds_db;
    $hours_24_before = date("Y-m-d H:i:s", strtotime("-24 hours"));
    $hours_72_before = date("Y-m-d H:i:s", strtotime("-72 hours"));
    $hours_48_before = date("Y-m-d H:i:s", strtotime("-48 hours"));

    while (true) {
        //待揽件异常
        $sql = "
      select mailno_id,
      shipped_time
      from mailnos
      where logistic_status = 'WAIT_ACCEPT'
      and accept_exception_status in ('OK','WAIT_ACCEPT_WARNING')
      and shipped_time < '{$hours_24_before}'
      AND (shipped_time < '2020-11-11' OR shipped_time >= '2020-11-19')
      limit 100000;
    ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'accept_exception_status', 'WAIT_ACCEPT_ERROR', $stat_drds_db, null, 'WAIT_ACCEPT_24', null);
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //走件异常
        $sql = "
      select mailno_id,
      accept_time
      from mailnos
      where logistic_status = 'ACCEPT'
      and trace_exception_status in ('OK','ACCEPT_WARNING')
      and accept_time < '{$hours_24_before}'
      AND (shipped_time < '2020-11-11' OR shipped_time >= '2020-11-19')
      limit 100000;
    ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'ACCEPT_ERROR', $stat_drds_db, null, 'ACCEPT_24', 'accept_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    //11.11-11.16
    if (strtotime(date("Y-m-d H:i:s")) > strtotime('2020-11-11')) {
        while (true) {
            //待揽件异常
            $sql = "
      select mailno_id,
      shipped_time
      from mailnos
      where logistic_status = 'WAIT_ACCEPT'
      and accept_exception_status in ('OK','WAIT_ACCEPT_WARNING')
      and shipped_time < '{$hours_72_before}'
      AND shipped_time >= '2020-11-11' 
                AND shipped_time < '2020-11-16'
      limit 100000;
    ";
            $mailnos = $stat_drds_db->getAll($sql);
            setMailnosStatus($mailnos, 'accept_exception_status', 'WAIT_ACCEPT_ERROR', $stat_drds_db, null, 'WAIT_ACCEPT_24', null);
            if (count($mailnos) < 100000) {
                break;
            }
        }

        while (true) {
            //走件异常
            $sql = "
      select mailno_id,
      accept_time
      from mailnos
      where logistic_status = 'ACCEPT'
      and trace_exception_status in ('OK','ACCEPT_WARNING')
      and accept_time < '{$hours_72_before}'
      AND shipped_time >= '2020-11-11' 
                AND shipped_time < '2020-11-16'
      limit 100000;
    ";
            $mailnos = $stat_drds_db->getAll($sql);
            setMailnosStatus($mailnos, 'trace_exception_status', 'ACCEPT_ERROR', $stat_drds_db, null, 'ACCEPT_24', 'accept_time');
            if (count($mailnos) < 100000) {
                break;
            }
        }
    }


    //11.16-11.19
    if (strtotime(date("Y-m-d H:i:s")) > strtotime('2020-11-16')) {
        while (true) {
            //待揽件异常
            $sql = "
      select mailno_id,
      shipped_time
      from mailnos
      where logistic_status = 'WAIT_ACCEPT'
      and accept_exception_status in ('OK','WAIT_ACCEPT_WARNING')
      and shipped_time < '{$hours_48_before}'
      AND shipped_time >= '2020-11-16' 
                AND shipped_time < '2020-11-19'
      limit 100000;
    ";
            $mailnos = $stat_drds_db->getAll($sql);
            setMailnosStatus($mailnos, 'accept_exception_status', 'WAIT_ACCEPT_ERROR', $stat_drds_db, null, 'WAIT_ACCEPT_24', null);
            if (count($mailnos) < 100000) {
                break;
            }
        }

        while (true) {
            //走件异常
            $sql = "
      select mailno_id,
      accept_time
      from mailnos
      where logistic_status = 'ACCEPT'
      and trace_exception_status in ('OK','ACCEPT_WARNING')
      and accept_time < '{$hours_48_before}'
      AND shipped_time >= '2020-11-16' 
                AND shipped_time < '2020-11-19'
      limit 100000;
    ";
            $mailnos = $stat_drds_db->getAll($sql);
            setMailnosStatus($mailnos, 'trace_exception_status', 'ACCEPT_ERROR', $stat_drds_db, null, 'ACCEPT_24', 'accept_time');
            if (count($mailnos) < 100000) {
                break;
            }
        }
    }

    while (true) {
        //商品到达至离开同一分拨中心的时间间隔超过24小时异常
        $sql = "
      select mailno_id,
      last_arrive_time
      from mailnos
      where logistic_status = 'STATION'
      and trace_exception_status in('OK','STATION_WARNING')
      and last_arrive_time < '{$hours_24_before}'
      limit 100000;
    ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'STATION_ERROR', $stat_drds_db, " 'STATION' ", null, 'last_arrive_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    //正常时间段
    //不同省份
    refresh_trace_error_status($stat_drds_db, "AND region_type = 'DIFF_PROVINCE'
                AND last_route_time < SUBDATE(now(),INTERVAL 72 HOUR)
                AND (shipped_time < '2020-11-11' OR shipped_time >= '2020-11-19')");

    //特殊地区
    refresh_trace_error_status($stat_drds_db, "AND region_type = 'SPECIAL_REGION'
                AND last_route_time < SUBDATE(now(),INTERVAL 120 HOUR)
                AND (shipped_time < '2020-11-11' OR shipped_time >= '2020-11-19')");

    //相同省份或相同地区
    refresh_trace_error_status($stat_drds_db, "AND region_type in ('JZHW_REGION','JJJ_REGION','SAME_PROVINCE')
                AND last_route_time < SUBDATE(now(),INTERVAL 48 HOUR)
                AND (shipped_time < '2020-11-11' OR shipped_time >= '2020-11-19')");

    //11.11-11.16
    if (strtotime(date("Y-m-d H:i:s")) > strtotime('2020-11-11')) {
        refresh_trace_error_status($stat_drds_db, "AND region_type in ('JZHW_REGION','JJJ_REGION','SAME_PROVINCE','DIFF_PROVINCE','SPECIAL_REGION')
                AND last_route_time < SUBDATE(now(),INTERVAL 120 HOUR)
                AND shipped_time >= '2020-11-11' 
                AND shipped_time < '2020-11-16'");
    }


    //11.16-11.19
    if (strtotime(date("Y-m-d H:i:s")) > strtotime('2020-11-16')) {
        refresh_trace_error_status($stat_drds_db, "AND region_type in ('JZHW_REGION','JJJ_REGION','SAME_PROVINCE','DIFF_PROVINCE')
                AND last_route_time < SUBDATE(now(),INTERVAL 96 HOUR)
                AND shipped_time >= '2020-11-16' 
                AND shipped_time < '2020-11-19'");

        refresh_trace_error_status($stat_drds_db, "AND region_type = 'SPECIAL_REGION'
                AND last_route_time < SUBDATE(now(),INTERVAL 120 HOUR)
                AND shipped_time >= '2020-11-16' 
                AND shipped_time < '2020-11-19'");
    }

    //预警单独处理
    refresh_warning_exception_status($stat_drds_db);
}

function refresh_trace_error_status($stat_drds_db, $sql_where)
{
    while (true) {
        $sql = "
            SELECT
                mailno_id,
                last_route_time
                FROM
                mailnos
                WHERE
                logistic_status = 'STATION'
                AND trace_exception_status in ('OK','TRACE_WARNING')
                {$sql_where}
              limit 100000;
            ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'TRACE_ERROR', $stat_drds_db, " 'STATION' ", null, 'last_route_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }
}

function refresh_warning_status($stat_drds_db, $facility_id, $sql_where, $exception_hour_arr)
{
    //待揽件预警
    while (true) {
        $sql = "
      select mailno_id,
      shipped_time
      from mailnos
      where facility_id = {$facility_id}
      AND logistic_status = 'WAIT_ACCEPT'
      and accept_exception_status = 'OK'
      and shipped_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['waitAccept']} HOUR)
      {$sql_where}
      limit 100000;
    ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'accept_exception_status', 'WAIT_ACCEPT_WARNING', $stat_drds_db, " 'WAIT_ACCEPT' ", 'WAIT_ACCEPT_12', null);
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //走件预警
        $sql = "
      select mailno_id,
      accept_time
      from mailnos 
      where facility_id = {$facility_id}
      AND logistic_status = 'ACCEPT'
      and trace_exception_status = 'OK'
      and accept_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['accept']} HOUR)
      {$sql_where}
      limit 100000;
    ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'ACCEPT_WARNING', $stat_drds_db, " 'ACCEPT' ", 'ACCEPT_12', 'accept_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //商品到达至离开同一分拨中心的时间间隔超过12小时预警
        $sql = "
      select mailno_id,
      last_arrive_time
      from mailnos
      where facility_id = {$facility_id}
      AND logistic_status = 'STATION'
      and trace_exception_status = 'OK'
      and last_arrive_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['station']} HOUR)
      {$sql_where}
      limit 100000;
    ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'STATION_WARNING', $stat_drds_db, " 'STATION' ", null, 'last_arrive_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //节点停留预警
        $sql = "
            SELECT
                mailno_id,
                last_route_time
                FROM
                mailnos
                WHERE
                facility_id = {$facility_id}
                AND logistic_status = 'STATION'
                AND trace_exception_status = 'OK'
                AND region_type = 'JZHW_REGION'
                AND last_route_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['jzhwRegion']} HOUR)
                {$sql_where}
              limit 100000;
            ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'TRACE_WARNING', $stat_drds_db, " 'STATION' ", null, 'last_route_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //节点停留预警
        $sql = "
            SELECT
                mailno_id,
                last_route_time
                FROM
                mailnos
                WHERE
                facility_id = {$facility_id}
                AND logistic_status = 'STATION'
                AND trace_exception_status = 'OK'
                AND region_type = 'JJJ_REGION'
                AND last_route_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['jjjRegion']} HOUR)
                {$sql_where}
              limit 100000;
            ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'TRACE_WARNING', $stat_drds_db, " 'STATION' ", null, 'last_route_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //节点停留预警
        $sql = "
            SELECT
                mailno_id,
                last_route_time
                FROM
                mailnos
                WHERE
                facility_id = {$facility_id}
                AND logistic_status = 'STATION'
                AND trace_exception_status = 'OK'
                AND region_type = 'DIFF_PROVINCE'
                AND last_route_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['diffProvince']} HOUR)
                {$sql_where}
              limit 100000;
            ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'TRACE_WARNING', $stat_drds_db, " 'STATION' ", null, 'last_route_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //节点停留预警
        $sql = "
            SELECT
                mailno_id,
                last_route_time
                FROM
                mailnos
                WHERE
                facility_id = {$facility_id}
                AND logistic_status = 'STATION'
                AND trace_exception_status = 'OK'
                AND region_type = 'SPECIAL_REGION'
                AND last_route_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['specialRegion']} HOUR)
                {$sql_where}
              limit 100000;
            ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'TRACE_WARNING', $stat_drds_db, " 'STATION' ", null, 'last_route_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }

    while (true) {
        //节点停留预警
        $sql = "
            SELECT
                mailno_id,
                last_route_time
                FROM
                mailnos
                WHERE
                facility_id = {$facility_id}
                AND logistic_status = 'STATION'
                AND trace_exception_status = 'OK'
                AND region_type = 'SAME_PROVINCE'
                AND last_route_time < SUBDATE(now(),INTERVAL {$exception_hour_arr['sameProvince']} HOUR)
                {$sql_where}
              limit 100000;
            ";
        $mailnos = $stat_drds_db->getAll($sql);
        setMailnosStatus($mailnos, 'trace_exception_status', 'TRACE_WARNING', $stat_drds_db, " 'STATION' ", null, 'last_route_time');
        if (count($mailnos) < 100000) {
            break;
        }
    }
}

function refresh_warning_exception_status($stat_drds_db)
{
    global $sync_customer_redis_config;
    $redis = new Predis\Client([
        'host' => $sync_customer_redis_config['host'],
        'port' => $sync_customer_redis_config['port']
    ]);
    if ($sync_customer_redis_config['auth']) {
        $redis->auth($sync_customer_redis_config['auth']);
    }
    $redis->select($sync_customer_redis_config['database']);

    $sql = 'select DISTINCT(facility_id) from mailnos';
    $facility_ids = $stat_drds_db->getCol($sql);
    foreach ($facility_ids as $facility_id) {
        $exception_hour_str = $redis->hget('warning_hour', $facility_id);
        if ($exception_hour_str != null) {
            $exception_hour_arr = json_decode($exception_hour_str, true);
        } else {
            $exception_hour_arr = array('waitAccept' => 12, 'accept' => 12, 'station' => 12, 'jzhwRegion' => 36, 'jjjRegion' => 36, 'sameProvince' => 36, 'diffProvince' => 60, 'specialRegion' => 108);
        }
        refresh_warning_status($stat_drds_db, $facility_id, "AND (shipped_time >= '2020-11-19' OR shipped_time < '2020-11-11')", $exception_hour_arr);

        if (strtotime(date("Y-m-d H:i:s")) > strtotime('2020-11-11')) {
            $exception_hour_str = $redis->hget('warning_hour', $facility_id . '1111-1115');
            if ($exception_hour_str != null) {
                $exception_hour_arr = json_decode($exception_hour_str, true);
            } else {
                $exception_hour_arr = array('waitAccept' => 60, 'accept' => 60, 'station' => 12, 'jzhwRegion' => 108, 'jjjRegion' => 108, 'sameProvince' => 108, 'diffProvince' => 108, 'specialRegion' => 108);
            }
            refresh_warning_status($stat_drds_db, $facility_id, "AND shipped_time >= '2020-11-11' AND shipped_time < '2020-11-16'", $exception_hour_arr);
        }

        if (strtotime(date("Y-m-d H:i:s")) > strtotime('2020-11-16')) {
            $exception_hour_str = $redis->hget('warning_hour', $facility_id . '1116-1118');
            if ($exception_hour_str != null) {
                $exception_hour_arr = json_decode($exception_hour_str, true);
            } else {
                $exception_hour_arr = array('waitAccept' => 36, 'accept' => 36, 'station' => 12, 'jzhwRegion' => 96, 'jjjRegion' => 96, 'sameProvince' => 96, 'diffProvince' => 96, 'specialRegion' => 108);
            }
            refresh_warning_status($stat_drds_db, $facility_id, "AND shipped_time >= '2020-11-16' AND shipped_time < '2020-11-19'", $exception_hour_arr);
        }
        echo("[]" . ' refresh_warning_status, facility_id:' . $facility_id . PHP_EOL);
    }
}

/**
 * 修改面单列表的异常状态
 *
 */
function setMailnosStatus($mailnos, $exception_type, $exception_status, $stat_drds_db, $logistic_status, $old_exception_status, $time_type)
{
    if (!empty($mailnos) && count($mailnos) > 0) {
        $sql = "";
        $count = 0;
        do {
            $mailno = array_pop($mailnos);
            $sql .= "update mailnos set {$exception_type} = '{$exception_status}' ";
            if (!empty($time_type)) {
                $sql .= ",trace_exception_start_time = '{$mailno[$time_type]}',trace_exception_end_time=null";
            }
            if ($old_exception_status != null) {
                $sql .= ",exception_status = '{$old_exception_status}' ";
            }
            $sql .= " where mailno_id={$mailno['mailno_id']}";
            if (!empty($logistic_status)) {
                $sql .= " and logistic_status in ( {$logistic_status} ) ";
            }
            $sql .= ';';
            $count++;
            if ($count == 100) {
                $stat_drds_db->query($sql);
                $sql = "";
                $count = 0;
            }
        } while (count($mailnos) > 0);
        if ($count > 0) {
            $stat_drds_db->query($sql);
        }
    }
}