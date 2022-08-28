<?php

namespace Battis\OAuth2\Entities;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class RefreshToken extends Model implements RefreshTokenEntityInterface
{
  // League/OAuth2-Server
  use EntityTrait, RefreshTokenTrait;

  // Eloquent ORM
  use Eloquence, Mappable;

  protected $table = "oauth2_refresh_tokens";

  protected $primary_key = "refresh_token";

  protected $maps = [
    "identifier" => "refresh_token",
    "expiryDateTime" => "expiry",
  ];

  protected $fillable = ["refresh_token", "expiry", "access_token_id"];

  public $timestamp = false;
}
