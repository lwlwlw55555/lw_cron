<?php
require("includes/init.php");
global $db_user, $db;
$checkDate = date("Y-m-d",strtotime("-1 day"));
$endDate = date("Y-m-d");
echo date("Y-m-d H:i:s")." start check share_mailnos check_date:".$checkDate.PHP_EOL;
$sql = "
    select 
        os.*,
        u.user_name
    from 
        oauth_share os 
        inner join user u on u.facility_id = os.to_facility_id
    where 
        used_count > 0 
";
$oauth_share_list = $db_user->getAll($sql);

foreach ($oauth_share_list as $oauth_share){

    selectRdsByFacilityId($oauth_share['facility_id']);
    //检查 oauth_share_mailnos 缺少的数据
    $from_db = "erp_".($oauth_share['facility_id'] % 256 );
    $to_db = "erp_".($oauth_share['to_facility_id'] % 256 );
    $sql = "
        insert into
            {$from_db}.oauth_share_mailnos
            (
                oauth_share_id,
                facility_id,
                to_facility_id,
                shipping_id,
                pdd_branch_code,
                pdd_branch_name,
                user_name,
                tracking_number,
                created_time
            )  
            select
                '{$oauth_share['oauth_share_id']}',
                '{$oauth_share['facility_id']}',
                '{$oauth_share['to_facility_id']}',
                '{$oauth_share['shipping_id']}',
                '{$oauth_share['pdd_branch_code']}',
                '{$oauth_share['pdd_branch_name']}',
                '{$oauth_share['user_name']}',
                m.tracking_number,
                m.created_time
        from
            {$to_db}.mailnos m 
            left join {$from_db}.oauth_share_mailnos om on om.oauth_share_id = m.oauth_share_id and om.tracking_number = m.tracking_number
        where 
            m.facility_id = {$oauth_share['to_facility_id']} 
            and m.oauth_share_id = {$oauth_share['oauth_share_id']}
            and m.created_time >= '{$checkDate}'
            and m.created_time < '{$endDate}'
            and om.tracking_number is null
            
    ";
    echo "insert oauth_share_mailnos sql =  ".$sql.PHP_EOL;
    $db->query($sql);

    // 检查 oauth_share 的使用数量和实际已打印数量是否一致
    echo $oauth_share['oauth_share_id']." oauth_share used_count ".$oauth_share['used_count'].PHP_EOL;


    $sql = "select count(1) from oauth_share_mailnos where facility_id = {$oauth_share['facility_id']} and oauth_share_id = {$oauth_share['oauth_share_id']} ";
    $print_count = $db->getOne($sql);
    echo $oauth_share['oauth_share_id']." mailnos  print_count  ".$print_count.PHP_EOL;

    if ($oauth_share['used_count'] != $print_count) {
        $sql = "update oauth_share set used_count = {$print_count} where oauth_share_id = {$oauth_share['oauth_share_id']} ";
        echo $sql.PHP_EOL;
        $db_user->query($sql);
    }
}
echo date("Y-m-d H:i:s")." end check share_mailnos check_date:".$checkDate.PHP_EOL;

