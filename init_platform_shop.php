<?php

require("includes/init.php");

global  $db_user, $db;

$create_table_sql = "
    CREATE TABLE `platform_shop` (
      `platform_shop_id` char(32) NOT NULL COMMENT '平台店铺id 拼多多 mall_id',
      `is_outer_oauth` tinyint(1) DEFAULT '0' COMMENT '是否是外部授权',
      `facility_ids` VARCHAR(512) DEFAULT NULL COMMENT '使用到店铺授权的用户',   
      `created_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
      `last_updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  
      PRIMARY KEY (`platform_shop_id`,`is_outer_oauth`),
      KEY `created_time` (`created_time`),
      KEY `last_updated_time` (`last_updated_time`)  
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='平台店铺表'
";
$db_user->query($create_table_sql);

$insert_sql = "
    insert IGNORE into 
        platform_shop
        (
          platform_shop_id,          
          facility_ids
        ) select
            platform_shop_id,            
            facility_id
        from
            shop_oauth
        where 
            facility_id > 0 
";
$db_user->query($insert_sql);


for ($i = 0; $i < 256 ; $i ++){
    selectRds('piece_1', "erp_".$i );
    echo "start "."erp_".$i.PHP_EOL;
    $sql = "
        select 
            o.facility_id,
            o.platform_shop_id,
            if(o.platform_name = 'pinduoduo', 0, 1 ) as is_outer_oauth
        from
            oauth o 
            left join shop s on s.facility_id = o.facility_id and s.platform_shop_id = o.platform_user_id
        where 
            s.shop_id is null
    ";
    $oauth_list = $db->getAll($sql);
    foreach ($oauth_list as $item){
        $sql = "
            insert into platform_shop (platform_shop_id,  is_outer_oauth, facility_ids) 
            values ('{$item['platform_shop_id']}', '{$item['is_outer_oauth']}', '{$item['facility_id']}') 
            ON DUPLICATE KEY UPDATE  facility_ids = concat(facility_ids, ',{$item['facility_id']}') 
        ";
        echo $sql.PHP_EOL;
        $db_user->query($sql);
    }
}
echo " end success";
