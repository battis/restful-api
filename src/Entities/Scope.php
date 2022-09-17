<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD\StoredObject;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

class Scope extends StoredObject implements ScopeEntityInterface
{
    use EntityTrait, ScopeTrait;

    protected static $crud_tableName = "oauth2_scopes";
    protected static $crud_primaryKey = "identifier";
}
