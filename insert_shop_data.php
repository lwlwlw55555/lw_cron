<?php
/**
 * 插入shop data
 */
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
echo(date("Y-m-d H:i:s") . " begin".PHP_EOL);
$time1 = date("Y-m-d H:i:s");
$startDayDate = date("Y-m-d",strtotime("-1 day"));
if (isset($opt_params['start'])){
    $is_date = strtotime($opt_params['start'])?strtotime($opt_params['start']):false;
    if($is_date){
        $startDayDate = $opt_params['start'];
    }
}
$endDayDate = date("Y-m-d");
if (isset($opt_params['end'])){
    $is_date = strtotime($opt_params['end'])?strtotime($opt_params['end']):false;
    if($is_date){
        $endDayDate = $opt_params['end'];
    }
}
echo "start_date :".$startDayDate."__________"."end_date :".$endDayDate.PHP_EOL;

global $db_user, $db;
$sql = " 
        select 
            u.rds,
            u.db,
            u.facility_id  
        from 
            `user` u 
        where 
            facility_id > 0
";
$user_list = $db_user->getAll($sql);
echo(date("Y-m-d H:i:s") . " 一共获取到".count($user_list)."用户".PHP_EOL);
$rds_list = getFacilityIdListByRds($user_list);
$i = 0;
while ($startDayDate < $endDayDate) {
    foreach ($rds_list as $user_rds => $db_list) {
        foreach ($db_list as $user_db => $facility_ids) {
            selectRds($user_rds, $user_db);
            foreach ($facility_ids as $facility_id) {
                $i++;
                echo(date("Y-m-d H:i:s") . " start {$i}__{$facility_id}".PHP_EOL);
                $order_count_sql = "
                    SELECT
                        MAX(t.facility_id) AS facility_id,
                        MAX(t.shop_id)	AS shop_id,
                        MAX(t.platform_shop_id) AS platform_shop_id,
                        MAX(t.order_count) AS order_count,
                        MAX(t.print_count) AS print_count
                    FROM
                        (
                            SELECT 
                                COUNT(*) AS order_count,
                                mgs.facility_id,
                                s.shop_id,
                                s.platform_shop_id,
                                0 as print_count
                            FROM 
                                multi_goods_shipment mgs
                                INNER JOIN shop s on mgs.facility_id = s.default_facility_id AND mgs.shop_id = s.shop_id
                            WHERE 
                                mgs.created_time >= '{$startDayDate}'
                                AND mgs.created_time <  DATE_ADD('{$startDayDate}',INTERVAL +1 day)
                                AND mgs.facility_id = {$facility_id}
                            GROUP BY
                                s.shop_id
                            UNION All
                            SELECT 
                                0 as order_count,
                                mgs.facility_id,
                                s.shop_id,
                                s.platform_shop_id,
                                COUNT(*) AS print_count
                            FROM 
                                multi_goods_shipment mgs
                                INNER JOIN shop s on mgs.facility_id = s.default_facility_id AND mgs.shop_id = s.shop_id
                            WHERE 
                                mgs.is_print_tracking = 1
                                AND mgs.print_time >=  '{$startDayDate}'
                                AND mgs.print_time <  DATE_ADD('{$startDayDate}',INTERVAL +1 day)
                                AND mgs.facility_id = {$facility_id}
                            GROUP BY
                                s.shop_id
                        ) AS t 
                    GROUP BY 
                        t.shop_id
                    HAVING 
                        print_count > 0 OR  order_count > 0
                ";
                $result = $db->getAll($order_count_sql);
                $value_str = '';
                $count = 0;
                foreach ($result as $item) {
                    $sql_str = getInsertShopDataSql($item, $startDayDate);
                    if (empty($value_str)) {
                        $value_str = $sql_str;
                    } else {
                        $value_str .= "," . $sql_str;
                    }
                    $count++;
                }
                if (!empty($value_str)) {
                    insertShopData($value_str);
                    echo "facility_id = ".$facility_id." date = {$startDayDate} insert {$count} 条".PHP_EOL;
                }
                echo(date("Y-m-d H:i:s") . " end {$i}__{$facility_id}".PHP_EOL);
            }
        }
    }
    $startDayDate = date("Y-m-d", strtotime("+1 day", strtotime($startDayDate)));
}

$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo( date("Y-m-d H:i:s") . " end cost {$cost}s \r\n");
function getInsertShopDataSql($item,$count_date){
    $sql = "
        (
            {$item['facility_id']},
            {$item['shop_id']},
           '{$item['platform_shop_id']}',
            '{$count_date}',
            {$item['order_count']},
            {$item['print_count']}
        )
    ";
    return $sql;
}

function insertShopData($value_str){
    global $db_user;
    $sql = "
        replace into 
            shop_data
            (
                facility_id,
                shop_id,
                platform_shop_id,
                count_date,
                order_count,
                print_count
            ) values {$value_str}
    ";
    $db_user->query($sql);
}


