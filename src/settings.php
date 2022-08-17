<?php

namespace Battis\RESTfulServer;

use Monolog\Logger;
use OAuth2\Server as OAuth2Server;
use PDO;
use Tuupola\Middleware\CorsMiddleware;

return [
    "addContentLengthHeader" => false,

    CorsMiddleware::class => [
        "origin" =>
            $_ENV["CORS__PHP_ORIGIN"] ?:
            "http" .
                ($_SERVER["HTTPS"] ? "s" : "") .
                "://{$_SERVER["HTTP_HOST"]}",
        "headers.allow" =>
            $_ENV["CORS__HEADERS"] ?:
            '["Authorization","Accept","Content-Type"]',
        "methods" => $_ENV["CORS__METHODS"] ?: '["POST","GET","OPTIONS"]',
        "cache" => $_ENV["CORS__CACHE"] ?: 0,
    ],

    PDO::class => [
        "dsn" =>
            $_ENV["DB__PHP_DSN"] ?: "sqlite:" . __DIR__ . "/../var/db.sqlite",
        "username" => $_ENV["DB__USER"] ?: null,
        "password" => $_ENV["DB__PASSWORD"] ?: null,
    ],

    Logger::class => [
        "name" => $_ENV["APP_NAME"],
        "path" => __DIR__ . "/../../logs/server.log",
    ],

    OAuth2Server::class => [
        "access_lifetime" =>
            $_ENV["SERVER__ACCESS_TOKEN_DURATION_IN_MINUTES"] * 60 ?: 3600,
        "always_issue_new_refresh_token" => true,
        "refresh_token_lifetime" =>
            $_ENV["SERVER__REFRESH_TOKEN_DURATION_IN_MINUTES"] * 60 ?:
            6 * 7 * 24 * 60 * 60,
        "www_realm" => $_ENV["WWW_REALM"] ?: $_ENV["APP_NAME"],
    ],
];
