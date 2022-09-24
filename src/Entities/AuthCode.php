<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use DateTimeImmutable;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AuthCode extends CRUD\Record implements AuthCodeEntityInterface
{
    use AuthCodeTrait, EntityTrait;
    use TokenEntityTrait {
        setExpiryDateTime as traitSetExpiryDateTime;
    }

    protected static function defineSpec(): CRUD\Spec
    {
        return new CRUD\Spec(self::class, "oauth2_auth_codes", "identifier", [
            "identifier" => "code",
            "expiryDateTime" => "expiry",
            "userIdentifier" => "user_id",
            "clientIdentifier" => "client_id",
        ]);
    }

    public function setExpiryDateTime($value)
    {
        if (!($value instanceof DateTimeImmutable)) {
            $value = new DateTimeImmutable($value);
        }
        return $this->traitSetExpiryDateTime($value);
    }
}
