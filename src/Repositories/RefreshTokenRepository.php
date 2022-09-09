<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\RefreshToken;
use Battis\OAuth2\Server\Repositories\Traits\TokenRepositoryTrait;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    use TokenRepositoryTrait;

    public function __construct(Connection $connection)
    {
        $this->table = "oauth2_refresh_tokens";
        $this->connection = $connection;
    }

    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(
        RefreshTokenEntityInterface $refreshTokenEntity
    ) {
        $this->persistToken([
            "identifier" => $refreshTokenEntity->getIdentifier(),
            "expiryDateTime" => $refreshTokenEntity->getExpiryDateTime(),
            "accessTokenIdentifier" => $refreshTokenEntity
                ->getAccessToken()
                ->getIdentifier(),
        ]);
    }

    public function revokeRefreshToken($tokenId)
    {
        $this->revokeToken($tokenId);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return $this->isTokenRevoked($tokenId);
    }
}
