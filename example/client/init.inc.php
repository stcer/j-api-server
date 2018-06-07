<?php

namespace j\api\example;

use Exception;
use j\api\client\Client;
use j\base\Config;
use j\log\Log;

require dirname(__DIR__) . '/init.inc.php';

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
    $config->setLogger(new Log());
    return Client::getInstance($config)->getRpc($api);
}