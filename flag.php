<?php

require("includes/init.php");
global $db;
$sql = "
    select 
        f.facility_id, order_flag_map, order_flag_for_report 
    from 
        facility f 
        left join jwang_temp_flag ff on f.facility_id = ff.facility_id 
    where 
        ff.facility_id is null 
";
$facility_list = $db->getAll($sql);
$color = array(
   '#FA541C',
  '#FAAD14',
  '#2DD782',
  '#2A58AD',
  '#800080'
);
foreach ($facility_list as $facility){

    $order_flag_map = json_decode($facility['order_flag_map']);
    $facility_id = $facility['facility_id'];
    $order_flag_for_report = explode(",", $facility['order_flag_for_report']);
    $new_order_flag_for_report = array(0);
    try {
        foreach ($order_flag_map as $flag_id => $flag_name) {
            $sql = "
                insert into facility_shipment_flag(facility_shipment_flag_name,facility_id,color)
                values('{$flag_name}',{$facility_id},'{$color[$flag_id-1]}')
            ";
            echo date("Y-m-d H:i:s") . $sql . "\n";
            $db->query($sql);

            $facility_shipment_flag_id = $db->getOne("SELECT LAST_INSERT_ID()");
            $sql = "
                update multi_goods_shipment set order_flag = {$facility_shipment_flag_id} , order_flag_name = '{$flag_name}'
                where facility_id = {$facility_id} and order_flag = {$flag_id}
            ";
            echo date("Y-m-d H:i:s") . $sql . "\n";
            $db->query($sql);
            if (in_array($flag_id, $order_flag_for_report)) {
                $new_order_flag_for_report[] = $facility_shipment_flag_id;
            }
        }
        $new_order_flag_for_report = implode(",", $new_order_flag_for_report);
        $sql = "
            update 
                facility
            set
                order_flag_for_report = '{$new_order_flag_for_report}'
            where
                facility_id = {$facility_id}
        ";
        echo date("Y-m-d H:i:s") . $sql . "\n";
        $db->query($sql);
        $db->query("insert into jwang_temp_flag values ({$facility_id})");
    } catch(Exception $e) {
        var_dump($e->getMessage());
        echo json_encode($facility).PHP_EOL;
        die("woca");
    }

}