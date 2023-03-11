<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use PDO;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(PDO $pdo)
    {
        CRUD\Connection::setPDO($pdo);
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
