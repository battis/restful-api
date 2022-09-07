<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Traits\FromArrayTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Battis\UserSession;

class User implements
    UserEntityInterface,
    UserSession\Entities\UserEntityInterface
{
    use EntityTrait, FromArrayTrait;

    /* @var string password hash */
    private $password;

    public function passwordVerify(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
