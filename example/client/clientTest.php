<?php

namespace j\api\example;

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

use j\api\client\Client;
use j\debug\Profiler;

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
ClientTest::run($type);
Profiler::stop();

/**
 * Class ClientTest
 */
class ClientTest {

    protected $type;

    /**
     * @var Client
     */
    protected $client;

    /**
     * ClientTest constructor.
     * @param $type
     */
    public function __construct($type){
        $this->type = $type;
        $this->client = getClient(($type));
    }


    static function  run($type){
        $test = new self($type);

        echo "----------------------- \n";
        echo "Start api test: \n\n";
        $test->testApi();

        echo "----------------------- \n";
        echo "Start rpc test: \n\n";
        $test->testRpc();

        echo "----------------------- \n";
        echo "Start batch test: \n\n";
        $test->testBatch();
    }

    function testApi(){

        $client = $this->client;
        $rs1 = $client->callApi('test.name', ["test"]);
        $rs2 = $client->callApi('test.getName', ["test"]);
        var_dump($rs1);
        var_dump($rs2);
    }

    function testRpc(){
        $client = $this->client;
        $testNews = $client->getRpc('test');
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
     */
    function testBatch(){
        $client = $this->client;
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