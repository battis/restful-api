<?php

// values loaded from the environment or individual library config files

use Battis\OAuth2\Server as OAuth2;

use function DI\string;

return [
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

    // Better to load from $_ENV than to hard code!
    PDO::class => fn() => new PDO(
        "mysql:host=127.0.0.1;port=8889;dbname=oauth2-server",
        "oauth2-server",
        "2l]FVp*1iJUcWcWE"
    ),

    // OAuth2 configuration
    OAuth2\Dependencies::TTL_REFRESH_TOKEN => "P6W",
];
