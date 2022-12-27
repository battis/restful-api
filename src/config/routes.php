<?php

use Battis\OAuth2\Server\OAuth2Controller;
use Battis\OAuth2\Server\OAuth2Middleware;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/** @var App $app */
/** @var Container $container */

$version = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$app->setBasePath($_ENV["PUBLIC_PATH"]);

$app->group("/oauth2", OAuth2Controller::class);

$app->group("/api/$version", function ($api) {
    $api->get("/example", function (
        ServerRequest $request,
        Response $response
    ) {
        return $response->withJson($request->getParsedBody());
    });
})->add(OAuth2Middleware::class);
