<?php

require(__DIR__ . '/../init.inc.php');

use j\api\server\FpmApp as App;

$app = new App();
$app->getLoader()->setNsPrefix('\\api\\action\\');
$app->isInner = true;
$app->run();
