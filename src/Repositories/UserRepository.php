<?php

namespace Battis\OAuth2\Repositories;

use Battis\OAuth2\Entities\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
  public function getUserEntityByUserCredentials(
    $username,
    $password,
    $grantType,
    ClientEntityInterface $clientEntity
  ) {
    if (empty($clientEntity)) {
      $user = User::find($username);
    }
  }
}
