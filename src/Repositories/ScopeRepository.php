<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Scope;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    use DBAL;

    protected $table = "oauth2_scopes";

    private $userRepository;

    public function __construct(
        Connection $connection,
        UserRepository $userRepository
    ) {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        $data =
            $this->queryBuilder()
                ->select("*")
                ->from($this->table)
                ->where(
                    $this->q()
                        ->expr()
                        ->eq("scope", ":s")
                )
                ->setParameter("s", $identifier)
                ->executeQuery()
                ->fetchAssociative() ?:
            null;
        if ($data) {
            return Scope::fromArray($data);
        }
        return null;
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
