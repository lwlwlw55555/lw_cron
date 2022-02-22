<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
include 'request/LogisticsOnlineSendRequest.php';
use Models\ShopModel;
use Models\OrderModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
include 'request/OrderNumberListGetRequest.php';
include 'PddClient.php';

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

global $oms_user_db;
$oms_user_db_conf = array(
    "host" => "100.65.1.202:32001",
    "user" => "mddomsapi",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomsuser"
);
$oms_user = ClsPdo::getInstance($oms_user_db_conf);

global $oms_sync_db;
$oms_sync_db_conf = array(
    "host" => "100.65.2.110:32057",
    "user" => "mddomsapi_sync",
    "pass" => "123JoisnD0",
    "charset" => "utf8",
    "pconnect" => "1",
    "name" => "mddomssync"
);
$oms_sync_db = ClsPdo::getInstance($oms_sync_db_conf);



$sql = "select distinct oo.platform_order_sn,pm.platform_shipping_id,o.tracking_number,s.access_token from order_info o
inner join order_goods og on og.order_id = o.order_id
inner join origin_order oo on oo.origin_order_id = og.origin_order_id
inner join origin_order_goods oog on oog.origin_order_id = oo.origin_order_id
inner join mddomsuser.sync_platform_shipping_mapping pm on pm.system_shipping_id = o.shipping_id and pm.platform_name = 'pinduoduo'
inner join mddomsuser.shop s on oo.shop_id = s.shop_id
 where 
 o.party_id = 2 and 
 oog.platform_order_status = 'WAIT_SELLER_SEND_GOODS' and oog.refund_status = 'NONE' and 
 o.order_sn in 
