<?php

namespace Battis\OAuth2\Server\Middleware;

use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RequireAccessToken
{
    /** @var ResourceServer */
    private $server;

    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ) {
        try {
            $request = $this->server->validateAuthenticatedRequest($request);
            return $handler->handle($request);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse(new Response());
        } catch (Exception $e) {
            return (new OAuthServerException(
                $e->getMessage(),
                0,
                "unknown_error",
                500
            ))->generateHttpResponse(new Response());
        }
    }
}
