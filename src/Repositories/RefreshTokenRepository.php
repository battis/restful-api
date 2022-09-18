<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\RefreshToken;
use Doctrine\DBAL;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(DBAL\Connection $connection)
    {
        CRUD\Manager::get($connection);
    }

    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(
        RefreshTokenEntityInterface $refreshTokenEntity
    ) {
        RefreshToken::create([
            "identifier" => $refreshTokenEntity->getIdentifier(),
            "expiryDateTime" => $refreshTokenEntity->getExpiryDateTime(),
            "accessTokenIdentifier" => $refreshTokenEntity
                ->getAccessToken()
                ->getIdentifier(),
        ]);
    }

    public function revokeRefreshToken($tokenId)
    {
        RefreshToken::delete($tokenId);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return RefreshToken::read($tokenId) === null;
    }
}
