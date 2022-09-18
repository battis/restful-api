<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use Battis\OAuth2\Server\Entities\User;
use Battis\UserSession\Entities\UserEntityInterface;
use Battis\UserSession\Repositories as UserSession;
use Doctrine\DBAL;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements
    UserRepositoryInterface,
    UserSession\UserRepositoryInterface
{
    protected $table = "users";

    public function __construct(DBAL\Connection $connection)
    {
        CRUD\Manager::get($connection);
    }

    public function getUserEntityByUsername(
        string $username
    ): ?UserEntityInterface {
        return User::read($username);
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
