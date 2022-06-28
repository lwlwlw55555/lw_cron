<?php
require("includes/init.php");
require(ROOT_PATH . "includes/erp_report/erp_report_function.php");

require("Services/LeqeeDbService.php");
use Services\LeqeeDbService;

// global $db;

// $sql = "select shop_id,shop_name,shop_nick from omssync.shop where is_delete = 'N' and platform = 'TAOBAO'";

// $shops = $db->getAll($sql);

// var_export($shops);

// echo json_encode(['code'=>0,'data'=>$shops]);


// $dbs = LeqeeDbService::getLeqeeDbs();

// $_REQUEST['start_date'] = '2022-06-24 15:00:40';
// $_REQUEST['end_date'] = '2022-06-24 15:06:40';

if (empty($_REQUEST) || !array_key_exists('start_date', $_REQUEST) || !array_key_exists('end_date', $_REQUEST)) {
	echo json_encode(['code'=>1,'data'=>'error params']);
	//!!!! 用echo + return 不要直接用return !!!!
	return;
}


            

$oms_dbs = LeqeeDbService::getStropsDbsGroup(['oms-polar','oms-mz-polar-prod','oms-v2-mz2-polar','oms-kx-polar-prod']);

$sync_dbs = LeqeeDbService::getStropsDbs('oms_v2_sync');


// var_dump($sync_dbs);

$sql = "select count(1) as c from omssync.sync_order_info
        where 
        api_platform = 'TAOBAO' and last_update_time >= '{$_REQUEST['start_date']}' and last_update_time < '{$_REQUEST['end_date']}' and create_time >= '{$_REQUEST['start_date']}' and create_time < '{$_REQUEST['end_date']}'";
if (isset($_REQUEST['shop_nick']) && !empty($_REQUEST['shop_nick'])) {
	$sql .= "and shop_nick = '{$_REQUEST['shop_nick']}'";
}

$sync_count = 0;
foreach ($sync_dbs as $sync_db) {
	$res = LeqeeDbService::query($sync_db,$sql);
	$sync_count+=$res[0]['c'];
}




$sql = "select count(1) as c from oms.origin_order
        where 
        platform = 'TAOBAO' and last_update_time >= '{$_REQUEST['start_date']}' and last_update_time < '{$_REQUEST['end_date']}' and create_time >= '{$_REQUEST['start_date']}' and create_time < '{$_REQUEST['end_date']}'";
if (isset($_REQUEST['shop_id']) && !empty($_REQUEST['shop_id'])) {
	$sql .= "and shop_id = '{$_REQUEST['shop_id']}'";
}
$origin_count = 0;
foreach ($oms_dbs as $oms_db) {
	$res = LeqeeDbService::query($oms_db,$sql);
	$origin_count+=$res[0]['c'];
}


$sql = "select count(1) as c from oms.order_info oi inner join oms.shop s on oi.shop_id = s.shop_id
        where
         and s.platform = 'TAOBAO' and oi.last_update_time >= '{$_REQUEST['start_date']}' and oi.last_update_time < '{$_REQUEST['end_date']}' and oi.create_time >= '{$_REQUEST['start_date']}' and oi.create_time < '{$_REQUEST['end_date']}'";
if (isset($_REQUEST['shop_id']) && !empty($_REQUEST['shop_id'])) {
	$sql .= "and oi.shop_id = '{$_REQUEST['shop_id']}'";
}
$oms_count = 0;
foreach ($oms_dbs as $oms_db) {
	$res = LeqeeDbService::query($oms_db,$sql);
	$oms_count+=$res[0]['c'];
}

echo json_encode(['code'=>0,'data'=>['sync'=>$sync_count,'origin'=>$origin_count,'oms'=>$oms_count]]);
return;


