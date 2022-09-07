<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\User;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Battis\UserSession\Entities\UserEntityInterface;
use Battis\UserSession\Repositories\UserRepositoryInterface as UserSessionUserRepositoryuInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface as OAuth2ServerUserRepositoryInterface;

class UserRepository implements
    OAuth2ServerUserRepositoryInterface,
    UserSessionUserRepositoryuInterface
{
    use DBAL;

    protected $table = "users";

    public function getUserEntityByUsername(
        string $username
    ): ?UserEntityInterface {
        $data = $this->queryBuilder()
            ->select($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("username", "?")
            )
            ->setParameter(0, $username)
            ->executeQuery()
            ->fetchOne();
        return $data ? User::fromArray($data) : null;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $user = $this->getUserEntityByUsername($username);
        // FIXME:
    }
}
