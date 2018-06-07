<?php

namespace j\api\example;

use j\api\client\Client;
use j\debug\Timer;

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

$timer = new \j\api\base\Timer();
$times = [];
foreach($types as $type){
    $times[$type] = (new BenchmarkTest($type))->test($timer);
}

echo "\n";
var_dump($times);


class BenchmarkTest {

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
        $this->client = getClient(($type));
    }


    private function testBatch(){
        $client = $this->client;
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

    private function testRpcObject(){
        $object = $this->client->getRpc('test');
        $rs2 = $object->name("Test2");
        $rs3 = $object->search("Test2");
        $rs4 = $object->getName();
        $rs5 = $object->count(1);

        var_dump($rs2);
        var_dump($rs3);
        var_dump($rs4);
        var_dump($rs5);
    }

    private function testApi(){
        $client = $this->client;
        for($i = 0; $i < 10; $i++){
            $client->callApi('test.name', ['test']);
            $client->callApi('test.search', ['search']);
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
