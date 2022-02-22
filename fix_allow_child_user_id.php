<?php
//require("includes/init.php");
require ("includes/ClsPdo.php");

$mysql_user_conf = array(
        "host" => "rm-bp1jbqrs28070zu23mo.mysql.rds.aliyuncs.com:3306",
        "user" => "erp_test",
        "pass" => "zgsdi67f8PhQ",
        "charset" => "utf8",
        "pconnect" => "1",
);
$mysql_user_conf['name'] = "erp_user_test_new";
$limit = 0;
echo "fix allow child user id begin".date('Y-m-d H:i:s');
do{
    $user_db = ClsPdo::getInstance($mysql_user_conf);
    $user_list = $user_db->getAll("select db, facility_id, child_user_id 
                    from user u 
                        inner join child_user cu 
                        on u.user_id = cu.user_id
                    where 
                        cu.role_id = 0 limit {$limit}, 1000");
    $limit += 1000;
    foreach ($user_list as $user){
        $sql = "update {$user['db']}.shop set login_child_user_id = {$user['child_user_id']} where default_facility_id = {$user['facility_id']}";
        $user_db->query($sql);
        echo "facility_id: ".$user['facility_id']. " finish  ";
        print($sql."\r\n");
    }
}while(count($user_list) == 1000);

echo "fix allow child user id end \r\n";