<?php

use Battis\OAuth2\Server\Repositories\AccessTokenRepository;
use Battis\OAuth2\Server\Repositories\AuthCodeRepository;
use Battis\OAuth2\Server\Repositories\ClientRepository;
use Battis\OAuth2\Server\Repositories\RefreshTokenRepository;
use Battis\OAuth2\Server\Repositories\ScopeRepository;
use DI\Container;
use Illuminate\Database\Capsule\Manager;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
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

$container->set(AuthorizationServer::class, function (
    ContainerInterface $container
) {
    $settings = $container->get(AuthorizationServer::class);
    $clientRepository = new ClientRepository();
    $scopeRepositroy = new ScopeRepository();
    $accessTokenRepository = new AccessTokenRepository();
    $authCodeRepository = new AuthCodeRepository();
    $refreshTokenRepository = new RefreshTokenRepository();

    $server = new AuthorizationServer(
        $clientRepository,
        $accessTokenRepository,
        $scopeRepositroy,
        $settings["private_key_path"],
        $settings["encryption_key"]
    );

    $server->enableGrantType(
        new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            $settings["auth_code_ttl"]
        ),
        $settings["access_token_ttl"]
    );

    $grant = new RefreshTokenGrant($refreshTokenRepository);
    $grant->setRefreshTokenTTL($settings["refresh_token_ttl"]);
    $server->enableGrantType($grant, $settings["access_token_ttl"]);

    return $server;
});

$container->set(ResourceServer::class, function (
    ContainerInterface $container
) {
    $settings = $container->get(ResourceServer::class);
    $server = new ResourceServer(
        new AccessTokenRepository(),
        $settings["public_key_path"]
    );

    return $server;
});
