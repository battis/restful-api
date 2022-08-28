<?php

use DI\Container;
use Slim\Factory\AppFactory;

require_once __DIR__ . "/../vendor/autoload.php";

$container = new Container();
$container->set("settings", require __DIR__ . "/config/settings.php");

$app = AppFactory::createFromContainer($container);

require __DIR__ . "/config/dependencies.php";
require __DIR__ . "/config/middleware.php";
require __DIR__ . "/config/routes.php";

$app->run();
