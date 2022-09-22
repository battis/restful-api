<?php

namespace Battis\OAuth2\Server\Actions;

use Battis\UserSession;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AuthorizeCodeGrant
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var UserSession\Manager
     */
    private $manager;

    public function __construct(
        AuthorizationServer $authorizationServer,
        UserSession\Manager $manager
    ) {
        $this->authorizationServer = $authorizationServer;
        $this->manager = $manager;
    }

    public function __invoke(ServerRequest $request, Response $response)
    {
        try {
            $authRequest = $this->authorizationServer->validateAuthorizationRequest(
                $request
            );
            // FIXME: this is a hack
            /** @var UserEntityInterface $user */
            $user = $this->manager->getCurrentUser();
            $authRequest->setUser($user);
            $authRequest->setAuthorizationApproved(true); // FIXME: oy
            return $this->authorizationServer->completeAuthorizationRequest(
                $authRequest,
                $response
            );
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($response);
        }
    }
}
