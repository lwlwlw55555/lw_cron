<?php
require("includes/init.php");
echo "[]". (date("Y-m-d H:i:s")). ( " insert_pddyun_sync_users  begin \r\n");
include 'PddClient.php';
include 'request/DdyPdpUserAddRequest.php';
include 'request/DdyPdpUserDeleteRequest.php';

$pdp_rds_list = [
    1 => '18A60CFEA72AC548'
];

$action = isset($argv[1])?$argv[1]:'insert';
global $db,$sync_db;
if ($action == 'insert') {
    
    // $shops = $sync_db->getAll("select * from shop where enabled = 1 limit 1000");
    $shops = $sync_db->getAll("SELECT * from erpsync.shop where platform_shop_id in
                         ('844900532',

'761372230',

'455494977',

'193124',

'331613701',

'313274035',

'956085925',

'667069086',

'993937864',

'729847253',

'229821627',

'614635426',

'926907456',

'228509863',

'103250709',

'783106308',

'485884005',

'370347866',

'131662672',

'326879468',

'388499280',

'134884779',

'398742564',

'948530521',


'166951260',

'582461855',

'878111611',

'662256633',

'916117945',

'197008073',

'472865947',

'433668077',

'1159058',

'314003864',


'1198407',

'1237465',

'947360603',

'697925412',

'846611208',

'270236397',

'1998887',

'4843614',

'5643406',

'709185398',

'608772666',

'890724915',

'898220254',

'170301404',

'207328',

'573466795',

'321939990',

'524573086',

'909493503',

'370004',

'559003982',

'332010782',

'633847809',

'343376702',

'1580610',

'505154',

'453856050',

'215598',

'1581994',

'2060087',

'1056438')");
    foreach ($shops as $shop) {
        deleteUser($shop['platform_shop_id']);
        insertUser($shop['access_token'],$shop['app_key'],1,$shop['shop_id']);
    } 
}

function insertUser($token,$app_key,$pdp_rds_id,$shop_id){
    global $pdd_new_app_config;
    global $pdp_rds_list;
    $pddClient = null;
    if (!empty($app_key) && !empty($pdd_new_app_config) && $app_key == $pdd_new_app_config['appkey']) {
        $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],$token);
    }
    $request = new DdyPdpUserAddRequest();
    $request->setRdsId($pdp_rds_list[$pdp_rds_id]);
    $request->setHistoryDays(5);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
        if ($result->error_code == 50001 && strpos($result->sub_msg,'商家已经存在') !== false) {

            // global $sync_db;
            // deleteUser($sync_db->getOne("select platform_shop_id from shop where shop_id = {$shop_id}"));
        }
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} token:".$token."调取DdyPdpUserAddRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). "shop_id:{$shop_id} token:".$token."调取DdyPdpUserAddRequest失败 result:".json_encode($result).PHP_EOL;
    return false;
}

function getUsers($page=1,$app_key=null){
    global $pdd_new_app_config;
    $pddClient  = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUsersGetRequest();
    $request->setPageSize(200);
    $request->setPageNo($page);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 result:".json_encode($result).PHP_EOL;
        return json_decode(json_encode($result),true);
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest成功  result:".json_encode($result).PHP_EOL;
        return json_decode(json_encode($result),true);
    }
    echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUsersGetRequest失败 result:".json_encode($result).PHP_EOL;
    return json_decode(json_encode($result),true);
}

function deleteUser($owner_id){
    global $pdd_new_app_config;
    $pddClient = new PddClient($pdd_new_app_config['appkey'],$pdd_new_app_config['secret'],'');
    $request = new DdyPdpUserDeleteRequest();
    $request->setOwnerId($owner_id);
    $result = $pddClient->execute($request);
    if (isset($result->error_code)) {
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 result:".json_encode($result).PHP_EOL;
        return false;
    }else if(isset($result->is_success) && $result->is_success){
        // return $result->total_count;
        echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest成功  result:".json_encode($result).PHP_EOL;
        return true;
    }
    echo "[]". (date("Y-m-d H:i:s")). " 调取DdyPdpUserDeleteRequest失败 result:".json_encode($result).PHP_EOL;
    return false;
}

