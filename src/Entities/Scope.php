<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Traits\FromArrayTrait;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

class Scope implements ScopeEntityInterface
{
    use EntityTrait, ScopeTrait, FromArrayTrait;
}
