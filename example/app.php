<?php

use DI\Container;
use Slim\Factory\AppFactory;

/** @var Container $container */
$container = require_once __DIR__ . "/bootstrap.php";

$app = AppFactory::createFromContainer($container);

include __DIR__ . "/config/dependencies.php";
include __DIR__ . "/config/middleware.php";
include __DIR__ . "/config/routes.php";

$app->run();
