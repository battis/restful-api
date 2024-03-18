<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessToken extends CRUD\Record implements
    AccessTokenEntityInterface,
    UserAssignable
{
    use AccessTokenTrait, TokenEntityTrait, EntityTrait;

    protected static function defineSpec(): CRUD\Spec
    {
        return new CRUD\Spec(
            self::class,
            "oauth2_access_tokens",
            "identifier",
            [
                "identifier" => "token",
                "expiryDateTime" => "expiry",
                "userIdentifier" => "user_id",
                "clientIdentifier" => "client_id",
            ]
        );
    }

    public function __construct(array $data = [])
    {
        if (in_array("client_id", $data)) {
            $this->client = Client::read($data["client_id"]);
            unset($data["client_id"]);
        }
        parent::__construct($data);
    }
}
