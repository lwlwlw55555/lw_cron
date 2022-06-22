<?php
require("includes/init.php");
echo(date("Y-m-d H:i:s") . " inset_ztc_word_stat  begin \r\n");

// global $wangmeng_db;
// global $db;
global $db;
if (!isset($argv[1])){
    echo date("Y-m-d H:i:s").' 未找到日期参数 日期为当日:'.date("Y-m-d").PHP_EOL;
	$date = date("Y-m-d");
    // echo "params error: 缺少日期参数".PHP_EOL;
	// die;	
}else{
    $date = $argv[1];
}

$rate = $db->getOne('select sum(if(impression>0,1,0))/count(*) rate from stat_pdd_cat');
$count = $db->getOne('select count(*) rate from stat_pdd_word where impression>0');

if(!(!empty($rate) && !empty($count) && $count>2000000 && $rate >0.9)){
	echo date("Y-m-d H:i:s")." 未满足数据条件不予以同步：count:{$count} rate:{$rate}".PHP_EOL;
	die;
}

$sql1 = "INSERT IGNORE INTO pdd_ztc_cat_type_daily(`cat_id`,`cat_type`,`date`)
        SELECT spc.`cat_id`,
        CASE WHEN 
                  spc.`impression_keyword_num` < t1.v1 AND
                    spc.`impression` < t2.v2
                 THEN 0
                 WHEN  
                  spc.`impression_keyword_num` < t1.v1 AND
                    spc.`impression` >=  t2.v2
                 THEN 1
                 WHEN  
                  spc.`impression_keyword_num` >= t1.v1 AND
                  spc.`impression` >=  t2.v2
                 THEN 2
                 WHEN  
                  spc.`impression_keyword_num` >= t1.v1 AND
                  spc.`impression` <  t2.v2
                 THEN 0
        END cat_type_result ,
        NOW() `date`
        FROM stat_pdd_cat spc
        INNER JOIN 
        (SELECT AVG(impression_keyword_num) v1 FROM stat_pdd_cat where impression_keyword_num > 0 AND impression_keyword_num is NOT NULL
        ) t1
        ON 1=1
        INNER JOIN (SELECT AVG(`impression`) v2 FROM stat_pdd_cat where impression > 0 AND impression is NOT NULL ) t2
        ON 1=1";
$db->query($sql1);
echo date("Y-m-d H:i:s").' sql1 INSERT IGNORE INTO pdd_ztc_cat_type_daily complete'.PHP_EOL;

$sql2 = " REPLACE INTO pdd_ztc_word_stat
        SELECT  t3.`cat_id`,
                t3.`word`,
                t3.`is_have_recommend`,
                t3.`is_have_report`,
                t3.`heat`,
                t3.`trend`,
                t3.`compete`,
                t3.`avg_bid`,
                t3.`impression`,
                t3.`impression_goods_num`,
                t3.`click`,
                t3.`click_goods_num`,
                t3.`order_num`,
                t3.`order_goods_num`,
                t3.`spend`,
                t3.`gmv`,
                t3.`roi`,
                t3.`conversion`,
                t3.`cost_performance`,
                t3.`a1`,
                CASE 
                WHEN t3.rank = 0 THEN NULL
                WHEN t4.word_sum < 4 THEN NULL
                WHEN t3.rank >= t4.word_sum * 0.75 AND t3.rank <= t4.word_sum * 0.89 THEN 1
                WHEN t3.rank >= t4.word_sum * 0.89 AND t3.rank <= t4.word_sum * 0.96 THEN 2
                WHEN t3.rank >= t4.word_sum * 0.96 THEN 3
                ELSE NULL END word_score
        FROM
        (
            SELECT
            t2.*,
            @rank := (case WHEN t2.a1 <= 0 then 0
                    when @Grp = t2.cat_id then @rank + 1 
                    else 1 end) rank, 
            @Grp:=t2.cat_id cat_id2
            FROM
            (
                SELECT  t1.*,
                LOG10(CASE WHEN t1.heat = 0 OR t1.heat is NULL then 1 ELSE t1.heat END) - 
                LOG10(CASE WHEN t1.compete = 0 OR t1.compete is NULL then 1 ELSE t1.compete END) a1
                FROM
                (
                    SELECT 
                    w.*,
                    TRUNCATE(w.`order_num` / (CASE WHEN w.`click` = 0 then NULL ELSE w.`click` END),4) conversion,
                    TRUNCATE(w.`order_num`*1000 / (CASE WHEN w.`avg_bid` = 0 then NULL ELSE w.`avg_bid` END),4) cost_performance
                    FROM stat_pdd_word w 
                    LEFT JOIN 
                    pdd_ztc_cat_type_daily c 
                    ON w.cat_id = c.cat_id
                    where (c.cat_type = 0 AND
                    c.date = '{$date}') OR (c.cat_id is NULL)
                ) t1 ORDER BY t1.cat_id, a1
            )t2, (SELECT @rank := 0,@Grp := 0) a
        )t3 
        LEFT JOIN
        (
            SELECT w.cat_id , count(*) word_sum FROM stat_pdd_word w 
            LEFT JOIN 
            pdd_ztc_cat_type_daily c 
            ON w.cat_id = c.cat_id
            where 
            (LOG10(CASE WHEN w.heat = 0 OR w.heat is NULL then 1 ELSE w.heat END) - 
            LOG10(CASE WHEN w.compete = 0 OR w.compete is NULL then 1 ELSE w.compete END)) > 0
            AND ((c.cat_type = 0 AND
                    c.date = '{$date}') OR (c.cat_id is NULL))
            GROUP BY w.cat_id
        )t4
        on t3.cat_id = t4.cat_id";
