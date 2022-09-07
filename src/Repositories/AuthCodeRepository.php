<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\AuthCode;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use DBAL;

    protected $table = "oauth2_auth_codes";

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        try {
            $this->queryBuilder()
                ->insert($this->table)
                ->values([
                    "auth_code" => "?",
                    "expiry" => "?",
                    "user_id" => "?",
                    "scopes" => "?",
                    "client_id" => "?",
                ])
                ->setParameter(0, $authCodeEntity->getIdentifier())
                ->setParameter(
                    1,
                    $authCodeEntity->getExpiryDateTime(),
                    "datetime"
                )
                ->setParameter(2, $authCodeEntity->getUserIdentifier())
                ->setParameter(3, $authCodeEntity->getScopes(), "json")
                ->setParameter(4, $authCodeEntity->getClient()->getIdentifier())
                ->executeStatement();
        } catch (UniqueConstraintViolationException $e) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    public function revokeAuthCode($codeId)
    {
        $this->queryBuilder()
            ->delete($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("auth_code", "?")
            )
            ->setParameter(0, $codeId)
            ->executeStatement();
    }

    public function isAuthCodeRevoked($codeId)
    {
        return $this->queryBuilder()
            ->select($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("auth_code", "?")
            )
            ->setParameter(0, $codeId)
            ->executeQuery()
            ->rowCount() == 0;
    }
}
