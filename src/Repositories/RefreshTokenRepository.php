<?php

namespace Battis\OAuth2\Server\Repositories;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Battis\OAuth2\Server\Entities\RefreshToken;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    use DBAL;

    protected $table = "oauth2_refresh_tokens";

    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(
        RefreshTokenEntityInterface $refreshTokenEntity
    ) {
        try {
            $this->queryBuilder()
                ->insert($this->table)
                ->values([
                    "refresh_token" => "?",
                    "expiry" => "?",
                    "access_token_id",
                    "?",
                ])
                ->setParameter(0, $refreshTokenEntity->getIdentifier())
                ->setParameter(
                    1,
                    $refreshTokenEntity->getExpiryDateTime(),
                    "datetime"
                )
                ->setParameter(
                    2,
                    $refreshTokenEntity->getAccessToken()->getIdentifier()
                )
                ->executeStatement();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    public function revokeRefreshToken($tokenId)
    {
        $this->queryBuilder()
            ->delete($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("refresh_token", "?")
            )
            ->setParameter(0, $tokenId)
            ->executeStatement();
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return $this->queryBuilder()
            ->select($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("refresh_token", "?")
            )
            ->setParameter(0, $tokenId)
            ->executeQuery()
            ->rowCount() == 0;
    }
}
