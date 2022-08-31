<?php

namespace Battis\OAuth2\Actions;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AccessTokenAction
{
  /** @var ContainerInterface */
  private $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function __invoke(ServerRequest $request, Response $response)
  {
    /** @var AuthorizationServer $server */
    $server = $this->container->get(AuthorizationServer::class);
    try {
      return $server->respondToAccessTokenRequest($request, $response);
    } catch (OAuthServerException $e) {
      return $e->generateHttpResponse($response);
    }
  }
}
