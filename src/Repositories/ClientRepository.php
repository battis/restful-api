<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\Client;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
  /**
   * @param string $clientIdentifier
   * @return ?Client
   */
  public function getClientEntity($clientIdentifier)
  {
    return Client::find($clientIdentifier);
  }

  /**
   * @param string $clientIdentifier
   * @param string $clientSecret
   * @param string $grantType
   * @return bool
   */
  public function validateClient($clientIdentifier, $clientSecret, $grantType)
  {
    $this->getClientEntity($clientIdentifier)->validate(
      $clientSecret,
      $grantType
    );
  }
}
