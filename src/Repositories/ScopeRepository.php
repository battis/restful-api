<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Scope;
use Doctrine\DBAL;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    private $userRepository;

    public function __construct(
        DBAL\Connection $connection,
        UserRepository $userRepository
    ) {
        CRUD\Manager::get($connection);
        $this->userRepository = $userRepository;
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        return Scope::read($identifier);
    }

    /**
     * @param Scope[] $scopes
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @param ?string $userIdentifier
     * @return Scope[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $client,
        $userIdentifier = null
    ) {
        $grantScopes =
            $grantType instanceof Scopeable ? $grantType->getScope() : [];
        $clientScopes =
            $client instanceof Scopeable ? $client->getScopes() : [];
        $user = $userIdentifier
            ? $this->userRepository->getUserEntityByUsername($userIdentifier)
            : null;
        $userScopes =
            $user && $user instanceof Scopeable ? $user->getScopes() : [];

        return array_filter($scopes, function (
            ScopeEntityInterface $scope
        ) use ($grantScopes, $clientScopes, $userScopes) {
            return $this->inScope($grantScopes, $scope) &&
                $this->inScope($clientScopes, $scope) &&
                $this->inScope($userScopes, $scope);
        });
    }

    /**
     * @param ScopeEntityInterface[] $haystack
     * @param ScopeEntityInterface $needle
     * @return bool
     */
    private function inScope(array $haystack, ScopeEntityInterface $needle)
    {
        return empty($haystack) ||
            array_reduce($haystack, function (
                $matchedAnotherScope = false,
                ScopeEntityInterface $straw
            ) use ($needle) {
                return $matchedAnotherScope ||
                    $straw->getIdentifier() == $needle->getIdentifier();
            });
    }
}
