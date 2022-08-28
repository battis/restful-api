<?php

use Battis\OAuth2;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/** @var App $app */
/** @var Container $container */

// configure the app's path on the server (including the option for multiple versions of the API)
$version = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$app->setBasePath("/");

// assign the OAuth2 endpoint(s) to the OAuth2\Controller
$app->group(OAuth2\Controller::ENDPOINT, OAuth2\Controller::class);

$app
  ->group("/api/$version", function ($api) {
    $api->get("/example", function (
      ServerRequest $request,
      Response $response
    ) {
      // TODO this is just an example
      return $response->withJson($request->getParsedBody());
    });
  })
  // secure the API endpoints with the OAuth2\Middleware
  ->add(OAuth2\Middleware::class);
