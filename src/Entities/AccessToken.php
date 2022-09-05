<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Traits\SerializeScopesTrait;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AccessToken extends Model implements AccessTokenEntityInterface
{
  // League/OAuth2-Server
  use AccessTokenTrait, TokenEntityTrait, EntityTrait;

  // Eloquent ORM
  use Eloquence, Mappable, SerializeScopesTrait;

  protected $table = "oauth2_access_tokens";

  protected $primary_key = "access_token";

  protected $maps = [
    "identifier" => "access_token",
    "expiryDateTime" => "expiry",
    "userIdentifier" => "user_id",
  ];

  protected $fillable = [
    "access_token",
    "expiry",
    "user_id",
    "scopes",
    "client_id",
  ];

  public $timestamps = false;

  public function client()
  {
    return $this->belongsTo(Client::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
