<?php

namespace Battis\OAuth2\Actions;

use Battis\OAuth2\Entities\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Authorize
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
      $authRequest = $server->validateAuthorizationRequest($request);
      $user = User::find($request->getParsedBodyParam("username"));
      $authRequest->setUser($user);
      $authRequest->setAuthorizationApproved(
        $user->verify($request->getParsedBodyParam("password"))
      );
      return $server->completeAuthorizationRequest($authRequest, $response);
    } catch (OAuthServerException $e) {
      return $e->generateHttpResponse($response);
    }
  }
}
