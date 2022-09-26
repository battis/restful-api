<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Client;
use Doctrine\DBAL;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(DBAL\Connection $connection)
    {
        CRUD\Manager::get($connection);
    }

    /**
     * @param string $clientIdentifier
     * @return ?Client
     */
    public function getClientEntity($clientIdentifier)
    {
        return Client::read($clientIdentifier);
    }

    /**
     * @param string $clientIdentifier
     * @param string $clientSecret
     * @param string $grantType
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $clients = Client::retrieve([
            "identifier" => $clientIdentifier,
            "client_secret" => $clientSecret,
        ]);
        return !empty($clients) &&
            (empty($clients[0]->getGrantTypes()) ||
                in_array($grantType, $clients[0]->getGrantTypes()));
    }
}
