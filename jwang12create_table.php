<?php
require("includes/init.php");
for($i = 0; $i < 256; $i++) {
    $erp_ddyun_db_conf = array(
        "host" => "100.65.1.0:32053",
        "user" => "erp",
        "pass" => "Titanerp2020",
        "charset" => "utf8",
        "pconnect" => "1",
    );

    $erp_ddyun_db_conf['name'] = 'erp_' . $i;
    $erp_ddyun_db = ClsPdo::getInstance($erp_ddyun_db_conf);
    echo 'erp_' . $i.'开始更新'.PHP_EOL;
    $sql = "
          
		 ";
    $print_log = $erp_ddyun_db->query($sql);

}
echo '全部更新完成'.PHP_EOL.'撒花完结';