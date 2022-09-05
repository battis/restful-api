<?php

namespace Battis\OAuth2\Server\Repositories;

use Battis\OAuth2\Server\Entities\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
  public function getNewRefreshToken()
  {
    return new RefreshToken();
  }

  public function persistNewRefreshToken(
    RefreshTokenEntityInterface $refreshTokenEntity
  ) {
    RefreshToken::create($refreshTokenEntity);
  }

  public function revokeRefreshToken($tokenId)
  {
    RefreshToken::destroy($tokenId);
  }

  public function isRefreshTokenRevoked($tokenId)
  {
    return empty(RefreshToken::find($tokenId));
  }
}
