<?php
require("includes/init.php");
echo date("Y-m-d H:i:s")." oms_delete_shipment_package_extension begin".PHP_EOL;

global $oms_db;
$oms_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddoms_0"
);
$oms_db = ClsPdo::getInstance($oms_db_conf);
$delete_count = 0;
for($i = 0; $i < 2; $i++){

    $oms_db_conf['name'] = 'mddoms_'.$i;
    $oms_db = ClsPdo::getInstance($oms_db_conf);

    $min_id = $oms_db->getOne(" select min(shipment_package_id) from shipment_package_extension ");
    $max_id = $oms_db->getOne(" select max(shipment_package_id) from shipment_package where created_time <= date_sub(current_date(), interval 30 day) ");

    while ($min_id < $max_id) {
        $start_id = $min_id;
        $end_id = $min_id + 1000;
        $ids = $oms_db->getCol(" select shipment_package_id from shipment_package_extension where shipment_package_id >= {$start_id} and shipment_package_id <= {$end_id} ");
        if ($ids) {
            $delete_count += count($ids);
            $ids_str = implode(",", $ids);
            $delete_sql = " delete from shipment_package_extension where shipment_package_id in ({$ids_str}) ";
            $oms_db->query($delete_sql);
        }
        $min_id = $end_id;
        echo "[]".$oms_db_conf['name']."   ".date("Y-m-d H:i:s ")." start_id={$min_id}  end_id={$end_id}  delete_count={$delete_count}".PHP_EOL;
    }
    echo date("Y-m-d H:i:s")." oms_delete_shipment_package_extension ".$oms_db_conf['name']." end ".PHP_EOL;
}
echo date("Y-m-d H:i:s")." oms_delete_shipment_package_extension end ".PHP_EOL;


