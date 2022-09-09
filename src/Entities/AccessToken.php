<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessToken implements AccessTokenEntityInterface, UserAssignable
{
    use AccessTokenTrait, TokenEntityTrait, EntityTrait;
}
