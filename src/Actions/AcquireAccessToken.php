<?php

namespace Battis\OAuth2\Server\Actions;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AcquireAccessToken
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    public function __construct(AuthorizationServer $authorizationServer)
    {
        $this->authorizationServer = $authorizationServer;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
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
