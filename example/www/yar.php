<?php

require(__DIR__ . '/../init.inc.php');

$app = new j\api\server\FpmYar();
$app->getLoader()->setNsPrefix('\\api\\action\\');
$app->run();
