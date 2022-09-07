<?php

// values loaded from the environment or individual library config files

use Battis\OAuth2\Server\Dependencies as OAuth2;
use Defuse\Crypto\Key;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;

use function DI\string;

// FIXME: better to load from $_ENV than to hard code!
return [
    // convenience
    "app.root" => dirname(__DIR__),

    /**
     * Slim app configuration
     * @see https://www.slimframework.com/docs/v3/objects/application.html#slim-default-settings
     */
    "settings" => [
        "displayErrorDetails" => true,
        "determineRouteBeforeAppMiddleware" => false,
        "routerCacheFile" => string("{app.root}/var/slim/routerCacheFile"),
    ],

    // Doctrine configuration
    "doctrine.devMode" => true,
    "doctrine.cacheDir" => string("{app.root}/var/doctrine"),
    "doctrine.metadataDirs" => [
        string("{app.root}/vendor/battis/oauth2-server/src/Entities"),
    ],

    // OAuth2 configuration
    OAuth2::DB_CONNECTION => [
        OAuth2::DB_DRIVER => "pdo_mysql",
        OAuth2::DB_HOST => "127.0.0.1",
        OAuth2::DB_PORT => 8889,
        OAuth2::DB_NAME => "oauth2-server",
        OAuth2::DB_USERNAME => "oauth2-server",
        OAuth2::DB_PASSWORD => "2l]FVp*1iJUcWcWE",
        OAuth2::DB_CHARSET => "utf-8",
    ],
    OAuth2::ENCRYPTION_KEY => fn() => Key::loadFromAsciiSafeString(
        "def00000b1a6feeefadb00454998cd54c98bd8e0d0aa5f0466679a58545d52c66d49a890f30d7087de5d679721c230b358a76937977e30a3c42004b70e94583255c67023"
    ),
    OAuth2::TTL_REFRESH_TOKEN => "P6W",
    OAuth2::GRANT_TYPES => [AuthCodeGrant::class, RefreshTokenGrant::class],
];
