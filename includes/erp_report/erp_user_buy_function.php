<?php
/* 查询用户交易信息 */
/**
 * 有效试用用户数
 * 有效用户中 支付总金额等于0的用户
 */

function getEffectiveAndNotBuyUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            COUNT(DISTINCT tt.user_id) 
        from
            (
                select 
                    user_id
                from 
                    pay_order
                where
                    pay_status = 'PS_PAYED'
                    and effective_start_time < '{$end_date}'
                    and effective_end_time >= '{$start_date}'
                group by 
                    user_id
            ) as tt
            left join pay_order p on p.user_id = tt.user_id AND p.pay_amount > 0 AND p.pay_status = 'PS_PAYED' and pay_time < '{$end_date}'
        where 
            p.user_id is null     
	";
    echo 'getEffectiveAndNotBuyUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取时间段内付费用户数
 */
function getBuyUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(distinct user_id)
        from
            pay_order 
        where
            pay_status = 'PS_PAYED'
            AND pay_amount > 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}'
        
    ";
    echo 'getBuyUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取时间段内转化用户数
 * 时间段内支付金额＞0的用户， 之前有购买试用且没有购买过 正式产品的用户
 */
function getTransformUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(distinct u.user_id)
        from
            user u
            inner join pay_order po1 on po1.user_id = u.user_id 
            inner join pay_order po2 on po2.user_id = u.user_id AND po2.pay_amount = 0 and po2.pay_status = 'PS_PAYED' and po2.pay_time < po1.pay_time
            left  join pay_order po3 on po3.user_id = u.user_id AND po3.pay_amount > 0 and po3.pay_status = 'PS_PAYED' and po3.pay_time < po1.pay_time
        where 
            po1.pay_time >= '{$start_date}'
            AND po1.pay_time < '{$end_date}'
            AND po1.pay_amount > 0
            AND po1.pay_status = 'PS_PAYED'
            AND po3.order_id is null
    ";
    echo 'getTransformUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取续费用户数
 *  有一笔订单的到期时间在 时间段内
 *  并且 有一另笔订单的生效时间在时间段内
 */

function getRenewUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(distinct po.user_id)
        from
            (    
            select
                max(order_sn) as order_sn,
                user_id
            from
                pay_order
            where 
                pay_status = 'PS_PAYED'
                and effective_end_time >= '{$start_date}'
                and effective_end_time < '{$end_date}'
            group by
                user_id
            ) as t 
            inner join pay_order po on po.user_id = t.user_id and po.order_sn != t.order_sn
        where 
            po.pay_status = 'PS_PAYED'
            and po.effective_start_time >= '{$start_date}'
            and po.effective_start_time < '{$end_date}'   
    ";
    echo 'getRenewUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 *获取复购用户数
 * 有一笔支付金额大于 0 的订单 在时间段内
 * 同时存在另一笔支付金额大于0 的订单
 */

function getRePurchaseUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(distinct u.user_id)
        from
            user u
            inner join pay_order po1 on po1.user_id = u.user_id  
            inner join pay_order po2 on po2.user_id = u.user_id 
        where 
            po1.pay_time >= '{$start_date}'
            AND po1.pay_time < '{$end_date}'
            AND po1.pay_amount > 0
            AND po1.pay_status = 'PS_PAYED'
            AND po2.pay_amount > 0 
            and po2.pay_status = 'PS_PAYED' 
            and po2.order_sn != po1.order_sn 
            and po2.pay_time < '{$end_date}'
    ";
    echo 'getRePurchaseUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取到期用户数
 * 有一笔订单 到期时间在 时间段内
 */

function getExpiredUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select 
            count(distinct user_id)
        from 
            pay_order
        where
            pay_status = 'PS_PAYED'
            and effective_end_time < '{$end_date}'
            and effective_end_time >= '{$start_date}'         
    ";
    echo 'getExpiredUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取订购笔数
 * 免费笔数+付费笔数
 */
function getPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";

    echo 'getPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取免费笔数
 *
 */
function getFreePayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_amount = 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getFreePayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取付费笔数
 */
function getPaidPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_amount > 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";

    echo 'getPaidPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 收入金额
 */
