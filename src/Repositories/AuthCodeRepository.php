<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD\Manager;
use Battis\OAuth2\Server\Entities\AuthCode;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(Connection $connection)
    {
        Manager::setConnection($connection);
    }

    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        AuthCode::create([
            "identifier" => $authCodeEntity->getIdentifier(),
            "expiryDateTime" => $authCodeEntity->getExpiryDateTime(),
            "userIdentifier" => $authCodeEntity->getUserIdentifier(),
            "scopes" => $authCodeEntity->getScopes(),
            "clientIdentifier" => $authCodeEntity->getClient()->getIdentifier(),
        ]);
    }

    public function revokeAuthCode($codeId)
    {
        AuthCode::delete($codeId);
    }

    public function isAuthCodeRevoked($codeId)
    {
        return AuthCode::read($codeId) === null;
    }
}
