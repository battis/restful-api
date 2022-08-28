<?php

namespace Battis\OAuth2\Server;

use Battis\OAuth2\Server\Entities\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class OAuth2Controller
{
  private $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function __invoke($routeGroup)
  {
    $routeGroup->post("/auth", self::class . ".authorize");
    $routeGroup->post("/token", self::class . ".token");
  }

  public function authorize(ServerRequest $request, Response $response)
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

  public function token(ServerRequest $request, Response $response)
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
