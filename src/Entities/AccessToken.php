<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD\StoredObject;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessToken extends StoredObject implements
    AccessTokenEntityInterface,
    UserAssignable
{
    use AccessTokenTrait, TokenEntityTrait, EntityTrait;

    protected static $crud_tableName = "oauth2_access_tokens";
    protected static $crud_primaryKey = "identifier";

    public function __construct(array $data = [])
    {
        if (in_array("clientIdentifier", $data)) {
            $this->client = Client::read($data["clientIdentifer"]);
            unset($data["clientIdentifier"]);
        }
        parent::__construct($data);
    }
}