('FH210803211700000053',
'FH210803211700000055',
'FH210803191500000054',
'FH210803191500000051',
'FH210803211700000592',
'FH210803201500000131',
'FH210803211700000577',
'FH210803201500000133',
'FH210804084900000451',
'FH210804002500000196',
'FH210804074100000030',
'FH210804074100000019',
'FH210804023100000025',
'FH210804023100000023',
'FH210804084900000446',
'FH210804084900000456',
'FH210804074100000022',
'FH210803231700000092',
'FH210804002500000153',
'FH210803231700000075',
'FH210804002500000194',
'FH210804074100000034',
'FH210804084900000440',
'FH210804002500000139',
'FH210804002500000192',
'FH210803171500000044',
'FH210803171500000046',
'FH210803160900000115',
'FH210803160900000117',
'FH210804092400000073',
'FH210804092200000041',
'FH210804092200000027',
'FH210804092200000015',
'FH210804092200000004',
'FH210804092200000064',
'FH210804092200000051',
'FH210804091800000301',
'FH210804091900000034',
'FH210804091800000117',
'FH210804091900000027',
'FH210804091800000232',
'FH210804091900000036',
'FH210804091900000003',
'FH210804091800000245',
'FH210804091800000288',
'FH210804091800000272',
'FH210804090100000245',
'FH210804090100000252',
'FH210804091100000210',
'FH210804090500000303',
'FH210804090500000225',
'FH210804090500000311',
'FH210804090400001713',
'FH210804090100000010',
'FH210804090000000070',
'FH210804090100000034',
'FH210804090100000020',
'FH210804090100000195',
'FH210803164200000080',
'FH210803155500005274',
'FH210803155500005238',
'FH210803150000001123',
'FH210803155500005176',
'FH210803193500000926',
'FH210803215900002981',
'FH210803235200000673',
'FH210803212300003276',
'FH210803231000000771',
'FH210803212300003274',
'FH210803165300000022',
'FH210803165300000020',
'FH210803173700000008',
'FH210803142300001141',
'FH210803151700000055',
'FH210804085300002002',
'FH210804085300003983',
'FH210804085300002776',
'FH210804084900000156',
'FH210803153200000273',
'FH210803153200000270',
'FH210803115500000041',
'FH210803115500000039',
'FH210803115500000044',
'FH210803114400000036',
'FH210803114300004131',
'FH210803152700000427',
'FH210803153200000275',
'FH210803124800000038',
'FH210803124800000040',
'FH210803140500003341',
'FH210803130700000646',
'FH210803130700000648',
'FH210803130700000650',
'FH210803152300000035',
'FH210803124800000024',
'FH210803124800000018',
'FH210803124800000026',
'FH210803152100000475',
'FH210803124800000022',
'FH210803152300000037',
'FH210803152100000477',
'FH210803124800000020',
'FH210803124800000036',
'FH210803152100000479',
'FH210803152100000483',
'FH210803124800000032',
'FH210803132000003683',
'FH210803124800000034',
'FH210803124800000028',
'FH210803130700000654',
'FH210803152100000481',
'FH210803132000003677',
'FH210803124800000030',
'FH210803132100001590',
'FH210803114400000049',
'FH210803162800000019',
'FH210803162800000017',
'FH210803162800000015',
'FH210803162800000013',
'FH210803125200001367',
'FH210802182400000068',
'FH210802182400000066',
'FH210803132200000101',
'FH210803125200001365',
'FH210803132200000099',
'FH210803140500003196',
'FH210803140500003024',
'FH210803140500003402',
'FH210803140500003098',
'FH210803140500003087',
'FH210803150900000050',
'FH210803150900000048',
'FH210803130700000642',
'FH210803130700000559',
'FH210803130700000563',
'FH210803130700000629',
'FH210803095900001686',
'FH210803130700000631',
'FH210803130700000652',
'FH210803110300000138',
'FH210803110300000088',
'FH210803095900001679',
'FH210803120300003892',
'FH210803095900001926',
'FH210803110300000090',
'FH210803110300000133',
'FH210803095900001924',
'FH210803110300000073',
'FH210803110300000069',
'FH210803110300000071',
'FH210803120300003847',
'FH210803110300000102',
'FH210803110300000100',
'FH210803143800003508',
'FH210803114300004119',
'FH210803145500004210',
'FH210802150700000081',
'FH210803120300003636',
'FH210802150700000079',
'FH210803130700000622',
'FH210802150700000083',
'FH210803130700000620',
'FH210803120300003730',
'FH210803120300003662',
'FH210803120300003724',
'FH210803114400000206',
'FH210803140500003515',
'FH210803140500003419',
'FH210803130700000611',
'FH210803130700000609',
'FH210803110300000131',
'FH210803110300000061',
'FH210803110300000067',
'FH210803110300000063',
'FH210803130700000575',
'FH210803130700000581',
'FH210803130700000579',
'FH210803130700000577',
'FH210803130700000598',
'FH210803130700000593',
'FH210803110300000140',
'FH210803095900001776',
'FH210803095900001770',
'FH210803120300003942',
'FH210803120300003328',
'FH210803085300000208',
'FH210803085300000210',
'FH210803132100001584',
'FH210803132100001588',
'FH210803132100001584',
'FH210803132100001588',
'FH210802212800000081',
'FH210803134500000280',
'FH210803121000000067',
'FH210803125900000086',
'FH210803135500000189',
'FH210803141700000015',
'FH210803130000004474',
'FH210803105900000047',
'FH210802232700000097',
'FH210802232700000095',
'FH210802202100000503',
'FH210802202100000520',
'FH210802191900000054',
'FH210802202100000533',
'FH210802202100000523',
'FH210802191900000061',
'FH210802202100000496',
'FH210802202100000643',
'FH210802191900000063',
'FH210802202100000494',
'FH210802191900000084',
'FH210802191900000052',
'FH210802202100000657',
'FH210802191900000065',
'FH210802222500002784',
'FH210803064900001143',
'FH210803024100000018',
'FH210803024100000022',
'FH210803024100000032',
'FH210803003300001246',
'FH210803024100000034',
'FH210802232700000147',
'FH210803003300001163',
'FH210803003300001312',
'FH210803003300001009',
'FH210802232700000072',
'FH210803064900001141',
'FH210803003300001349',
'FH210802232700000151',
'FH210803003300001103',
'FH210803024100000028',
'FH210803024100000020',
'FH210803024100000024',
'FH210802222500002561',
'FH210803024100000016',
'FH210803024100000012',
'FH210803024100000014',
'FH210802232700000081',
'FH210803024100000026',
'FH210803024100000030',
'FH210803003300001743',
'FH210802222500002670',
'FH210803003300001666',
'FH210802222500002272',
'FH210803024100000010',
'FH210802222500002716',
'FH210802222500002229',
'FH210802171500006111',
'FH210802181900000085',
'FH210802181900000083',
'FH210802171500006262',
'FH210802181900000049',
'FH210802171500006103',
'FH210802220500001054',
'FH210802220500000976',
'FH210802200500005541',
'FH210802200500005539',
'FH210802182700000087',
'FH210802182700000085',
'FH210802191900000068',
'FH210802232700000106',
'FH210802232700000126',
'FH210802222500002669',
'FH210802222500001335',
'FH210802232700000108',
'FH210802213200005701',
'FH210802232700000128',
'FH210802213200005763',
'FH210802222500002713',
'FH210802222500001423',
'FH210802181900000045',
'FH210802181900000043',
'FH210802133000000068',
'FH210802142900000062',
'FH210802172600000021',
'FH210802172600000018',
'FH210802222500003177',
'FH210802232700000083',
'FH210802222500003223',
'FH210802232700000118',
'FH210802213200006327',
'FH210802232700000114',
'FH210802222500001947',
'FH210802222500001997',
'FH210802232700000116',
'FH210802232700000155',
'FH210802213200006329',
'FH210802222500002027',
'FH210802222500002671',
'FH210802171500006264',
'FH210802222500002680',
'FH210802171500006123',
'FH210802171500006110',
'FH210803085300000172',
'FH210803085300000176',
'FH210803085300000178',
'FH210803085300000181',
'FH210803085300000183',
'FH210803085300000174',
'FH210803085300000214',
'FH210803085300000168',
'FH210803085300000185',
'FH210803085300000206',
'FH210803085300000187',
'FH210803085300000195',
'FH210803085300000204',
'FH210803085300000197',
'FH210803085300000224',
'FH210803085300000222',
'FH210803085300000212',
'FH210803085300000170',
'FH210803075100001906',
'FH210803064600000045',
'FH210803065100000015',
'FH210803060200000006',
'FH210803074200000459',
'FH210803085300000193',
'FH210803085300000189',
'FH210803085300000216',
'FH210803085300000218',
'FH210803085300000191',
'FH210803085300000200',
'FH210803085300000202',
'FH210803085300000220',
'FH210802172600000071',
'FH210802172600000069',
'FH210802172600000073',
'FH210802172600000075',
'FH210803092700000066',
'FH210803102300000118',
'FH210803094500000179',
'FH210803085600001565',
'FH210803085600001563',
'FH210802232700000130',
'FH210802232700000139',
'FH210802191900000070',
'FH210802232700000132',
'FH210802232700000145',
'FH210803013700000015',
'FH210803013700000018',
'FH210802191900000082',
'FH210802181900000047',
'FH210802182400000054',
'FH210802182400000056',
'FH210803091400000257',
'FH210803091400000290',
'FH210803091400000274',
'FH210803090800000006',
'FH210803090300000010',
'FH210803090300000004',
'FH210803090300000235',
'FH210803090200004777',
'FH210803085900000037',
'FH210803085900000016',
'FH210803085900000003',
'FH210803085900000025',
'FH210803085800000245',
'FH210803084800000220',
'FH210803084800000092',
'FH210803084900000012',
'FH210803084800000171',
'FH210803084700004230',
'FH210802232900002504',
'FH210802214200000025',
'FH210802223800000054',
'FH210802192100000063',
'FH210802181100000052',
'FH210802182900001695',
'FH210802221500000035',
'FH210802203100000146',
'FH210802161100000035',
'FH210802162500000033',
'FH210802135000000015',
'FH210802135000000013',
'FH210802150700000062',
'FH210802154400000051',
'FH210802161300000041',
'FH210802161300000057',
'FH210802161300000065',
'FH210802161300000067',
'FH210802123100000014',
'FH210802123100000011',
'FH210802142900000060',
'FH210802150700000041',
'FH210802150700000043',
'FH210802164600000025',
'FH210802164600000019',
'FH210802164600000021',
'FH210802164600000023',
'FH210802142500000026',
'FH210802142500000024',
'FH210802130700000083',
'FH210802130700000079',
'FH210802150700000090',
'FH210802150700000116',
'FH210802150700000072',
'FH210802150700000055',
'FH210802150700000058',
'FH210802150700000060',
'FH210802150700000100',
'FH210802150700000102',
'FH210802150700000074',
'FH210802150700000087',
'FH210802150700000051',
'FH210802150700000114',
'FH210802130700000077',
'FH210802140900000077',
'FH210802140900000074',
'FH210802130700000111',
'FH210802140900000071',
'FH210802130700000115',
'FH210802130700000103',
'FH210802130700000112',
'FH210802140900000036',
'FH210802130700000081',
'FH210802130700000091',
'FH210802130700000095',
'FH210802140900000038',
'FH210802153100000066',
'FH210802142400000007',
'FH210802142400000009',
'FH210802142400000026',
'FH210802153100000068',
'FH210802153500000003',
'FH210802153500000005',
'FH210802141900000073',
'FH210802142400000024',
'FH210802142400000022',
'FH210802141900000075',
'FH210802130700000116',
'FH210802095500000093',
'FH210802120600000046',
'FH210802120600000043',
'FH210802100300000056',
'FH210802120600000050',
'FH210802120600000039',
'FH210802120600000064',
'FH210802120600000048',
'FH210802120600000062',
'FH210802120600000054',
'FH210802120600000037',
'FH210802120600000052',
'FH210802120600000056',
'FH210802120600000060',
'FH210802120600000058',
'FH210802110500000082',
'FH210802110500000041',
'FH210802110500000045',
'FH210802120300000098',
'FH210802120300000093',
'FH210802120300000082',
'FH210802120300000090',
'FH210802120300000051',
'FH210802120300000053',
'FH210802110500000063',
'FH210802100300000061',
'FH210802120300000096',
'FH210802100300000063',
'FH210802100300000054',
'FH210802120300000084',
'FH210802100300000077',
'FH210802110500000080',
'FH210802120300000060',
'FH210802100300000081',
'FH210802100300000043',
'FH210802100300000079',
'FH210802110500000065',
'FH210802100300000083',
'FH210802120300000063',
'FH210802110500000055',
'FH210802120300000102',
'FH210802110500000053',
'FH210802110500000051',
'FH210802110500000049',
'FH210802120300000104',
'FH210802104800000073',
'FH210802125400000118',
'FH210802125700000172',
'FH210802124100000012',
'FH210802111800000044',
'FH210802100000000012',
'FH210802123500000082',
'FH210802135900000057')";
$orders = $oms_db->getAll($sql);
var_dump($orders);
foreach ($orders as $order) {
   checkToken($order);
   die;
 } 