function getPayOrderAmount($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            sum(pay_amount)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getPayOrderAmount'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}
/**
 * 订购一个月订单笔数
 */

function getBuyOneMonthPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND goods_id = 2
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getBuyOneMonthPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 订购三个月订单笔数
 */

function getBuyThreeMonthPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND goods_id = 3
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getBuyThreeMonthPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 订购六个月订单笔数
 */

function getBuySixMonthPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND goods_id = 4
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getBuySixMonthPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 订购12个月订单笔数
 */

function getBuyOneYearPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND goods_id = 5
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getBuyOneYearPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 退款笔数
 */
function getRefundOrderFromPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            pay_order po 
        inner join shop_pay_order spo on po.user_id = spo.user_id   
        where
            po.pay_status  = 'PS_PAYED'
            AND spo.order_type = 'PAY_OAUTH_INIT'
            AND po.refund_status = 'REFUNDED'
            AND po.refund_apply_time >= '{$start_date}'
            AND po.refund_apply_time < '{$end_date}' 
    ";
    echo 'getRefundOrderFromPayOrder'.PHP_EOL.$sql.PHP_EOL;
    $count_shop = $db_user->getOne($sql);
    $sql = "
        select
            count(*)
        from
            platform_pay_order
        where
            pay_status  = 'PS_PAYED'
            AND refund_status != 'COMMON'
            AND refund_apply_time >= '{$start_date}'
            AND refund_apply_time < '{$end_date}' 
    ";
    echo 'getRefundOrderFromPayOrder'.PHP_EOL.$sql.PHP_EOL;
    $count_platform = $db_user->getOne($sql);
    return $count_shop + $count_platform;
    
}

/**
 * 退款金额
 */

function getRefundAmountFromPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            sum(refund_amount)
        from
            pay_order
        where
            pay_status  = 'PS_PAYED'
            AND refund_status = 'REFUNDED'
            AND refund_apply_time >= '{$start_date}'
            AND refund_apply_time < '{$end_date}' 
    ";
    echo 'getRefundAmountFromPayOrder'.PHP_EOL.$sql.PHP_EOL;
    $refund_amount = $db_user->getOne($sql);
    $sql = "
        select
             sum(refund_amount)
        from
            platform_pay_order
        where
            pay_status  = 'PS_PAYED'
            AND refund_status != 'COMMON'
            AND refund_apply_time >= '{$start_date}'
            AND refund_apply_time < '{$end_date}' 
    ";
    echo 'getRefundOrderFromPayOrder'.PHP_EOL.$sql.PHP_EOL;
    $refund_amount_platform = $db_user->getOne($sql);
    return $refund_amount + $refund_amount_platform;
}

/**
 * 获取时间段内新增付费用户数
 * 时间段内买的订单是用户第一笔付费订单
 */
