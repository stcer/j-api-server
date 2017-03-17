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


$di = Container::getInstance();
$di->set('config', $config);
$di->registerProviders([
    'j\db\ServiceProvider'
]);