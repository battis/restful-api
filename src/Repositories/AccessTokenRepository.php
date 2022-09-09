<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\AccessToken;
use Battis\OAuth2\Server\Repositories\Traits\TokenRepositoryTrait;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use TokenRepositoryTrait;

    public function __construct(Connection $connection)
    {
        $this->table = "oauth2_access_tokens";
        $this->connection = $connection;
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
        $this->persistToken([
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
        $this->revokeToken($tokenId);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return $this->isTokenRevoked($tokenId);
    }
}
