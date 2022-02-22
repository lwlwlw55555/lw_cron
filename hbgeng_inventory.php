<?php

require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

echo "Statistics data size"."\r\n";

$db_data_size = array();
for ($i = 0 ; $i <= 255; $i++){
    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );

    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);

    $sql = "SELECT CONCAT(table_name) AS 'table_name',
    table_rows AS 'number_of_rows',
    ROUND(data_length/(1024*1024*1024),6) AS 'data_size',
    ROUND(index_length/(1024*1024*1024),6) AS 'index_size' ,
    ROUND((data_length+index_length)/(1024*1024*1024),6) AS'total',
 (DATA_LENGTH+INDEX_LENGTH-TABLE_ROWS*AVG_ROW_LENGTH) /1024/1024/1024 as 'suipian'
FROM information_schema.TABLES
WHERE table_schema LIKE '{$erp_ddyun_db_conf['name']}' order by table_rows desc ";
    $table_data = $erp_ddyun_db->getAll($sql);
    foreach ($table_data as $key1 => $table){
        if($erp_ddyun_db_conf['name'] == 'erp_0'){
            $db_data_size[] = $table;
        }else{
            foreach ($db_data_size as $key2 => $db_size){
                if($db_size['table_name'] == $table['table_name']){
                    $db_data_size[$key2]['number_of_rows'] += $table['number_of_rows'];
                    $db_data_size[$key2]['data_size'] += $table['data_size'];
                    $db_data_size[$key2]['index_size'] += $table['index_size'];
                    $db_data_size[$key2]['total'] += $table['total'];
                    $db_data_size[$key2]['suipian'] += $table['suipian'];
                }
            }
        }
    }
    print($erp_ddyun_db_conf['name']."\r\n");
    echo "\r\n";
}
$header = array(array("Table Name","Number of Rows","Data Size","Index Size","Total","SuiPian"));
send_erp_email('jwang',$header,$key_array, $db_data_size, 0, ['jwang@titansaas.com']);

echo "end".PHP_EOL;