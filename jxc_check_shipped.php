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

    global $sync_db;

    while (true) {

        $last_check_time = date("Y-m-d H:i:0",strtotime("-1 hour", time()));
        var_dump($last_check_time);

        $is_start = true;
        while($is_start || count($orders) == 1000){
            $is_start = false;
            $sql = "select o.shop_id,order_sn from sync_pinduoduo_order_info o inner join shop s on o.shop_id = s.shop_id where s.enabled =1 and  shipping_time is not null and order_status =1 and  o.last_updated_time > '{$last_check_time}' limit 1000";
            echo $sql.PHP_EOL;
            $orders = $sync_db->getAll($sql);
            var_dump($orders);
            // $shop_id = $sync_db->getOne("select shop_id from sync_douyin_order_info where pid = '{$pre_ids[0]}'");
            // var_dump(array_diff($pre_ids,$ids));
            foreach ($orders as $order) {
                // var_dump($sync_db->getOne("select enabled from shop where shop_id = {$order['shop_id']}"));
                if ($sync_db->getOne("select enabled from shop where shop_id = {$order['shop_id']}")) {
                   $result = postJsonData('http://100.65.132.5:10314/order/downloadSingleOrder', json_encode(['platform_shop_id'=>$order['shop_id'],'platform_name'=>'pinduoduo','platform_order_sn'=>$order['order_sn']]),0);
                }
            }

            sleep(60*60);
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
         
}