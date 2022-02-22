<?php

echo date("Y-m-d H:i:s")." create table print_log".PHP_EOL;

require ("includes/ClsPdo.php");
$sql = "CREATE TABLE `print_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `shipment_id` bigint(20) unsigned NOT NULL,
  `platform_order_sn` varchar(32) DEFAULT NULL,
  `platform_name` varchar(30) DEFAULT NULL,
  `print_type` enum('TRACKINGNUMBER','WAYBILL') DEFAULT 'TRACKINGNUMBER' COMMENT 'TRACKINGNUMBER 打印面单,WALLBILL 打印发货单,',
  `shipping_id` int(10) unsigned DEFAULT '0',
  `shipping_name` varchar(120) NOT NULL DEFAULT '未选择快递',
  `tracking_number` varchar(50) DEFAULT NULL,
  `province_id` smallint(5) NOT NULL COMMENT '收件人所在省id',
  `province_name` varchar(64) NOT NULL COMMENT '收件人所在省，如浙江省、北京',
  `city_id` smallint(5) NOT NULL COMMENT '收件人所在市id',
  `city_name` varchar(64) NOT NULL COMMENT '收件人所在市，如杭州市、上海市',
  `district_id` smallint(5) NOT NULL COMMENT '收件人所在县id',
  `district_name` varchar(64) NOT NULL COMMENT '收件人所在县（区）',
  `shipping_address` varchar(256) NOT NULL COMMENT '收件人详细地址，不包含省市',
  `receive_name` varchar(64) NOT NULL COMMENT '收件人姓名',
  `mobile` varchar(16) NOT NULL COMMENT '收件人移动电话',
  `print_user` varchar(128) NOT NULL COMMENT '收件人移动电话',
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `facility_id` int(10) unsigned DEFAULT '0' COMMENT '发货地（仓）',
  `warehouse_id` int(10) DEFAULT NULL,
  `shop_id` mediumint(8) NOT NULL,
  `batch_sn` varchar(32) DEFAULT NULL,
  `thermal_type` varchar(30) NOT NULL DEFAULT 'THERMAL',
  `batch_order` int(10) DEFAULT '1',
  `tactics_id` int(10) DEFAULT NULL,
  `tactics_name` varchar(64) DEFAULT NULL,
  `print_data` text,
  `print_source` varchar(30) DEFAULT 'SINGLE' COMMENT 'SINGLE 正常打印 MULTI 追加面单 SHIPPED 已发货打印 MANU 手工订单 EXCEPTION 异常订单打印 PRINT_LOG打印日志',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `shipment_id` (`shipment_id`) USING BTREE,
  KEY `platform_order_sn` (`platform_order_sn`) USING BTREE,
  KEY `shipping_id` (`shipping_id`) USING BTREE,
  KEY `tracking_number` (`tracking_number`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `facility_id` (`facility_id`),
  KEY `created_time` (`created_time`),
  KEY `receive_name` (`receive_name`),
  KEY `province_id` (`province_id`),
  KEY `city_id` (`city_id`),
  KEY `district_id` (`district_id`),
  KEY `batch_sn` (`batch_sn`) USING BTREE,
  KEY `shop_id` (`shop_id`),
  KEY `thermal_type` (`thermal_type`) USING BTREE,
  KEY `platform_name` (`platform_name`),
  KEY `tactics_id` (`tactics_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `print_type` (`print_type`)
) ENGINE=InnoDB ";

$erp_ddyun_history_db_conf = array(
    "host" => "100.65.2.183:32058",
    "user" => "erp",
    "pass" => "Titanerp2020",
    "charset" => "utf8",
    "pconnect" => "1",
);
$auto_increment = 1000000000;
for ($i = 0; $i <= 255 ; $i++){
    $erp_ddyun_history_db_conf['name'] = 'erp_'.$i;
    $erp_ddyun_history_db = ClsPdo::getInstance($erp_ddyun_history_db_conf);
    $sql .= " AUTO_INCREMENT={$auto_increment} DEFAULT CHARSET=utf8 COMMENT='打印日志表'";
//    $erp_ddyun_history_db->query($sql);
    $auto_increment += 1000000000;
    echo $erp_ddyun_history_db_conf['name']." has created table print_log".$auto_increment.PHP_EOL;
}