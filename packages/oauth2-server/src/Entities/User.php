<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Battis\UserSession;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class User extends CRUD\Record implements
    UserEntityInterface,
    UserSession\Entities\UserEntityInterface,
    Scopeable,
    TokenGrantable
{
    use EntityTrait;

    protected static function defineSpec(): CRUD\Spec
    {
        return new CRUD\Spec(self::class, null, "identifier", [
            "identifier" => "username",
        ]);
    }

    /* @var string password hash */
    protected $password;

    /** @var ScopeEntityInterface[] */
    protected $scopes = [];

    /** @var string[] */
    protected $grant_types = [];

    public function passwordVerify(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function setScopes($scopes)
    {
        if (is_array($scopes)) {
            foreach ($scopes as $scope) {
                $this->scopes[] = Scope::read($scope);
            }
        }
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getGrantTypes(): array
    {
        return $this->grant_types;
    }
}
