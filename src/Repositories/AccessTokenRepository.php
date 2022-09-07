<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\AccessToken;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use DBAL;

    protected $table = "oauth2_access_tokens";

    public function __construct(Connection $connection)
    {
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
        try {
            $this->queryBuilder()
                ->insert($this->table)
                ->values([
                    "access_token" => "?",
                    "expiry" => "?",
                    "user_id" => "?",
                    "scopes" => "?",
                    "client_id" => "?",
                ])
                ->setParameter(0, $accessTokenEntity->getIdentifier())
                ->setParameter(
                    1,
                    $accessTokenEntity->getExpiryDateTime(),
                    "datetime"
                )
                ->setParameter(2, $accessTokenEntity->getUserIdentifier())
                ->setParameter(3, $accessTokenEntity->getScopes(), "json")
                ->setParameter(
                    4,
                    $accessTokenEntity->getClient()->getIdentifier()
                )
                ->executeStatement();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    public function revokeAccessToken($tokenId)
    {
        $this->queryBuilder()
            ->delete($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("access_token", "?")
            )
            ->setParameter(0, $tokenId)
            ->executeStatement();
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return $this->queryBuilder()
            ->select($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("access_token", "?")
            )
            ->setParameter(0, $tokenId)
            ->executeQuery()
            ->rowCount() == 0;
    }
}
