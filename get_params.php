<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

// http://121.40.113.153/get_params.php?is_search=true
echo json_encode($_REQUEST);
