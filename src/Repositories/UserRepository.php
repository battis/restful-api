<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\User;
use Battis\UserSession\Entities\UserEntityInterface;
use Battis\UserSession\Repositories\UserRepositoryInterface as UserSessionUserRepositoryuInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface as OAuth2ServerUserRepositoryInterface;

class UserRepository implements
  OAuth2ServerUserRepositoryInterface,
  UserSessionUserRepositoryuInterface
{
  public function getUserEntityByUsername(
    string $username
  ): ?UserEntityInterface {
    return User::find($username);
  }

  public function getUserEntityByUserCredentials(
    $username,
    $password,
    $grantType,
    ClientEntityInterface $clientEntity
  ) {
    // FIXME check against other parameters
    return User::find($username);
  }
}
