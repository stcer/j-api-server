<?php

namespace j\api\example;

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

use Exception;
use j\api\client\Client;
use j\base\Config;
use j\debug\Profiler;
use j\log\Log;

require __DIR__ . "/init.inc.php";

$type = isset($argv[1]) ? $argv[1] : '';
$links = ['http', 'yar', 'httpSwoole', 'yarSwoole', 'tcp'];
if(!in_array($type, $links)){
    echo <<<STR
usage:
    php client.php http|yar|httpSwoole|yarSwoole|tcp

STR;
    exit;
}

Profiler::start();
LocalTest::run($type);
Profiler::stop();

/**
 * Class LocalTest
 */
class LocalTest {

    /**
     * @param $type
     * @param $api
     * @return Client
     * @throws Exception
     */
    static function getClient($type, $api = ''){
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
        $config->setProtocol($type);
        $config->setEndPoint($confs[$type]);
//        $config->setLogger(new Log());
        return Client::getInstance($config)->getRpc($api);
    }

    static function  run($type){
        self::testApi(self::getClient($type));
        self::testRpc(self::getClient($type, 'test'));
        self::testBatch(self::getClient($type));
    }

    /**
     * @param Client $client
     */
    static function testApi($client){
        echo "----------------------- \n";
        echo "Start api test: \n\n";

        $rs1 = $client->callApi('test.name', ["test"]);
        $rs2 = $client->callApi('test.getName', ["test"]);
        var_dump($rs1);
        var_dump($rs2);
    }

// call 2

    /**
     * @param $testNews
     */
    static function testRpc($testNews){
        echo "----------------------- \n";
        echo "Start rpc test: \n\n";

        $rs2 = $testNews->name("Test2");
        $rs3 = $testNews->search("Test2");
        $rs4 = $testNews->getName();
        $rs5 = $testNews->count(1);

        var_dump($rs2);
        var_dump($rs3);
        var_dump($rs4);
        var_dump($rs5);
    }

    /**
     * batch request
     * @param Client $client
     */
    static function testBatch($client){
        echo "----------------------- \n";
        echo "Start batch test: \n\n";

        $calls = array();
        for($i = 0; $i < 2; $i++){
            $calls["test.name_{$i}"] = [
                'api' => 'test.name',
                'params' => ["TestListener"]
                ];
            $calls["test.search_{$i}"] = [
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