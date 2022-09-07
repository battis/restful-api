<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Traits\FromArrayTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements UserEntityInterface
{
    use EntityTrait, FromArrayTrait;
}
