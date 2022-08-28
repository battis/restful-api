<?php

namespace Battis\OAuth2\Server\Entities;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Scope extends Model implements ScopeEntityInterface
{
  // League/OAuth2-Server
  use EntityTrait, ScopeTrait;

  // Eloquent ORM
  use Eloquence, Mappable;

  protected $table = "oauth2_scopes";

  protected $primary_key = "scope";

  protected $maps = ["identifier" => "scope"];

  protected $fillable = ["scope", "description"];
}
