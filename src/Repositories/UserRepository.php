<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\User;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Battis\UserSession\Entities\UserEntityInterface;
use Battis\UserSession\Repositories as UserSession;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements
    UserRepositoryInterface,
    UserSession\UserRepositoryInterface
{
    use DBAL;

    protected $table = "users";

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getUserEntityByUsername(
        string $username
    ): ?UserEntityInterface {
        $data = $this->queryBuilder()
            ->select("*")
            ->from($this->table)
            ->where("username = ?")
            ->setParameter(0, $username)
            ->executeQuery()
            ->fetchAssociative();
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