function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data))
    );
    $time_start = microtime(true);

    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    
    $result = json_decode($return_content, true);
    if(isset($result['code']) && $result['code'] == 0) {
        $str = "[ExpressApiService][ok] url {$url},data".json_encode($data).",response:".json_encode($result);
    }else{
        $str = "[ExpressApiService][error] url {$url},data".json_encode($data).",response:".json_encode($result);
    }
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo date("Y-m-d H:i:s").' '.(date("Y-m-d H:i:s") .$str." cost {$time}  \r\n");
    return  $result;
}

function checkToken($order){
        global $pdd_new_app_config;
        $pddClient = null;
        // if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        //     $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
        // }else{
        $token = $order['access_token'];
        $pddClient = new PddClient('94b911bd020f4973b4e8fd3e1b2963f8','a074b4b4dc16ff009faca99608a64acdad373cae',$token);
        // }
        $request = new LogisticsOnlineSendRequest();
        $request->setOrderSn($order['platform_order_sn']);
        $request->setTrackingNumber($order['tracking_number']);
        $request->setLogisticsId($order['platform_shipping_id']);

        $result = $pddClient->execute($request);
        if (isset($result->error_code)) {
            echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
            return false;
        }else if(isset($result->mall_name)){
            return true;
        }
        echo date("Y-m-d H:i:s")." token:".$token."调取pdd获取店铺信息失败 result:".json_encode($result).PHP_EOL;
        return false;
    }
