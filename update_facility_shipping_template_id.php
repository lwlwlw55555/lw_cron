<?php
require("includes/init.php");
for($i = 0; $i < 256; $i++) {
    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
        'name' => 'erp_' . $i,
    );
    $erp_history_db_conf = array(
        "host" => "100.65.2.183:32058",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
        'name' => 'erp_' . $i,
    );
    
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    $erp_history_db = ClsPdo::getInstance($erp_history_db_conf);
    echo 'erp_' . $i.'开始查询模板'.PHP_EOL;
    $sql = "
		select  id as facility_shipping_template_id,warehouse_id,shipping_id
		from facility_shipping_template  ";
    $facility_shipping_template = $erp_ddyun_db->getAll($sql);

    
    foreach ($facility_shipping_template as $item){
        $sql_select1 = "
                                select tracking_number
                                from  mailnos
                                where  warehouse_id = {$item['warehouse_id']} and shipping_id = {$item['shipping_id']} and facility_shipping_template_id is null ";
        $sql_select2 = "
                                select tracking_number
                                from  print_log
                                where  warehouse_id = {$item['warehouse_id']} and shipping_id = {$item['shipping_id']} and facility_shipping_template_id is null ";
        echo  $sql_select1 .PHP_EOL;
        $mailnos1 = $erp_ddyun_db->getAll($sql_select1);
        $mailnos2 = $erp_history_db->getAll($sql_select1);
        $mailnos3 = $erp_ddyun_db->getAll($sql_select2);
        $mailnos4 = $erp_history_db->getAll($sql_select2);
        $mailnos_list = array_chunk(array_merge($mailnos1,$mailnos2,$mailnos3,$mailnos4),1000);
        foreach ($mailnos_list as $mailnos){
            $tracking_numbers = implode("','",array_column($mailnos,'tracking_number'));
            $sql_multi = "
		                UPDATE multi_goods_shipment 
		                set  facility_shipping_template_id = {$item['facility_shipping_template_id']} , last_updated_time  = last_updated_time
		                where  tracking_number in ('{$tracking_numbers}') ";
            $erp_ddyun_db->query($sql_multi);
            $erp_history_db->query($sql_multi);
            $sql_print_log = "
		                UPDATE print_log 
		                set  facility_shipping_template_id = {$item['facility_shipping_template_id']} 
		                where  tracking_number in ('{$tracking_numbers}') ";
            $erp_ddyun_db->query($sql_print_log);
            $erp_history_db->query($sql_print_log);
            echo  $sql_print_log.PHP_EOL;
            $sql_mailnos = "
		                UPDATE mailnos 
		                set  facility_shipping_template_id = {$item['facility_shipping_template_id']} , last_update_time = last_update_time
		                where  tracking_number in ('{$tracking_numbers}') ";
            $erp_ddyun_db->query($sql_mailnos);
            $erp_history_db->query($sql_mailnos);
        }
        echo  'erp_' . $i.'  '.$item['facility_shipping_template_id'] .PHP_EOL;
    }
    echo 'erp_' . $i.'模板id更新完成'.PHP_EOL;
    
}
echo '全部更新完成'.PHP_EOL.'撒花完结';