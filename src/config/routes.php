<?php

use Chadicus\Slim\OAuth2;
use Slim\App;

/** @var App $app */
/** @var Container $container */

$version = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$app->setBasePath($_ENV["PUBLIC_PATH"] . "/api/$version");

/**********************************************************************
 * OAuth Server
 */

$server = $container->get(OAuth2\Server::class);
$renderer = $container->get(Slim\Views\PhpRenderer::class);

$app->map(
    ["GET", "POST"],
    OAuth2\Routes\Authorize::ROUTE,
    new OAuth2\Routes\Authorize($server, $renderer)
)->setName("authorize");
$app->post(
    OAuth2\Routes\Token::ROUTE,
    new OAuth2\Routes\Token($server)
)->setName("token");
$app->map(
    ["GET", "POST"],
    OAuth2\Routes\ReceiveCode::ROUTE,
    new OAuth2\Routes\ReceiveCode($renderer)
)->setName("receive-code");

$authorization = new OAuth2\Middleware\Authorization(
    $server,
    $app->getContainer()
);

// ********************************************************************

$app->group("/", function ($api) {
    // TODO API routes here
})->add($authorization);
