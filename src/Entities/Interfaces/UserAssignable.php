<?php

namespace Battis\OAuth2\Server\Entities\Interfaces;

interface UserAssignable
{
    /**
     * @return string|int|null
     */
    function getUserIdentifier();
}