function getNewBuyUser($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(distinct po1.user_id)
        from
            pay_order po1 
            left join pay_order po2 on po2.user_id = po1.user_id and po2.order_sn != po1.order_sn and po2.pay_time < '{$start_date}' and po2.pay_status = 'PS_PAYED'AND po2.pay_amount > 0
        where
            po1.pay_status = 'PS_PAYED'
            AND po1.pay_amount > 0
            AND po1.pay_time >= '{$start_date}'
            AND po1.pay_time < '{$end_date}'
            AND po2.order_sn is null
        
    ";
    echo 'getNewBuyUser'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取系统免费笔数
 *
 */
function getFreeShopPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            shop_pay_order
        where
            order_type = 'FREEPAY_BY_SYSTEM'
            AND pay_amount = 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getFreeShopPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取系统付费笔数
 *
 */
function getPaidShopPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            shop_pay_order
        where
            pay_status  = 'PS_PAYED'
            AND order_type = 'ALIPAY'
            AND pay_amount > 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getPaidShopPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取系统订购金额
 *
 */
function getShopPayOrderAmount($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            SUM(pay_amount)
        from
            shop_pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getShopPayOrderAmount'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取服务市场免费笔数
 *
 */
function getFreePlatformPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            platform_pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_amount = 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getFreePlatformPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取服务市场付费笔数
 *
 */
function getPaidPlatformPayOrder($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(*)
        from
            platform_pay_order
        where
            pay_status  = 'PS_PAYED'
            AND pay_amount > 0
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getPaidPlatformPayOrder'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取服务市场订购金额
 *
 */
function getPlatformPayOrderAmount($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            SUM(pay_amount)
        from
            platform_pay_order
        where
           pay_status  = 'PS_PAYED'
            AND pay_time >= '{$start_date}'
            AND pay_time < '{$end_date}' 
    ";
    echo 'getPlatformPayOrderAmount'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 付费生效店铺数 
 */
function getEffectivePaidShop($start_date,$end_date){
    global $db_user;
    $sql = "
		select 
            count(DISTINCT spo.platform_shop_id)
        from 
            shop_pay_order spo 
            left join platform_pay_order ppo on ppo.platform_pay_order_id = spo.platform_pay_order_id
        where
            ((ppo.pay_status = 'PS_PAYED'
            and ppo.pay_amount > 0) or (spo.order_type in ('ALIPAY','PAY_OAUTH_INIT')and  spo.pay_status ='PS_PAYED') )
            and spo.effective_end_time >= '{$start_date}'
            and spo.effective_start_time < '{$end_date}'   
	";
    echo 'getEffectivePaidShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 本周付费店铺数中 付费订单超过1个的店铺数
 */
function getRePaidTwoShopRate($start_date,$end_date){
    global $db_user;
    $sql = " select 
                DISTINCT spo.platform_shop_id
            from 
                shop_pay_order spo 
                left join platform_pay_order ppo on ppo.platform_pay_order_id = spo.platform_pay_order_id
            where
                ((ppo.pay_status = 'PS_PAYED'
                and ppo.pay_amount > 0) or (spo.order_type ='ALIPAY' and  spo.pay_status ='PS_PAYED'))    
                and spo.pay_time < '{$end_date}'
                and spo.pay_time >= '{$start_date}' ";
    $list = $db_user->getAll($sql);
    $count = count($list);
    $platform_shop_id_str = implode(",",array_column($list,'platform_shop_id'));
    $sql = "
        select 
              spo.platform_shop_id
        from
            shop_pay_order spo 
            left join platform_pay_order ppo on ppo.platform_pay_order_id = spo.platform_pay_order_id
        where   spo.platform_shop_id in ({$platform_shop_id_str}) and ((ppo.pay_status = 'PS_PAYED'
                and ppo.pay_amount > 0) or (spo.order_type ='ALIPAY' and  spo.pay_status ='PS_PAYED'))       
        group by  spo.platform_shop_id   
        having count(*)>1
	";
    echo 'getRePaidShop'.PHP_EOL.$sql.PHP_EOL;
    return round(count($db_user->getAll($sql)) /$count *100,2)."%";
}

/**
 * 存在过试用订单店铺中有付费的店铺
 */
function getPaidHaveFreeOrderShop ($start_date,$end_date){
    global $db_user;
    $sql = "
        select count(DISTINCT ppo.platform_shop_id)
        from shop_pay_order spo 
            inner join platform_pay_order ppo on ppo.platform_pay_order_id = spo.platform_pay_order_id
            inner join (
            select 
                DISTINCT (spo.platform_shop_id)
            from 
                shop_pay_order spo 
                inner join platform_pay_order ppo on ppo.platform_pay_order_id = spo.platform_pay_order_id
            where
                ppo.pay_status = 'PS_PAYED'
                and ppo.effective_days = 15
                and ppo.pay_amount = 0
                and spo.pay_time < '{$end_date}' ) spp on spp.platform_shop_id = ppo.platform_shop_id
		where ppo.pay_status = 'PS_PAYED'
            and ppo.pay_amount > 0
            and spo.effective_start_time < '{$end_date}'
            and spo.effective_end_time >= '{$start_date}' 
	";
    echo 'getPaidHaveFreeOrderShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 存在过试用订单店铺数
 */
function getHaveFreeOrderShop($end_date){
    global $db_user;
    $sql = "
		select 
            count(DISTINCT spo.platform_shop_id)
        from 
            shop_pay_order spo 
            inner join platform_pay_order ppo on ppo.platform_pay_order_id = spo.platform_pay_order_id
        where
            ppo.pay_status = 'PS_PAYED'
            and ppo.effective_days = 15
            and ppo.pay_amount = 0
            and spo.pay_time < '{$end_date}'  
	";
    echo 'getHaveFreeOrderShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}

/**
 * 获取续费店铺时
 *  有一笔订单的到期时间在 时间段内
 *  并且 有一另笔订单的生效时间在时间段内
 */

function getRePaidShop($start_date,$end_date){
    global $db_user;
    $sql = "
        select
            count(distinct spo.platform_shop_id)
        from
            (    
            select
                max(order_sn) as order_sn,
                platform_shop_id
            from
                shop_pay_order
            where 
                pay_status = 'PS_PAYED'
                and effective_end_time >= '{$start_date}'
                and effective_end_time < '{$end_date}'
            group by
                platform_shop_id
            ) as t 
            inner join shop_pay_order spo on spo.platform_shop_id = t.platform_shop_id and spo.order_sn != t.order_sn
        where 
            spo.pay_status = 'PS_PAYED'
            and spo.effective_start_time >= '{$start_date}'
            and spo.effective_start_time < '{$end_date}'   
    ";
    echo 'getRePaidShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}
/**
 * 获取到期店铺数
 * 有一笔订单 到期时间在 时间段内
 */

function getExpiredShop($start_date,$end_date){
    global $db_user;
    $sql = "
        select 
            count(distinct platform_shop_id)
        from 
            shop_pay_order
        where
            pay_status = 'PS_PAYED'
            and effective_end_time < '{$end_date}'
            and effective_end_time >= '{$start_date}'         
    ";
    echo 'getExpiredShop'.PHP_EOL.$sql.PHP_EOL;
    return $db_user->getOne($sql);
}
