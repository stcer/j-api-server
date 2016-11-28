<?php

# 127.0.0.1:8062
require(__DIR__ . '/init.inc.php');

use j\api\server\SwooleYar as Server;
use syar\Server as YarServer;
use syar\log\Log;

$server = new Server();
$server->options['pid_file'] = __DIR__ . "/api_swoole_yar.pid";
//$server->options['daemonize'] = true;
$server->onServerCreate = function(YarServer $server){
    $server->setLogger(new Log());
    $server->setOption('task_worker_num', 10);
};

$server->run();