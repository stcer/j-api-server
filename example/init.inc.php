<?php

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

$vendorPath = realpath(__DIR__ . "/../vendor/");
$loader = include($vendorPath . "/autoload.php");

use j\di\Container;
$loader->addPsr4('api\\action\\', __DIR__ . '/action/');
$loader->addPsr4('api\\', __DIR__ . '/modules/');

$config = \j\base\Config::getInstance();
$config->set('db', [
    'conn' => [
        //'adapter' => 'Mysqli',
        'host' => '192.168.0.234',
        'port' => 3306,
        'user' => 'root',
        'password' => 'root',
        'database' => 'home2',
        'charset' => 'gbk',
        'persitent' => false
    ]
]);

define('PATH_ROOT', dirname(__DIR__));
$_binDir = PATH_ROOT . '/bin/';
$_tmpDir = PATH_ROOT . '/tmp/';
$config->set('apiServer', [
    'api.ns' => 'api\\action\\',
    'api.baseDir' => __DIR__ . '/action/',
    'api.test' => 'http://w.api.jz.x1.cn/index.php?api=%action%',
    'bin.dir' => $_binDir,
    'logFile' => $_tmpDir . '/log/api.log',
    'logMode' => 31,

    'httpSwoole' => [
        'debug' => 1,
        'port' => 8501,
        'host' => '0.0.0.0',
        'daemonize' => false,
        'worker_num' => 10,
        'max_request' => 1,
        'pid' => $_tmpDir . "/pid/api_swoole_http.pid",
        'log' => $_tmpDir . '/log/http_swoole.log',
    ],

    'yarSwoole' => [
        'debug' => 1,
        'port' => 8502,
        'host' => '0.0.0.0',
        'daemonize' => false,
        'worker_num' => 10,
        'max_request' => 1,
        'task_worker_num' => 10,
        'pid' => $_tmpDir . "/pid/api_swoole_yar.pid",
        'log' => $_tmpDir . '/log/yar_swoole.log',
    ]
]);

$di = Container::getInstance();
$di->set('config', $config);
$di->registerProviders([
    j\db\ServiceProvider::class,
    j\api\ApiServerProvider::class
]);