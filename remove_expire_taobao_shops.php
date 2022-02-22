<?php
require("includes/init.php");
require("includes/taobaoSDK/TopClient.php");
require("includes/taobaoSDK/request/JushitaJdpUsersGetRequest.php");
require("includes/taobaoSDK/request/JushitaJdpUserAddRequest.php");
require("includes/taobaoSDK/request/JushitaJdpUserDeleteRequest.php");
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
$redis = getFacilityRedis();

echo date("Y-m-d H:i:s").PHP_EOL;

$facilitys = [];

$facilitys = $db_user->getCol("
   select facility_id from 
(select 
             u.facility_id,max(sd.session_date) max_session_date
        from 
            user u
            inner join session_date sd on u.user_id = sd.user_id
        where u.is_force_manage_goods = 0
        group by u.facility_id
        having max_session_date < date_sub(now(), interval 7 day)
 ) tt
");

echo 'facilitys:'.PHP_EOL;
// var_dump($facilitys);
$shop_ids = [];
foreach ($facilitys as $facility_id) {
    selectRdsByFacilityId($facility_id);
    
try{
    $shops = $db->getCol("select shop_id from shop where default_facility_id = {$facility_id} and platform_code = 'taobao' and enabled = 1");
    if (!empty($shops)) {
        // echo date("Y-m-d H:i:s").' facility_id:'.$facility_id. ' shops:'.json_encode($shops).PHP_EOL;
        // var_dump($shops);
        $shop_ids = array_merge($shop_ids,$shops);
        // var_dump($shop_ids);
    }
}catch(\Exception $e){continue;}
}

var_dump($shop_ids);
echo count($shop_ids);
$sql = "update shop_extension set shop_mod = 888 where shop_id in (".implode(",", $shop_ids).")";
echo $sql.PHP_EOL;
$sync_db->query($sql);
$shops = $sync_db->getAll("select * from shop where shop_id in (".implode(",", $shop_ids).")");


global $top_config;
$c = new TopClient;
$c->appkey = $top_config['appkey'];
$c->secretKey = $top_config['secret'];


foreach ($shops as $shop) {
    $req = new JushitaJdpUserDeleteRequest;
    $req->setNick($shop['platform_user_name']);
    $resp = $c->execute($req);
    var_dump($shop);
    var_dump($resp);
}



function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
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

function refreshGroupSkuWeightAndPrice($platform_sku_id,$facility_id){
    global $db,$sync_db,$redis;
     $sql = "select s.sku_id,sum(sk.weight*g.number) weight,sum(i.purchase_price*g.number) purchase_price from group_sku_mapping g inner join sku_mapping s
            on g.facility_id = s.facility_id and g.platform_sku_id = s.platform_sku_id
            inner join sku sk on sk.sku_id = g.sku_id and sk.facility_id = g.facility_id
            inner join inventory i on i.sku_id = g.sku_id and i.facility_id = g.facility_id
            where g.facility_id = {$facility_id} and g.platform_sku_id = {$platform_sku_id}";
    $group_goods = $db->getRow($sql);
    var_dump($group_goods);
    if (!empty($group_goods)&&!empty($group_goods['sku_id'])) {
        echo date("Y-m-d H:i:s").' '."update sku set weight = {$group_goods['weight']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
        $db->query("update sku set weight = {$group_goods['weight']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
        echo date("Y-m-d H:i:s").' '."update inventory set purchase_price = {$group_goods['purchase_price']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}".PHP_EOL;
        $db->query("update inventory set purchase_price = {$group_goods['purchase_price']} where sku_id = {$group_goods['sku_id']} and facility_id = {$facility_id}");
        $redis_value = ['sku_id'=>$group_goods['sku_id'],'facility_id'=>$facility_id];
        $redis->lpush("order_weight_update", json_encode($redis_value));
    }
}

function refreshArrToShopMap($arr){
    $map = [];
    foreach ($arr as $value) {
        if (isset($value['shop_id']) && isset($value['enabled'])) {
            $map[$value['shop_id']] = $value['enabled'];
        }
    }
    return $map;
}

function getFacilityRedis() {
    global $redis_config;
    require_once 'includes/predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => $redis_config['host'],
          'port' => $redis_config['port']
    ]);
    if ($redis_config['auth']) {
        $redis->auth($redis_config['auth']);
    }
    if ($redis_config['database']) {
        $redis->select($redis_config['database']);
    }
    return $redis;
}

