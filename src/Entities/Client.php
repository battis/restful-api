<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Client implements
    ClientEntityInterface,
    UserAssignable,
    Scopeable,
    TokenGrantable
{
    use EntityTrait, ClientTrait;

    /** @var ?string */
    private $userIdentifier = null;

    /** @var ScopeEntityInterface[] */
    private $scopes = [];

    /** @var string[] */
    private $grant_types = [];

    public function getUserIdentifier(): ?string
    {
        return $this->user_id;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getGrantTypes(): array
    {
        return $this->grant_types;
    }
}
