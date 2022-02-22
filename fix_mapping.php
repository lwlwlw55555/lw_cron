<?php
require("includes/init.php");
$url = 'http://100.65.128.171:10317';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo date("Y-m-d H:i:s").PHP_EOL;

selectRdsByFacilityId(200419);


$goodsList = $db->getAll("select
    *
    from goods
    where facility_id =200419 and mapping_count > 0 and goods_alias is not null and goods_alias <> ''
    GROUP by goods_alias
    having count(1) > 1
    order by mapping_count desc");
if (!empty($goodsList)) {
    $params = [];
    foreach ($goodsList as $goods) {
        $sql = "select goods_id from goods where goods_alias = '{$goods['goods_alias']}' and goods_id <> {$goods['goods_id']}";
        $ids = $db->getCol($sql);
        $sql = "select * from goods_mapping where goods_id in (".implode(",", $ids).")";
        $mappings = $db->getAll($sql);
        foreach ($mappings as $mapping) {
            $mapping['goods_id'] = $goods['goods_id'];   
            $params[] = $mapping;
        }
    }
    $result = postJsonData($url.'/goods/updateGoodsMapping', json_encode($params),0);
}

$skuList = $db->getAll("select
    *
    from sku
    where facility_id =200419 and mapping_count > 0 and sku_alias is not null and sku_alias <> ''
    GROUP by sku_alias
    having count(1) > 1
    order by mapping_count desc");
if (!empty($skuList)) {
    foreach ($skuList as $sku) {
        $params = [];
        $sql = "select sku_id from sku where sku_alias = '{$sku['sku_alias']}' and sku_id <> {$sku['sku_id']}";
        $ids = $db->getCol($sql);
        $sql = "select * from sku_mapping where sku_id in (".implode(",", $ids).")";
        $mappings = $db->getAll($sql);
        foreach ($mappings as $mapping) {
            $mapping['goods_id'] = $sku['goods_id'];
            $mapping['sku_id'] = $sku['sku_id'];
            $params[] = $mapping;
        }
        $result = postJsonData($url.'/goods/updateSkuMapping', json_encode($params),0);
    }
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
