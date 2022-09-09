<?php

namespace Battis\OAuth2\Server\Entities\Interfaces;

use League\OAuth2\Server\Grant\GrantTypeInterface;

interface TokenGrantable
{
    /**
     * @return string[]
     */
    function getGrantTypes(): array;
}
