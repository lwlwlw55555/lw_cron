<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");
$time1 = date("Y-m-d H:i:s");
global $db,$db_user;
//文件名
$report_file_name = "erp数据导出";
$this_month = '2020-05-01';
$next_month = '2020-05-07';

$sql = "
    select 
        date_format( '{$this_month}', '%Y-%m') ,
        '麦得多ERP' ,
        t.pay_time , 
        t.order_sn ,
        t.platform_shop_id ,
        if (t.source = 1, '回流', '服务市场标准版') ,
        t.pay_amount , 
        t.effective_start_time ,
        t.effective_end_time ,
        t.effective_days ,
        t.second_jwang / 3600/ 24 ,
        t.second_jwang * qian 
    from (
        select 
            effective_start_time > '{$this_month}' ,
            if (effective_start_time > '{$this_month}' and effective_end_time < '{$next_month}', 
                unix_timestamp(effective_end_time) - unix_timestamp(effective_start_time),
                if(effective_start_time > '{$this_month}', 
                    UNIX_TIMESTAMP('{$next_month}') - UNIX_TIMESTAMP(effective_start_time),
                    if (effective_end_time < '{$next_month}',
                        UNIX_TIMESTAMP(effective_end_time) - UNIX_TIMESTAMP('{$this_month}'),
                        UNIX_TIMESTAMP('{$next_month}') - UNIX_TIMESTAMP('{$this_month}')
                    )
                )
            ) as second_jwang,
            (pay_amount / (UNIX_TIMESTAMP(effective_end_time) - UNIX_TIMESTAMP(effective_start_time))) as qian, 
            p.*
        from 
            platform_pay_order  p 
        having second_jwang > 0 and pay_amount > 0 
    ) as t 
";

$sql = "
select 
 '麦得多ERP' ,
pay_time,
order_sn ,
'ERP标准版' ,
effective_days ,
pay_amount ,
platform_shop_id 
from platform_pay_order
where pay_time >= '2020-04-01' and pay_time <'2020-05-01';

";

$sql = "
select 
    u.facility_id, 
    max(u.created_time),
    count(distinct sd.session_date),
    date(max(po.expire_time)),
    count(distinct sdd.count_date)
from 
    user u 
    inner join pay_oauth po on u.user_id = po.user_id 
    left join session_date sd on u.user_id = sd.user_id and sd.session_date >= '2020-05-01'
    left join shop_data sdd on u.facility_id = sdd.facility_id and sdd.print_count > 0 and sdd.count_date >= '2020-05-01'
group by 
    u.user_id 
";

$sql = "
select 
from 
    shop 
group by 

";
echo $sql.PHP_EOL;
$result = $db_user->getAll($sql);





if (empty($result)) {
    echo(date("Y-m-d H:i:s") . " send report end 数据为空");
    die();
}
$header = array( array_keys($result[0]) );
$erp_report_send_to = array("jwang@titansaas.com","qmcheng@titansaas.com");
$erp_report_send_to = array("jwang@titansaas.com");

send_erp_email($report_file_name, $header, [], $result, 0, $erp_report_send_to);
$cost = strtotime(date("Y-m-d H:i:s")) - strtotime($time1);
echo PHP_EOL;
echo(date("Y-m-d H:i:s") . " send report end cost {$cost}s \r\n");