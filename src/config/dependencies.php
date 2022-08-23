<?php

use DI\Container;
use Illuminate\Database\Capsule\Manager;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/** @var Container $container */

$container->set(LoggerInterface::class, function (
    ContainerInterface $container
) {
    $settings = $container->get("settings")[LoggerInterface::class];
    $logger = new Logger($settings["name"]);
    $logger->pushProcessor(new WebProcessor());
    if (false === empty($settings["path"])) {
        $logger->pushHandler(
            new RotatingFileHandler($settings["path"], 0, Logger::DEBUG)
        );
    } else {
        $logger->pushHandler(new ErrorLogHandler(0, Logger::DEBUG, true, true));
    }
    return $logger;
});

$container->set(PDO::class, function (ContainerInterface $container) {
    $settings = $container->get("settings")[PDO::class];

    $pdo = new PDO(
        $settings["dsn"],
        $settings["username"],
        $settings["password"]
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if (strpos($settings["dsn"], "sqlite") === 0) {
        $pdo->exec("PRAGMA foreign_keys = ON");
    }

    return $pdo;
});

$container->set(Manager::class, function (ContainerInterface $container) {
    $capsule = new Manager();
    $settings = $container->get(PDO::class);
    $capsule->addConnection(array_filter($settings));
    $capsule->bootEloquent();
    return $capsule;
});
