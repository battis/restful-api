<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\Client;
use Battis\OAuth2\Server\Repositories\Traits\DBAL;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    use DBAL;

    protected $table = "oauth2_clients";

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $clientIdentifier
     * @return ?Client
     */
    public function getClientEntity($clientIdentifier)
    {
        return $this->queryBuilder()
            ->select("*")
            ->from($this->table)
            ->where(
                $this->q()
                    ->expr()
                    ->eq("identifier", ":i")
            )
            ->setParameter("i", $clientIdentifier)
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
                            ->eq("identifier", ":i"),
                        $this->q()
                            ->expr()
                            ->eq("client_secret", ":s")
                    )
            )
            ->setParameter("i", $clientIdentifier)
            ->setParameter("s", $clientSecret)
            ->executeQuery()
            ->fetchAssociative();
        return empty($client["grant_types"]) ||
            in_array($grantType, $client["grant_types"]);
    }
}
