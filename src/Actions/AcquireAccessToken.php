<?php

namespace Battis\OAuth2\Server\Actions;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AcquireAccessToken
{
  /**
   * @Inject
   * @var AuthorizationServer
   */
  private $authorizationServer;

  public function __invoke(ServerRequest $request, Response $response)
  {
    /** @var AuthorizationServer $server */
    try {
      return $this->authorizationServer->respondToAccessTokenRequest(
        $request,
        $response
      );
    } catch (OAuthServerException $e) {
      return $e->generateHttpResponse($response);
    }
  }
}
