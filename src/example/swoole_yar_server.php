<?php

# define loader 
$loader = Loader::getLoader();
$loader->addPsr4('api\\action\\', __DIR__ . '/action/');
$loader->addPsr4('api\\', __DIR__ . '/modules/');

use j\api\server\SwooleYar as Server;

$server = new Server();
$server->options['pid_file'] = __DIR__ . "/swoole_server.pid";
//$server->options['daemonize'] = true;
$server->run();