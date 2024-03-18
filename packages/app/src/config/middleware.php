<?php

use DI\Container;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tuupola\Middleware\CorsMiddleware;

/** @var App $app */
/** @var Container $container */

$container->set(CorsMiddleware::class, function (
    ContainerInterface $container
) {
    $settings = $container->get("settings")[CorsMiddleware::class];
    $corsOrigin = json_decode($settings["origin"]);
    if (($i = array_search("@", $corsOrigin, true)) !== false) {
        $corsOrigin[$i] =
            ($_SERVER["HTTPS"] ? "https" : "http") .
            "://{$_SERVER["HTTP_HOST"]}";
    }
    return new CorsMiddleware([
        "origin" => $corsOrigin,
        "headers.allow" => json_decode($settings["headers.allow"]),
        "methods" => json_decode($settings["methods"]),
        "cache" => $settings["cache"],
        "credentials" => true,
    ]);
});

$app->add(CorsMiddleware::class);
$app->addBodyParsingMiddleware();