$db->query($sql2);
echo date("Y-m-d H:i:s").' sql2 cat_type=0 complete'.PHP_EOL;


$sql3 = "REPLACE INTO pdd_ztc_word_stat
        SELECT  t3.`cat_id`,
                t3.`word`,
                t3.`is_have_recommend`,
                t3.`is_have_report`,
                t3.`heat`,
                t3.`trend`,
                t3.`compete`,
                t3.`avg_bid`,
                t3.`impression`,
                t3.`impression_goods_num`,
                t3.`click`,
                t3.`click_goods_num`,
                t3.`order_num`,
                t3.`order_goods_num`,
                t3.`spend`,
                t3.`gmv`,
                t3.`roi`,
                t3.`conversion`,
                t3.`cost_performance`,
                t3.`a1`,
                CASE 
                WHEN t3.rank = 0 THEN NULL
                WHEN t4.word_sum < 4 THEN NULL
                WHEN t3.rank >= t4.word_sum * 0.75 AND t3.rank <= t4.word_sum * 0.89 THEN 1
                WHEN t3.rank >= t4.word_sum * 0.89 AND t3.rank <= t4.word_sum * 0.96 THEN 2
                WHEN t3.rank >= t4.word_sum * 0.96 THEN 3
                ELSE NULL END word_score
        FROM
        (
            SELECT
            t2.*,
            @rank := (case WHEN t2.a1 <= 0 then 0
                    when @Grp = t2.cat_id then @rank + 1 
                    else 1 end) rank, 
            @Grp:=t2.cat_id cat_id2
            FROM
            (
                SELECT  t1.*,
                LOG10(CASE WHEN t1.heat = 0 OR t1.heat is NULL then 1 ELSE t1.heat END) - 
                LOG10(CASE WHEN t1.compete = 0 OR t1.compete is NULL then 1 ELSE t1.compete END) + 
                LOG10(CASE WHEN t1.order_num = 0 OR t1.order_num is NULL then 1 ELSE t1.order_num END) a1 
                FROM
                (
                    SELECT 
                    w.*,
                    TRUNCATE(w.`order_num` / (CASE WHEN w.`click` = 0 then NULL ELSE w.`click` END),4) conversion,
                    TRUNCATE(w.`order_num`*1000 / (CASE WHEN w.`avg_bid` = 0 then NULL ELSE w.`avg_bid` END),4) cost_performance
                    FROM stat_pdd_word w 
                    LEFT JOIN 
                    pdd_ztc_cat_type_daily c 
                    ON w.cat_id = c.cat_id
                    where c.cat_type = 1 AND
                          c.date = '{$date}'
                ) t1 ORDER BY t1.cat_id, a1
            )t2, (SELECT @rank := 0,@Grp := 0) a
        )t3 
        LEFT JOIN
        (
            SELECT w.cat_id , count(*) word_sum FROM stat_pdd_word w 
            LEFT JOIN 
            pdd_ztc_cat_type_daily c 
            ON w.cat_id = c.cat_id
            where 
            (LOG10(CASE WHEN w.heat = 0 OR w.heat is NULL then 1 ELSE w.heat END) - 
                LOG10(CASE WHEN w.compete = 0 OR w.compete is NULL then 1 ELSE w.compete END) + 
                LOG10(CASE WHEN w.order_num = 0 OR w.order_num is NULL then 1 ELSE w.order_num END)) > 0
            AND c.cat_type = 1 
            AND c.date = '{$date}'
            GROUP BY w.cat_id
        )t4
        on t3.cat_id = t4.cat_id";
