<?php

namespace j\api\example;

use Exception;
use j\api\client\Client;
use j\debug\Timer;
use j\log\Log;
use swoole_process;

include(__DIR__ . "/init.inc.php");

$options = getopt('c:n:t:');

$n = isset($options['n']) ? $options['n'] : 1;
$n = intval($n);
$c = isset($options['c']) ? $options['c'] : 1;
$c = intval($c);
$types = isset($options['t']) ? $options['t'] : '';
$types = preg_split('/\s+/', $types, -1, PREG_SPLIT_NO_EMPTY);
if(!$types || !$c || !$n){
    echo <<<STR
usage:

    php benchmarkTest.php -c N -n N -t '<type1> [type2] [type3] [typeN]'
        -c connections
        -n run times
        -t type, valid type:
            http yar httpSwoole yarSwoole tcp
    example:
        php benchmarkTest.php -c 2 -n 10 -t '<type1> [type2] [type3] [typeN]'

STR;
    exit;
}

// c个进程同时请求, 每个进程执行N次请求
$processManager = new SimpleProcessorManager();
$processTimes = $processManager->run($c, function(swoole_process $worker) use($types, $n){
    $timer = new Timer();
    $times = [];
    for($i = 0; $i <= $n; $i++){
        foreach($types as $type){
            $times[$type][] = (new jzApiTest($type))->test($timer);
        }
    }
    echo "Start write to parent\n";
    $worker->write(serialize($times));
}, true);

echo "\nResult:\n";
echo count($processTimes) . " processes was finished, run {$n} times per process, rpc call 24(sql queries) per time";
$result = [];
foreach($processTimes as $time){
    $oneProcess = unserialize($time);
    foreach($oneProcess as $type => $nTimes){
        if(!isset($result[$type])){
            $result[$type] = 0;
        }
        $result[$type] += array_sum($nTimes);
    }
}

var_dump($result);

/**
 * Class SimpleProcessorManager
 * @package j\debug
 */
class SimpleProcessorManager{
    /**
     * @var array
     */
    protected $works = [];
    protected $workNums;
    protected $result = [];

    function run($workNums, $callback, $readResult = null){
        $this->workNums = $workNums;
        $this->start($callback, $readResult);
        $this->close();

        return $this->result;
    }

    protected function start($callback, $readResult){
        /** @var swoole_process[] $workers */
        $workers = [];
        for($i = 0; $i < $this->workNums; $i++) {
            $process = new swoole_process($callback, false, 2);
            $pid = $process->start();
            $workers[$pid] = $process;
        }

        if($readResult){
            echo "Start read from work:\n";
            foreach($workers as $worker){
                $this->result[] = $worker->read();
            }
        }

        return $this->result;
    }

    protected function close(){
        for($i = 0; $i < $this->workNums; $i++) {
            $ret = swoole_process::wait();
            $pid = $ret['pid'];
            echo "Worker Exit, PID=" . $pid . PHP_EOL;
        }
    }
}

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
        //$config->setLogger(new Log());
        return Client::getInstance($config);
    }


    private function testBatch(){
        $client = $this->client;
        $calls = array();
        for($i = 0; $i < 10; $i++){
            $uid = rand(1, 1000000);
            $calls["user_{$uid}"] = [
                'api' => 'uc.User.getUser',         // 获取会员信息
                'args' => [$uid]
            ];
            $calls["goods_u_{$uid}"] = [
                'api' => 'goods.UserGoods.search', // 查询会员产品
                'args' => [$uid]
            ];
        }
        $data = $client->calls($calls);
        //var_dump($data);
    }

    private function testRpcObject(){
        $object = $this->client->getRpc('uc.User');
        $u = $object->getUser(6388);
        $f = $object->authPassword("1231230", '1231230');
        var_dump($u, $f);
    }

    private function testApi(){
        $client = $this->client;
        $u = $client->request('uc.User.getUser', [rand(1, 100000)]);
        $f = $client->request('uc.User.authPassword', ['username', 'test']);
        var_dump($u, $f);
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
