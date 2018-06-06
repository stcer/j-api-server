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
    'ns' => 'api\\action\\',
    'classSuffix' => '',
    'logFile' => $_tmpDir . '/log/api.log',
    'logMode' => 31,

    'doc' => [
        'port' => 8500,
        'host' => '0.0.0.0',
        'baseDir' => __DIR__ . '/action/',
        'fileSuffix' => '/Service.php$/',
        'testUrl' => 'http://w.api.jz.x2.cn/index.php?api=%action%',
    ],

    'http' => [
        'debug' => 1,
        'port' => 8501,
        'host' => '0.0.0.0',
        'daemonize' => false,
        'pid' => $_tmpDir . "/pid/http.pid",
        'log' => $_tmpDir . '/log/http_swoole.log',
        'swoole' => [
            'worker_num' => 10,
            'task_worker_num' => 10,
            'package_max_length' => 1024 * 4,
            'max_request' => 10,
        ]
    ],

    'yar' => [
        'debug' => 1,
        'port' => 8502,
        'host' => '0.0.0.0',
        'daemonize' => false,
        'pid' => $_tmpDir . "/pid/yar.pid",
        'log' => $_tmpDir . '/log/yar_swoole.log',
        'swoole' => [
            'worker_num' => 10,
            'max_request' => 20,
            'task_worker_num' => 10,
        ]
    ],

    'tcp' => [
        'debug' => 1,
        'port' => 8503,
        'host' => '0.0.0.0',
        'daemonize' => false,
        'pid' => $_tmpDir . "/pid/tcp.pid",
        'log' => $_tmpDir . '/log/tcp.log',
        'swoole' => [
            'worker_num' => 10,
            'max_request' => 20,
            'task_worker_num' => 10,
        ]
    ],
]);

$di = Container::getInstance();
$di->set('config', $config);
$di->registerProviders([
    j\db\ServiceProvider::class,
    j\api\ServerProvider::class
]);