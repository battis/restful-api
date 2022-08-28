<?php

namespace Battis\OAuth2\Server\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Serializable;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Client extends Model implements ClientEntityInterface
{
  // Laravel/OAuth2-Server
  use EntityTrait, ClientTrait;

  // Eloquent ORM
  use Eloquence, Mappable;

  protected $table = "oauth2_clients";

  protected $primary_key = "client_id";

  protected $maps = [
    "identifier" => "client_id",
    "name" => "display_name",
    "isConfidential" => "confidential",
    "redirectUri" => "redirect_uri",
  ];

  protected $fillable = [
    "client_id",
    "display_name",
    "redirect_uri",
    "user_id",
    "grant_types",
    "confidential",
  ];

  protected $hidden = ["client_secret"];

  public function grantTypes(): Attribute
  {
    return new Attribute(
      fn($value) => json_decode($value),
      fn($value) => json_encode($value)
    );
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function validate($secret, $grantType)
  {
    return (empty($this->grant_types) ||
      in_array($grantType, $this->grant_types)) &&
      $secret === $this->client_secret;
  }
}
