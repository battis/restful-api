<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Client extends CRUD\Record implements
    ClientEntityInterface,
    UserAssignable,
    Scopeable,
    TokenGrantable
{
    use EntityTrait, ClientTrait;

    protected static $crud_tableName = "oauth2_clients";
    protected static $crud_primaryKey = "identifier";

    /** @var ?string */
    protected $userIdentifier = null;

    /** @var ScopeEntityInterface[] */
    protected $scopes = [];

    /** @var string[] */
    protected $grant_types = [];

    public function getUserIdentifier(): ?string
    {
        return $this->user_id;
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
