<?php
require("includes/init.php");
$url = 'http://100.65.128.171:10317';
require("Services/ExpressApiService.php");
use Services\ExpressApiService;
echo date("Y-m-d H:i:s").PHP_EOL;


$params=['orderSn'=>'230309-559976323870058','source'=>'MMS'];
$result = postJsonData('http://121.40.113.153/pdd', json_encode($params),0);
var_dump($result);



function postJsonData($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT,3*60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data),
            'Cookie: api_uid=rBUUx2BRh8kAZAYSleeGAg==; _bee=slB6NXNorRwPmKp6uT31P5hykfVwIMyB; _f77=986e63ac-ec59-4f2f-ad33-e9a432e3f18a; _a42=422798f6-0704-43bb-a82e-91e5ce48713c; rckk=slB6NXNorRwPmKp6uT31P5hykfVwIMyB; ru1k=986e63ac-ec59-4f2f-ad33-e9a432e3f18a; ru2k=422798f6-0704-43bb-a82e-91e5ce48713c; _nano_fp=XpEan59YnpUxnpTxnT_0_kAq2mxTYWUCT2GinqTK; terminalFinger=Ps4qbZWi3Wf2gCzCRWRAVMHTy0APvXG5; AMCV_98CF678254E93B1B0A4C98A5%40AdobeOrg=-2121179033%7CMCMID%7C09615350499651801611896203990380463613%7CMCAAMLH-1642523062%7C11%7CMCAAMB-1642523062%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1641925462s%7CNONE%7CvVersion%7C5.3.0; PASS_ID=1-/bs3HaIQ1SrHccaSHhuHEuoL0QCvmY39XaGZQQz3Ee6/ufU0cJtXhcyq3E1WrY8G7GMdTfANrgb56TYacvpSWQ_797882671_96224939; sm_tid=158%2Ccd5fb74d-1b27-4c5b-ac5b-15713071926b%2C158%2C7d2ce155-4eee-4fce-9141-850433203688; mms_b84d1838=3500,3526,108,3523,3434,3470,3531,3254,3474,3475,3477,3479,410,3482,1202,1203,1204,1205,3417,3397,3497; x-visit-time='.microtime(true).'; JSESSIONID=0C982D4563AFCF8E53668CD7A6836F3D'
        )
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
