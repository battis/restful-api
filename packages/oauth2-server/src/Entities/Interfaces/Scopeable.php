<?php

namespace Battis\OAuth2\Server\Entities\Interfaces;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

interface Scopeable
{
    /**
     * @return ScopeEntityInterface[]
     */
    function getScopes(): array;
}
