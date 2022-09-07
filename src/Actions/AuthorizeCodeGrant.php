<?php

namespace Battis\OAuth2\Server\Actions;

use Battis\OAuth2\Server\Entities\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AuthorizeCodeGrant
{
  /**
   * @Inject
   * @var AuthorizationServer
   */
  private $authorizationServer;

  public function __invoke(ServerRequest $request, Response $response)
  {
    try {
      $authRequest = $this->authorizationServer->validateAuthorizationRequest(
        $request
      );
      $user = User::find($request->getParsedBodyParam("username"));
      $authRequest->setUser($user);
      $authRequest->setAuthorizationApproved(
        $user->verify($request->getParsedBodyParam("password"))
      );
      return $this->authorizationServer->completeAuthorizationRequest(
        $authRequest,
        $response
      );
    } catch (OAuthServerException $e) {
      return $e->generateHttpResponse($response);
    }
  }
}
