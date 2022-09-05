<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Traits\SerializeScopesTrait;
use Battis\UserSession\Entities\UserEntityInterface as UserSessionUserEntityInterface;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\UserEntityInterface as OAuth2ServerUserEntityInterface;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class User extends Model implements
  OAuth2ServerUserEntityInterface,
  UserSessionUserEntityInterface
{
  // Laravel/OAuth2-Server
  public function getIdentifier()
  {
    return $this->identifier;
  }

  // Eloquent ORM
  use Eloquence, Mappable, SerializeScopesTrait;

  protected $maps = ["identifier" => "username"];

  protected $fillable = ["username", "display_name", "scopes"];

  protected $hidden = ["password"];

  public function passwordVerify(string $password): bool
  {
    return password_verify($password, $this->password);
  }
}
