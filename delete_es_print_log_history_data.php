<?php

date_default_timezone_set("Asia/Shanghai");

require 'vendor/autoload.php';
$hosts = [
    'host' => 'saas-8c53a240'
];
$client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

$delete_time = date("Y-m-d H:i:s", strtotime());
$param = [
    'index' => 'print_log',
    'type'  => '_doc',
    'body'  => [
        'query' => [
            'bool' => [
                'filter' => [
                    'range' => [
                        'created_time' => [
                            'lte' => $delete_time
                        ]
                    ]
                ]
            ]
        ]
    ]
];
$client->deleteByQuery($param);
echo $delete_time." 之前的数据已删除".PHP_EOL;