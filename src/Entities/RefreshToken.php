<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD\StoredObject;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshToken extends StoredObject implements RefreshTokenEntityInterface
{
    use EntityTrait, RefreshTokenTrait;

    protected static $crud_tableName = "oauth2_refresh_tokens";
    protected static $crud_primaryKey = "identifier";

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
