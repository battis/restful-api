<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD\StoredObject;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AuthCode extends StoredObject implements AuthCodeEntityInterface
{
    use AuthCodeTrait, TokenEntityTrait, EntityTrait;

    protected static $crud_tableName = "oauth2_auth_codes";
    protected static $crud_primaryKey = "identifier";
}