// {"oms-polar":{"db":"leqee","databaseId":"21","databaseName":"oms-polar"},"oms_v2_main_slave_2":{"db":"leqee","databaseId":"22","databaseName":"oms_v2_main_slave_2"},"oms_v2_sync":{"db":"leqee","databaseId":"23","databaseName":"oms_v2_sync"},"oms-v2-xxl-job":{"db":"leqee","databaseId":"36","databaseName":"oms-v2-xxl-job"},"oms_v2_pressure_test":{"db":"leqee","databaseId":"45","databaseName":"oms_v2_pressure_test"},"oms-v2-wmsroute-prod":{"db":"leqee","databaseId":"52","databaseName":"oms-v2-wmsroute-prod"},"oms-v2-analyze-prod":{"db":"leqee","databaseId":"55","databaseName":"oms-v2-analyze-prod"},"oms-v2-main-slave-big":{"db":"leqee","databaseId":"56","databaseName":"oms-v2-main-slave-big"},"oms-v2-scm-prod":{"db":"leqee","databaseId":"57","databaseName":"oms-v2-scm-prod"},"oms-v2-shop-prod":{"db":"leqee","databaseId":"58","databaseName":"oms-v2-shop-prod"},"oms-v2-pt":{"db":"leqee","databaseId":"64","databaseName":"oms-v2-pt"},"oms_v2_preprod":{"db":"leqee","databaseId":"66","databaseName":"oms_v2_preprod"},"omsbase-prod":{"db":"leqee","databaseId":"77","databaseName":"omsbase-prod"},"oms-v2-test-3":{"db":"leqee","databaseId":"80","databaseName":"oms-v2-test-3"},"oms-kx-polar-prod":{"db":"leqee","databaseId":"81","databaseName":"oms-kx-polar-prod"},"oms-mz-polar-prod":{"db":"leqee","databaseId":"82","databaseName":"oms-mz-polar-prod"},"oms-kx-analyze-prod":{"db":"leqee","databaseId":"83","databaseName":"oms-kx-analyze-prod"},"oms-mz-analyze-prod":{"db":"leqee","databaseId":"84","databaseName":"oms-mz-analyze-prod"},"oms-kx-xxl-prod":{"db":"leqee","databaseId":"85","databaseName":"oms-kx-xxl-prod"},"oms-mz-xxl-prod":{"db":"leqee","databaseId":"86","databaseName":"oms-mz-xxl-prod"},"oms-v2-mz2-polar":{"db":"leqee","databaseId":"90","databaseName":"oms-v2-mz2-polar"},"oms-mz2-xxl-prod":{"db":"leqee","databaseId":"91","databaseName":"oms-mz2-xxl-prod"},"oms-mz2-analyze-prod":{"db":"leqee","databaseId":"93","databaseName":"oms-mz2-analyze-prod"},"oms-common-prod":{"db":"leqee","databaseId":"97","databaseName":"oms-common-prod"},"oms-kx-adb3":{"db":"leqee","databaseId":"100","databaseName":"oms-kx-adb3"},"oms-mz-adb3":{"db":"leqee","databaseId":"101","databaseName":"oms-mz-adb3"},"oms-mz2-adb3":{"db":"leqee","databaseId":"102","databaseName":"oms-mz2-adb3"},"pim-shop-prod":{"db":"leqee","databaseId":"104","databaseName":"pim-shop-prod"},"gyc-rds-1":{"db":"gyc","databaseId":"2","databaseName":"gyc-rds-1"},"gyc-oms-1":{"db":"gyc","databaseId":"5","databaseName":"gyc-oms-1"},"gyc-oms-sync-1":{"db":"gyc","databaseId":"6","databaseName":"gyc-oms-sync-1"},"gyc-oms-open-1":{"db":"gyc","databaseId":"7","databaseName":"gyc-oms-open-1"},"gyc-oms-xxl-job":{"db":"gyc","databaseId":"17","databaseName":"gyc-oms-xxl-job"},"gyc-oms-adb":{"db":"gyc","databaseId":"21","databaseName":"gyc-oms-adb"},"PerfectDiary-OMS":{"db":"gyc","databaseId":"22","databaseName":"PerfectDiary-OMS"},"PerfectDiary-Yiran-XXL-Job":{"db":"gyc","databaseId":"31","databaseName":"PerfectDiary-Yiran-XXL-Job"},"PerfectDiary-Yiran-OMS-Slave":{"db":"gyc","databaseId":"32","databaseName":"PerfectDiary-Yiran-OMS-Slave"},"PerfectDiary-Yiran-OMS":{"db":"gyc","databaseId":"33","databaseName":"PerfectDiary-Yiran-OMS"},"PerfectDiary-Yiran-OMS-Sync":{"db":"gyc","databaseId":"34","databaseName":"PerfectDiary-Yiran-OMS-Sync"},"gyc-oms-analyze-prod":{"db":"gyc","databaseId":"35","databaseName":"gyc-oms-analyze-prod"},"gyc-oms-1-slave":{"db":"gyc","databaseId":"36","databaseName":"gyc-oms-1-slave"},"ruyun-oms-xxl-job":{"db":"gyc","databaseId":"38","databaseName":"ruyun-oms-xxl-job"},"ruyun-oms-sync":{"db":"gyc","databaseId":"40","databaseName":"ruyun-oms-sync"},"ruyun-oms":{"db":"gyc","databaseId":"41","databaseName":"ruyun-oms"},"gyc-oms-test":{"db":"gyc","databaseId":"47","databaseName":"gyc-oms-test"},"wms-lite-express-prod":{"db":"gyc","databaseId":"48","databaseName":"wms-lite-express-prod"},"wms-lite-prod":{"db":"gyc","databaseId":"49","databaseName":"wms-lite-prod"},"ruyun-slave-oms":{"db":"gyc","databaseId":"50","databaseName":"ruyun-slave-oms"},"CTF-OMS":{"db":"gyc","databaseId":"52","databaseName":"CTF-OMS"},"CTF-OMS-SYNC":{"db":"gyc","databaseId":"53","databaseName":"CTF-OMS-SYNC"},"ctf-wms-lite-express":{"db":"gyc","databaseId":"54","databaseName":"ctf-wms-lite-express"}}

// echo json_encode($dbs);