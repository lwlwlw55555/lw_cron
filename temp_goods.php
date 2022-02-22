<?php
require("includes/init.php");
require("Models/ShopModel.php");
require("Models/OrderModel.php");
use Models\ShopModel;
require("Services/ExpressApiService.php");
use Services\ExpressApiService;

echo(date("Y-m-d H:i:s") . " downloadGoods  begin \r\n");

/**
 * 第一个参数为店铺id，如果不传或传0，则表示所有店铺都要执行
 *
 * */
//第一个参数存在且不为0，则下载该店铺订单

$shop_list = ShopModel::getNewShopList();
foreach($shop_list as $shop){
    $result = ExpressApiService::downloadGoods($shop['shop_id']);
    if($result['code'] == 20004){
        selectRdsByShopId($shop['shop_id']);
        ShopModel::enableShop($shop['shop_id']);
    }
}

echo(date("Y-m-d H:i:s") . " downloadGoods end \r\n");
