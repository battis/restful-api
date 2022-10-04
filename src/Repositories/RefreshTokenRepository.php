<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use PDO;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(PDO $pdo)
    {
        CRUD\Manager::get($pdo);
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
