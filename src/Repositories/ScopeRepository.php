<?php

namespace Battis\OAuth2\Repositories;

use Battis\OAuth2\Entities\Scope;
use Battis\OAuth2\Entities\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
  public function getScopeEntityByIdentifier($identifier)
  {
    return Scope::find($identifier);
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
    $user = $userIdentifier ? User::find($userIdentifier) : null;
    foreach ($scopes as $proposedScope) {
      $scopeId = $proposedScope->getIdentifier();
      $scope = Scope::find($scopeId);
      if (
        $scope &&
        (empty($clientEntity->scopes) ||
          in_array($scope, $clientEntity->scopes)) &&
        (empty($user) ||
          (empty($user->scopes) || in_array($scope, $user->scopes)))
      ) {
        array_push($finalizedScopes, $scope);
      }
    }
    return $finalizedScopes;
  }
}
