<?php

namespace Battis\OAuth2\Repositories;

use Battis\OAuth2\Entities\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
  public function getNewAuthCode()
  {
    return new AuthCode();
  }

  public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
  {
    AuthCode::create($authCodeEntity);
  }

  public function revokeAuthCode($codeId)
  {
    AuthCode::destroy($codeId);
  }

  public function isAuthCodeRevoked($codeId)
  {
    return empty(AuthCode::find($codeId));
  }
}
