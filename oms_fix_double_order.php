<?php
require("includes/init.php");
date_default_timezone_set("Asia/Shanghai");
$mdd_oms_db_conf = array(
    "host" => "10.1.14.61:3306",
    "name" => "mddoms_0",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
);
$mdd_oms_db = ClsPdo::getInstance( $mdd_oms_db_conf );

$min_id = $mdd_oms_db->getOne("select min(origin_order_goods_id) from origin_order_goods oog where created_time >= '2021-11-10' ");

$count = 0;
while(true) {
    $sql = "select origin_order_goods_id from origin_order_goods where origin_order_goods_id >= {$min_id} order by origin_order_goods_id asc limit 500";
    $ids = $mdd_oms_db->getCol($sql);
    $oog_ids = $ids;
    if (empty($ids)) {
        echo "[] ".(date("Y-m-d H:i:s")) . " count:{$count} done" . PHP_EOL;
        break;
    }
    $min_id = $ids[count($ids)-1];
    $count += count($ids);

    $ids = check($mdd_oms_db, $oog_ids);
    if (! empty($ids)) {
        echo "[] ".(date("Y-m-d H:i:s")) . " count:{$count} check1 error:" . implode(",", $ids) . PHP_EOL;
        error($mdd_oms_db, $ids, 1);
        continue;
    }
    $ids = doubleCheck($mdd_oms_db, $oog_ids);
    if (! empty($ids)) {
        echo "[] ".(date("Y-m-d H:i:s")) . " count:{$count} check2 error:" . implode(",", $ids) . PHP_EOL;
        error($mdd_oms_db, $ids, 2);
        continue;
    }
    echo "[] ".(date("Y-m-d H:i:s")) . " count:{$count} ok" . PHP_EOL;
}

function check($mdd_oms_db, $ids) {
    $ids = implode(",", $ids);
    $sql = "SELECT
            oog.origin_order_goods_id
        FROM
        origin_order_goods oog
        INNER JOIN order_goods og ON oog.origin_order_goods_id = og.origin_order_goods_id AND og.create_type = 'ORIGIN'
        INNER JOIN order_info oi ON og.order_id = oi.order_id
        LEFT JOIN order_action oa ON og.order_id = oa.order_id AND oa.type = 'UPDATE_ORDER_GOODS'
        WHERE
        og.origin_order_goods_id in ({$ids})
        and oi.order_status NOT IN ( 'CLOSED_MANUAL', 'CLOSED_SYSTEM', 'CLOSED_PLATFORM' )
        AND oog.platform_sku_id IS NOT NULL
        AND oa.order_id IS NULL
        GROUP BY
        oog.origin_order_goods_id,
        oog.goods_number,
        oog.transfer_goods_number
        HAVING
        sum( og.goods_number ) != oog.transfer_goods_number
    ";
    $ids = $mdd_oms_db->getCol($sql);
    if (empty($ids)) {
        return $ids;
    }
    return removeIgnore($mdd_oms_db, $ids);
}
function doubleCheck($mdd_oms_db, $ids) {
    $ids = implode(",", $ids);
    $sql = "SELECT
            oog.origin_order_goods_id
        FROM
        origin_order_goods oog
        inner join order_goods og on oog.origin_order_goods_id = og.origin_order_goods_id and og.create_type = 'ORIGIN'
        inner join order_info oi on og.order_id = oi.order_id
        left join platform_sku ps on oog.platform_sku_id = ps.platform_sku_id and og.shop_id = ps.shop_id
        left join sku s on ps.sku_id = s.sku_id
        left join sku_group_detail sgd on s.sku_id = sgd.group_sku_id
        left join order_action oa on og.order_id = oa.order_id and oa.type='UPDATE_ORDER_GOODS'
        WHERE
        og.origin_order_goods_id in ({$ids})
        and oog.platform_sku_id is not null
        and ps.platform_sku_id is not null
        and oi.order_status != 'CLOSED_SYSTEM'
        and oa.order_id is null
        GROUP BY
        oog.origin_order_goods_id,
        oog.goods_number
        HAVING
        sum( og.goods_number )/ ifnull( sum( sgd.number ), 1 ) > oog.goods_number";
    $ids = $mdd_oms_db->getCol($sql);
    if (empty($ids)) {
        return $ids;
    }
    return removeIgnore($mdd_oms_db, $ids);
}
function removeIgnore($mdd_oms_db, $ids) {
    $ignore_ids = implode(",", $ids);
    $sql = "select
            distinct oog.origin_order_goods_id
        from
            origin_order_goods oog
            inner join order_goods og on og.origin_order_id = oog.origin_order_id
            inner join order_action oa on og.order_id = oa.order_id
        where
            oog.origin_order_goods_id in ({$ignore_ids})
            and oa.type in ('DELETE_ORDER_GOODS', 'UPDATE_ORDER_GOODS')";
    $ignore_ids = $mdd_oms_db->getCol($sql);
    if (! empty($ignore_ids)) {
        $ids = array_diff($ids, $ignore_ids);
    }
    return $ids;
}

function error($mdd_oms_db, $ids, $check) {
    foreach ($ids as $id) {
        $mdd_oms_db->query("insert into jwang_temp_1111_origin_order_goods values ({$id}, {$check})");
    }
}
