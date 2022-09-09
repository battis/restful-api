<?php

namespace Battis\OAuth2\Server\Entities\Interfaces;

interface UserAssignable
{
    function getUserIdentifier(): ?string;
}
