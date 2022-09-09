<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use Battis\OAuth2\Server\Entities\Traits\FromArrayTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Battis\UserSession;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class User implements
    UserEntityInterface,
    UserSession\Entities\UserEntityInterface,
    Scopeable,
    TokenGrantable
{
    use EntityTrait, FromArrayTrait;

    /* @var string password hash */
    private $password;

    /** @var ScopeEntityInterface[] */
    private $scopes = [];

    /** @var string[] */
    private $grant_types = [];

    public function passwordVerify(string $password): bool
    {
        return password_verify($password, $this->password);
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
