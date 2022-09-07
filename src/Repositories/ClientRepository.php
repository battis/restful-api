<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\Client;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    use DBAL;

    protected $table = "oauth2_clients";

    /**
     * @param string $clientIdentifier
     * @return ?Client
     */
    public function getClientEntity($clientIdentifier)
    {
        return $this->queryBuilder()
            ->select($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("client_id", ":client_id")
            )
            ->setParameter("client_id", $clientIdentifier)
            ->executeQuery()
            ->fetchOne() ?:
            null;
    }

    /**
     * @param string $clientIdentifier
     * @param string $clientSecret
     * @param string $grantType
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        // FIXME: someone more skeptical than me would check $grantType for valid values
        $client = $this->queryBuilder()
            ->select($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->and(
                        $this->q()
                            ->expr()
                            ->eq("client_id", "?"),
                        $this->q()
                            ->expr()
                            ->eq("client_secret", "?")
                    )
            )
            ->setParameter(0, $clientIdentifier)
            ->setParameter(1, $clientSecret)
            ->executeQuery()
            ->fetchAssociative();
        return empty($client["grant_types"]) ||
            in_array($grantType, $client["grant_types"]);
    }
}
