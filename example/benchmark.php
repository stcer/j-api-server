<?php

namespace j\api\example;

use j\api\client\Client;
use j\base\Config;
use Exception;
use j\log\Log;

include(__DIR__ . "/init.inc.php");

/**
 * @param $type
 * @param string $api
 * @return Client|Client[]
 * @throws Exception
 */
function getClient($type, $api = ''){
    $confs = [
        'http' => 'http://api.j7.x2.cn/index.php',
        'yar' => 'http://api.j7.x2.cn/yar.php',
        'httpSwoole' => 'http://127.0.0.1:' . Config::getInstance()->get('apiServer.http.port'),
        'yarSwoole' => 'http://127.0.0.1:' . Config::getInstance()->get('apiServer.yar.port'),
        'tcp' => ['127.0.0.1', Config::getInstance()->get('apiServer.tcp.port')],
    ];

    if(!isset($confs[$type])){
        throw new Exception("Invalid client type");
    }
    $config = new \j\api\client\Config('jz-test', '123123');
    $config->setTimeout(10);
    $config->setProtocol($type);
    $config->setEndPoint($confs[$type]);
    //$config->setLogger(new Log());
    return Client::getInstance($config)->getRpc($api);
}

/**
 * @param Client $client
 */
function testBatch($client){
    $calls = array();
    for($i = 0; $i < 10; $i++){
        $calls["test.name_{$i}"] = [
            'api' => 'test.name',
            'args' => [rand(1, 100)]
        ];
        $calls["test.search_{$i}"] = [
            'api' => 'test.search',
            'args' => [rand(1, 100)]
        ];
    }
    $data = $client->calls($calls);
    var_dump($data);
}

function testObject($object){
    $rs2 = $object->name("Test2");
    $rs3 = $object->search("Test2");
    $rs4 = $object->getName();
    $rs5 = $object->count(1);

    var_dump($rs2);
    var_dump($rs3);
    var_dump($rs4);
    var_dump($rs5);
}

function testApi($client){
    for($i = 0; $i < 10; $i++){
        $client->callApi('test.name', ['test']);
        $client->callApi('test.search', ['search']);
    }
}

function test($types){
    $timer = new \j\api\base\Timer();
    $times = [];
    foreach($types as $type){
        $timer->start();
        testObject(getClient($type, 'test'));
        testBatch(getClient($type));
        testApi(getClient($type));
        $times[$type] = $timer->stop();
    }

    echo "<pre>\n";
    var_dump($times);
}

test(['http', 'yar', 'httpSwoole', 'yarSwoole', 'tcp']);