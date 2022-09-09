<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use Battis\OAuth2\Server\Entities\User;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Battis\UserSession\Entities\UserEntityInterface;
use Battis\UserSession\Repositories as UserSession;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Grant\GrantTypeInterface;
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
            ->where(
                $this->q()
                    ->expr()
                    ->eq("username", ":u")
            )
            ->setParameter("u", $username)
            ->executeQuery()
            ->fetchAssociative();
        return $data ? User::fromArray($data) : null;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $client
    ) {
        $user = $this->getUserEntityByUsername($username);
        $userGrantTypes =
            $user instanceof TokenGrantable ? $user->getGrantTypes() : [];
        $clientGrantTypes =
            $client instanceof TokenGrantable ? $client->getGrantTypes() : [];

        // verify user password...
        if ($user->passwordVerify($password)) {
            // ...and that the user can use this grant type...
            if ($this->availableGrantType($userGrantTypes, $grantType)) {
                // ...and that the user can use this client...
                if (
                    !($client instanceof UserAssignable) ||
                    empty($client->getUserIdentifier()) ||
                    $client->getUserIdentifier() === $user->getIdentifier()
                ) {
                    // ...and that this client can request this grant type
                    if (
                        $this->availableGrantType($clientGrantTypes, $grantType)
                    ) {
                        return $user;
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param string[] $haystack
     * @param string $needle
     * @return bool
     */
    private function availableGrantType(array $haystack, string $needle): bool
    {
        return empty($haystack) || in_array($needle, $haystack);
    }
}
