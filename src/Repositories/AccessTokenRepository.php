<?php

namespace Battis\OAuth2\Repositories;

use Battis\OAuth2\Entities\AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
  public function getNewToken(
    ClientEntityInterface $clientEntity,
    array $scopes,
    $userIdentifier = null
  ) {
    $accessToken = new AccessToken();
    $accessToken->setClient($clientEntity);
    $accessToken->setUserIdentifier($userIdentifier);
    foreach ($scopes as $scope) {
      $accessToken->addScope($scope);
    }
    return $accessToken;
  }

  public function persistNewAccessToken(
    AccessTokenEntityInterface $accessTokenEntity
  ) {
    AccessToken::create($accessTokenEntity);
  }

  public function revokeAccessToken($tokenId)
  {
    AccessToken::destroy($tokenId);
  }

  public function isAccessTokenRevoked($tokenId)
  {
    return empty(AccessToken::find($tokenId));
  }
}
