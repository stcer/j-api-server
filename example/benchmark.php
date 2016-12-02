<?php

use j\debug\Profiler;
use j\api\client\SwooleYar as ClientYar;
use j\api\client\Tcp as ClientTcp;
use j\api\client\Yar as ClientFpmYar;
use j\api\client\HttpJson as ClientHttpJson;
use j\api\client\SwooleHttp;

$vendorPath = realpath(__DIR__ . "/../vendor/");
$loader = include($vendorPath . "/autoload.php");

/**
 * @param $type
 * @param string $api
 * @return \j\api\client\Base
 * @throws Exception
 */
function getClient($type, $api = ''){
    switch ($type){
        case 'fpmHttp' :
            $client = ClientHttpJson::getInstance($api);
            $client->serverAddress = 'http://api.j7.x1.cn/index.php';
            break;
        case 'fpmYar' :
            $client = ClientFpmYar::getInstance($api);
            $client->serverAddress = 'http://api.j7.x1.cn/yar.php';
            break;

        case 'swooleHttp' :
            $client = SwooleHttp::getInstance($api);
            $client->serverAddress = 'http://127.0.0.1:8061';
            break;

        case 'swooleYar' :
            ClientYar::$serverUrl = 'http://127.0.0.1:8062';
            $client = ClientYar::getInstance($api);
            break;
        case 'tcp' :
            $client = ClientTcp::getInstance($api);
            $client->port = 8063;
            $client->server = '127.0.0.1';
            break;
        default :
            throw new Exception("Invalid client type");
    }
    return $client;
}

function testBatch1($client){
    for($i = 0; $i < 10; $i++){
        $client->callApi('test.name', ['test']);
        $client->callApi('test.search', ['search']);
    }
}

function testBatch($client){
    $calls = array();
    for($i = 0; $i < 10; $i++){
        $calls["test.name_{$i}"] = [
            'api' => 'test.name',
            'params' => [rand(1, 100)]
        ];
        $calls["test.search {$i}"] = [
            'api' => 'test.search',
            'params' => [rand(1, 100)]
        ];
    }
    $data = $client->calls($calls);
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

function test($types = ['swooleYar', 'fpmYar']){
    $timer = new \j\api\base\Timer();
    $times = [];
    foreach($types as $type){
        $timer->start();
        testBatch(getClient($type)); 
        //testBatch1(getClient($type));
        testObject(getClient($type, 'test'));
        $times[$type] = $timer->stop();
    }

    echo "<pre>\n";
    var_dump($times);
}

test(['fpmHttp', 'fpmYar',  'swooleHttp', 'swooleYar', 'tcp']);