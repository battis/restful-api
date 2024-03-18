<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshToken extends CRUD\Record implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait, EntityTrait;

    protected static function defineSpec(): CRUD\Spec
    {
        return new CRUD\Spec(
            self::class,
            "oauth2_refresh_tokens",
            "identifier",
            [
                "identifier" => "token",
                "expiryDateTime" => "expiry",
                "accessTokenIdentifier" => "access_token_id",
            ]
        );
    }

    public function __construct(array $data = [])
    {
        if (in_array("accessTokenIdentifier", $data)) {
            $this->accessToken = AccessToken::read(
                $data["accessTokenIdentifier"]
            );
            unset($data["accessTokenIdentifier"]);
        }
        parent::__construct($data);
    }
}
