<?php

use Battis\OAuth2\Server as OAuth2;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/** @var App $app */
/** @var Container $container */

// assign the OAuth2 endpoint(s) to the OAuth2\Controller
$app->group(OAuth2\Controller::ENDPOINT, OAuth2\Controller::class);

$app
  ->group("/api", function ($api) {
    $api->get("/echo", function (ServerRequest $request, Response $response) {
      return $response->withJson($request->getQueryParams());
    });
  })
  // secure the API endpoints with the OAuth2\Middleware
  ->add(OAuth2\Middleware\RequireAccessToken::class);
