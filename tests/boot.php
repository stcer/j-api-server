<?php

namespace j\api;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . "/vendor/autoload.php";
$loader->addPsr4(__NAMESPACE__ . "\\", __DIR__ . "/api");
