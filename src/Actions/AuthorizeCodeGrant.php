<?php

namespace Battis\OAuth2\Server\Actions;

use Battis\UserSession;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Views\PhpRenderer;

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

    /** @var PhpRenderer */
    private $renderer;

    /** @var ClientRepositoryInterface */
    private $clientRepo;

    /** @var ScopeRepositoryInterface */
    private $scopeRepo;

    public function __construct(
        AuthorizationServer $authorizationServer,
        UserSession\Manager $manager,
        PhpRenderer $renderer,
        ClientRepositoryInterface $clientRepo,
        ScopeRepositoryInterface $scopeRepo
    ) {
        $this->authorizationServer = $authorizationServer;
        $this->manager = $manager;
        $this->renderer = $renderer;
        $this->clientRepo = $clientRepo;
        $this->scopeRepo = $scopeRepo;
    }

    public function __invoke(ServerRequest $request, Response $response)
    {
        try {
            if ($request->getParam("authorize", null)) {
                $authRequest = $this->authorizationServer->validateAuthorizationRequest(
                    $request
                );
                // FIXME: this is a hack
                /** @var UserEntityInterface $user */
                $user = $this->manager->getCurrentUser();
                $authRequest->setUser($user);
                $authRequest->setAuthorizationApproved(
                    $request->getParam("authorize") == "yes"
                );
                return $this->authorizationServer->completeAuthorizationRequest(
                    $authRequest,
                    $response
                );
            } else {
                return $this->renderer->render($response, "authorize.php", [
                    "client" => $this->clientRepo->getClientEntity(
                        $request->getParam("client_id")
                    ),
                    "scopes" => array_map(
                        fn(
                            $scope
                        ) => $this->scopeRepo->getScopeEntityByIdentifier(
                            $scope
                        ),
                        explode(" ", $request->getParam("scope"))
                    ),
                ]);
            }
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($response);
        }
    }
}
