<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\AuthCode;
use Battis\OAuth2\Server\Repositories\Traits\TokenRepositoryTrait;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use TokenRepositoryTrait;

    public function __construct(Connection $connection)
    {
        $this->table = "oauth2_auth_codes";
        $this->connection = $connection;
    }

    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $this->persistToken([
            "identifier" => $authCodeEntity->getIdentifier(),
            "expiryDateTime" => $authCodeEntity->getExpiryDateTime(),
            "userIdentifier" => $authCodeEntity->getUserIdentifier(),
            "scopes" => $authCodeEntity->getScopes(),
            "clientIdentifier" => $authCodeEntity->getClient()->getIdentifier(),
        ]);
    }

    public function revokeAuthCode($codeId)
    {
        $this->revokeToken($codeId);
    }

    public function isAuthCodeRevoked($codeId)
    {
        $this->isTokenRevoked($codeId);
    }
}
