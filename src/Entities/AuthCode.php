<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Traits\SerializeScopesTrait;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AuthCode extends Model implements AuthCodeEntityInterface
{
  // League/OAuth-Server
  use EntityTrait, TokenEntityTrait, AuthCodeTrait;

  // Eloquent ORM
  use Eloquence, Mappable, SerializeScopesTrait;

  protected $table = "oauth2_auth_codes";

  protected $primary_key = "auth_code";

  protected $maps = [
    "identifier" => "auth_code",
    "expiryDateTime" => "expiry",
    "userIdentifier" => "user_id",
  ];

  protected $fillable = [
    "auth_code",
    "expiry",
    "user_id",
    "scopes",
    "client_id",
  ];

  public $timestamps = false;

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function client()
  {
    return $this->belongsTo(Client::class);
  }
}
