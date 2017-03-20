<?php

# 127.0.0.1:8061
require(__DIR__ . '/../init.inc.php');

use j\api\server\SwooleHttp as Server;
use j\network\http\Server as HttpServer;
use j\log\Log;

$server = new Server();
$server->port = 8603;
$server->options['pid_file'] = PATH_ROOT . "/tmp/pid/api_swoole_http.pid";
//$server->options['daemonize'] = true;

$server->getDocReader()->setApiPath(__DIR__ . '/action/');
$server->getDocReader()->apiFilePattern = '/Service.php$/';
$server->getLoader()->classSuffix = 'Service';
$server->testUrl = 'http://api.j7.x1.cn/?api=%action%';

$server->onServerCreate = function(HttpServer $server){
    $server->setOption('worker_num', 10);
    $server->setOption('max_request', 1);
    $server->setLogger(new Log());
};

$server->run();