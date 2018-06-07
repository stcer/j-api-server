<?php

namespace j\api\example;

use Exception;
use j\api\client\Client;
use j\debug\Timer;
use j\log\Log;

include(__DIR__ . "/init.inc.php");

$types = $argv;
array_shift($types);
if(!$types){
    echo <<<STR
usage:

    php benchmarkTest.php <type1> [type2] [type3] [typeN]
    
    valid type:
        http yar httpSwoole yarSwoole tcp

STR;
    exit;
}

$timer = new Timer();
$times = [];
foreach($types as $type){
    $times[$type] = (new jzApiTest($type))->test($timer);
}

echo "\n";
var_dump($times);


class jzApiTest {

    protected $type;

    /**
     * @var Client
     */
    protected $client;

    /**
     * ClientTest constructor.
     * @param $type
     * @throws
     */
    public function __construct($type){
        $this->type = $type;
        $this->client = $this->getClient(($type));
    }

    protected function getClient($type){
        $confs = [
            'http' => 'http://w.backend.jz.cn/index.php',
            'httpSwoole' => 'http://192.168.0.178:8601',
            'yarSwoole' => 'http://192.168.0.178:8602',
        ];

        if(!isset($confs[$type])){
            throw new Exception("Invalid client type");
        }
        $config = new \j\api\client\Config('jz-test', '123123');
        $config->setTimeout(10);
        $config->setProtocol($type);
        $config->setEndPoint($confs[$type]);
        $config->setLogger(new Log());
        return Client::getInstance($config);
    }


    private function testBatch(){
        $client = $this->client;
        $calls = array();
        for($i = 0; $i < 10; $i++){
            $uid = rand(1, 100000);
            $calls["user_{$uid}"] = [
                'api' => 'uc.User.getUser',
                'args' => [rand(1, 100000)]
            ];
            $calls["goods_u_{$uid}"] = [
                'api' => 'goods.UserGoods.search',
                'args' => [$uid]
            ];
        }
        $data = $client->calls($calls);
        var_dump($data);
    }

    private function testRpcObject(){
        $object = $this->client->getRpc('uc.User');
        $u = $object->getUser(6388);
        $f = $object->authPassword("1231230", '1231230');
        var_dump($u, $f);
    }

    private function testApi(){
        $client = $this->client;
        for($i = 0; $i < 5; $i++){
            $client->callApi('uc.User.getUser', [rand(1, 10000)]);
            $client->callApi('uc.User.authPassword', ['username', 'test']);
        }
    }

    /**
     * @param Timer $timer
     * @return float
     */
    function test($timer){
        $timer->start();
        $this->testRpcObject();
        $this->testBatch();
        $this->testApi();
        return $timer->stop();
    }
}
