<?php
require("includes/init.php");

$url = $master_config['expressapi_url']."job/checkOrderIncrementJobs";
$response = http_get_data($url);
echo(date("Y-m-d H:i:s") . " url:{$url} response:{$response} \r\n");
