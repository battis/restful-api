<?php

use Defuse\Crypto\Key;
use League\OAuth2\Server\AuthorizationServer;
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
    AuthorizationServer::class => [
        "private_key_path" =>
            realpath(
                __DIR__ . "/../../" . $_ENV["OAUTH2__KEY_PATH"] . "/private.key"
            ) ?:
            realpath(__DIR__ . "/../../var/oauth2/private.key"),
        "encryption_key" => Key::loadFromAsciiSafeString(
            $_ENV["OAUTH2__SERVER_KEY"]
        ),
        "auth_code_ttl" => new DateInterval(
            $_ENV["OAUTH2__AUTH_CODE_TTL"] ?: "PT5M"
        ),
        "access_token_ttl" => new DateInterval(
            $_ENV["OAUTH2__ACCESS_CODE_TTL"] ?: "PT1H"
        ),
        "refresh_token_ttl" => new DateInterval(
            $_ENV["OAUTH2__REFRESH_TOKEN_TTL"] ?: "P1M"
        ),
    ],

    ResourceServer::class => [
        "public_key_path" =>
            realpath(
                __DIR__ . "/../../" . $_ENV["OAUTH2__KEY_PATH"] . "/public.key"
            ) ?:
            realpath(__DIR__ . "/../../var/oauth2/public.key"),
    ],
];
