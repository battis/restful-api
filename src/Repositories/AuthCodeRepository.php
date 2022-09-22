<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\AuthCode;
use Doctrine\DBAL;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(DBAL\Connection $connection)
    {
        CRUD\Manager::get($connection);
    }

    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        AuthCode::create([
            "identifier" => $authCodeEntity->getIdentifier(),
            "expiryDateTime" => $authCodeEntity
                ->getExpiryDateTime()
                ->format("Y-m-d H:i:s"),
            "userIdentifier" => $authCodeEntity->getUserIdentifier(),
            "scopes" => json_encode($authCodeEntity->getScopes()),
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
