<?php

use Battis\OAuth2\Server as OAuth2;
use DI\Container;

/** @var Container $container */

// prepare to inject dependencies for OAuth2 Server
// if overriding implementations, set them before calling this method
OAuth2\Dependencies::prepare($container);
