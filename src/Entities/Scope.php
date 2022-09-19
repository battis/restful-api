<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

class Scope extends CRUD\Record implements ScopeEntityInterface
{
    use EntityTrait, ScopeTrait;

    protected static function defineSpec(): CRUD\Spec
    {
        return new CRUD\Spec(self::class, null, "identifier", [
            "identifier" => "scope",
        ]);
    }
}
