<?php

# 127.0.0.1:8063
require(__DIR__ . '/../init.inc.php');

use j\api\server\SwooleTcp as Server;
use j\network\tcp\Server as TcpServer;
use j\log\Log;

$server = new Server();
//$server->options['daemonize'] = true;
$server->onServerCreate = function(TcpServer $server){
    $server->setOption(['pid_file' => PATH_ROOT . "/tmp/pid/api_tcp.pid"]);
    $server->setOption('task_worker_num', 10);
    //$server->setLogger(new Log());
};

$server->run();