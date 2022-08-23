<?php

use Psr\Log\LoggerInterface;
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
        "driver" => $_ENV["DB__DRIVER"] ?: null,
        "host" => $_ENV["DB__HOST"] ?: null,
        "port" => $_ENV["DB__PORT"] ?: null,
        "database" => $_ENV["DB__DATABASE"] ?: null,
        "username" => $_ENV["DB__USER"] ?: null,
        "password" => $_ENV["DB__PASSWORD"] ?: null,
    ],

    LoggerInterface::class => [
        "name" => $_ENV["APP_NAME"],
        "path" =>
            __DIR__ . "/../../" . $_ENV["LOG__PATH"] ?: "./logs/server.log",
    ],
];
