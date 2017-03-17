<?php

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

use j\debug\Profiler;
use j\api\client\SwooleYar as ClientYar;
use j\api\client\Tcp as ClientTcp;
use j\api\client\FpmYar as ClientFpmYar;
use j\api\client\HttpJson as ClientHttpJson;
use j\api\client\SwooleHttp;

$vendorPath = realpath(__DIR__ . "/../../../vendor/");
$loader = include($vendorPath . "/autoload.php");
header('Content-Type:text/html;charset=gbk');

/**
 * main()
 */
if(isset($_GET['type'])){
    echo "<pre/>";
    Profiler::start();
    LocalTest::run($_GET['type']);
    Profiler::stop();
} else {
    $links = [
        'fpmHttp',
        'fpmYar',
        'swooleHttp',
        'swooleYar',
        'tcp',
        ];
    foreach($links as $link){
        echo "<a href='?type={$link}' target='_blank'>{$link}</a> ";
    }

    echo "<hr /><a href='http://api.j6.x1.cn/xhprof/xhprof_html/index.php' target='_blank'>Profiler</a>";
}

/**
 * Class LocalTest
 */
class LocalTest {
    static function  run($type){
        self::testApi(self::getClient($type));
        self::testObject(self::getClient($type, 'test'));
        self::testBatch(self::getClient($type));
    }

    /**
     * @param $type
     * @param $api
     * @return ClientYar|ClientHttpJson|ClientTcp|ClientFpmYar
     * @throws Exception
     */
    static function getClient($type, $api = ''){
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


    /**
     * @param ClientYar|ClientHttpJson|ClientTcp|ClientFpmYar $client
     */
    static function testApi($client){
        $rs1 = $client->callApi('test.name', ["test"]);
        var_dump($rs1);
    }

// call 2
    static function testObject($testNews){
        $rs2 = $testNews->name("Test2");
        $rs3 = $testNews->search("Test2");
        $rs4 = $testNews->getName();
        $rs5 = $testNews->count(1);

        var_dump($rs2);
        var_dump($rs3);
        var_dump($rs4);
        var_dump($rs5);
    }

// call 3
    /**
     * @param  ClientYar|ClientTcp|ClientFpmYar|ClientHttpJson $client
     */
    static function testBatch($client){
        $calls = array();
        for($i = 0; $i < 20; $i++){
            $calls["test.name_{$i}"] = [
                'api' => 'test.name',
                'params' => ["TestListener"]
                ];
            $calls["test.search {$i}"] = [
                'api' => 'test.search',
                'params' => ["TestListener"]
                ];
        }

        $rs = $client->calls($calls);
        //$rsN = $client->asyncCalls($calls);
        var_dump($rs);
        //var_dump($rsN);
    }
}