<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo(date("Y-m-d H:i:s") . " downloadGoods  begin \r\n");

/**
 * 第一个参数为piece值
 * 第二个参数为库名
 * 第三个参数为店铺id，如果不传或传0，则表示所有店铺都要执行
 * */

//第一个参数存在且不为0，则下载该店铺订单

if (isset($opt_params['shop_id'])){
    $page = 1;
    $shop_id  = $opt_params['shop_id'];
    $result = ExpressApiService::downloadGoods($shop_id,$page);
    while (checkIfContinue($result)) {
        $page ++;
        $result = ExpressApiService::downloadGoods($shop_id,$page);
    }
    if($result['code'] == 20004){
        selectRdsByShopId($shop_id);
        ShopModel::enableShop($shop_id);
    }
}else{
    $sql = "SELECT
                shop_id
            FROM
                shop_extension
            WHERE
                enabled = 1 and 
                is_big_shop < 20
    ";
$shop_list = $sync_db->getAll($sql);

foreach($shop_list as $shop){
    $page = 1;
    $result = ExpressApiService::downloadGoods($shop['shop_id'],$page);
    while (checkIfContinue($result)) {
        $page ++;
        $result = ExpressApiService::downloadGoods($shop['shop_id'],$page);
    }
    if($result['code'] == 20004){
        selectRdsByShopId($shop['shop_id']);
        ShopModel::enableShop($shop['shop_id']);
    }
}
}
echo(date("Y-m-d H:i:s") . " downloadGoods end \r\n");

function checkIfContinue($result){
    if (isset($result['code']) && $result['code'] == 0) {
        if (isset($result['data']['msg']) && strpos($result['data']['msg'], 'pinduoduo') !== false) {
            $count = intval(substr($result['data']['msg'],strpos($result['data']['msg'], 'success:')+8,strpos($result['data']['msg'], 'fail')-1-(strpos($result['data']['msg'], 'success:')+8)));
            if (!empty($count) && is_integer($count) && $count == 100) {
                return true;
            }
        }
    }
    return false;
}