$db->query($sql3);
echo date("Y-m-d H:i:s").' sql3 cat_type=1 complete'.PHP_EOL;


$sql4 = "REPLACE INTO pdd_ztc_word_stat
        SELECT  t3.`cat_id`,
                t3.`word`,
                t3.`is_have_recommend`,
                t3.`is_have_report`,
                t3.`heat`,
                t3.`trend`,
                t3.`compete`,
                t3.`avg_bid`,
                t3.`impression`,
                t3.`impression_goods_num`,
                t3.`click`,
                t3.`click_goods_num`,
                t3.`order_num`,
                t3.`order_goods_num`,
                t3.`spend`,
                t3.`gmv`,
                t3.`roi`,
                t3.`conversion`,
                t3.`cost_performance`,
                t3.`a1`,
                CASE 
                WHEN t3.rank = 0 THEN NULL
                WHEN t4.word_sum < 4 THEN NULL
                WHEN t3.rank >= t4.word_sum * 0.75 AND t3.rank <= t4.word_sum * 0.89 THEN 1
                WHEN t3.rank >= t4.word_sum * 0.89 AND t3.rank <= t4.word_sum * 0.96 THEN 2
                WHEN t3.rank >= t4.word_sum * 0.96 THEN 3
                ELSE NULL END word_score
        FROM
        (
            SELECT
            t2.*,
            @rank := (case WHEN t2.a1 <= 0 then 0
                    when @Grp = t2.cat_id then @rank + 1 
                    else 1 end) rank, 
            @Grp:=t2.cat_id cat_id2
            FROM
            (
                SELECT  t1.*,
                LOG10(CASE WHEN t1.impression = 0 OR t1.impression is NULL then 1 ELSE t1.impression END) - 
                LOG10(CASE WHEN t1.impression_goods_num = 0 OR t1.impression_goods_num is NULL then 1 ELSE t1.impression_goods_num END) + 
                LOG10(CASE WHEN t1.order_num = 0 OR t1.order_num is NULL then 1 ELSE t1.order_num END) a1 
                FROM
                (
                    SELECT 
                    w.*,
                    TRUNCATE(w.`order_num` / (CASE WHEN w.`click` = 0 then NULL ELSE w.`click` END),4) conversion,
                    TRUNCATE(w.`order_num`*1000 / (CASE WHEN w.`avg_bid` = 0 then NULL ELSE w.`avg_bid` END),4) cost_performance
                    FROM stat_pdd_word w 
                    LEFT JOIN 
                    pdd_ztc_cat_type_daily c 
                    ON w.cat_id = c.cat_id
                    where 
                    c.cat_type = 2 
                    AND c.date = '{$date}'
                ) t1 ORDER BY t1.cat_id, a1
            )t2, (SELECT @rank := 0,@Grp := 0) a
        )t3 
        LEFT JOIN
        (
            SELECT w.cat_id , count(*) word_sum FROM stat_pdd_word w 
            LEFT JOIN 
            pdd_ztc_cat_type_daily c 
            ON w.cat_id = c.cat_id
            where 
            (LOG10(CASE WHEN w.impression = 0 OR w.impression is NULL then 1 ELSE w.impression END) - 
                    LOG10(CASE WHEN w.impression_goods_num = 0 OR w.impression_goods_num is NULL then 1 ELSE w.impression_goods_num END) + 
                    LOG10(CASE WHEN w.order_num = 0 OR w.order_num is NULL then 1 ELSE w.order_num END)) > 0
            AND c.cat_type = 2 
            AND c.date = '{$date}'
            GROUP BY w.cat_id
        )t4
        on t3.cat_id = t4.cat_id";
$db->query($sql4);
echo date("Y-m-d H:i:s").' sql4 cat_type=2 complete'.PHP_EOL;


echo(date("Y-m-d H:i:s") . " inset_ztc_word_stat  end \r\n");





