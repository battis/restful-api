<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use PDO;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(PDO $pdo)
    {
        CRUD\Manager::get($pdo);
    }

    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ) {
        $accessToken = new AccessToken();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        return $accessToken;
    }

    public function persistNewAccessToken(
        AccessTokenEntityInterface $accessTokenEntity
    ) {
        AccessToken::create([
            "identifier" => $accessTokenEntity->getIdentifier(),
            "expiryDateTime" => $accessTokenEntity->getExpiryDateTime(),
            "userIdentifier" => $accessTokenEntity->getUserIdentifier(),
            "scopes" => $accessTokenEntity->getScopes(),
            "clientIdentifier" => $accessTokenEntity
                ->getClient()
                ->getIdentifier(),
        ]);
    }

    public function revokeAccessToken($tokenId)
    {
        AccessToken::delete($tokenId);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return AccessToken::read($tokenId) === null;
    }
}
