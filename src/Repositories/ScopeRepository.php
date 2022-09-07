<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\Scope;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Entities\ClientEntityInterface;
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
                ->select($this->table)
                ->where(
                    $this->q()
                        ->expr()
                        ->eq("scope", "?")
                )
                ->setParameter(0, $identifier)
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
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $finalizedScopes = [];
        $user = $userIdentifier
            ? $this->userRepository->getUserEntityByUsername($userIdentifier)
            : null;
        // FIXME:
        foreach ($scopes as $proposedScope) {
            if (
                (empty($clientEntity->scopes) ||
                    in_array($proposedScope, $clientEntity->scopes)) &&
                (empty($user) ||
                    (empty($user->scopes) ||
                        in_array($proposedScope, $user->scopes)))
            ) {
                array_push($finalizedScopes, $proposedScope);
            }
        }
        return $finalizedScopes;
    }
}
