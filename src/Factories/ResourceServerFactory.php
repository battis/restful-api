<?php

namespace Battis\OAuth2\Factories;

use Battis\OAuth2\Settings;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;

class ResourceServerFactory
{
  public static function createFromContainer(ContainerInterface $container)
  {
    /** @var Settings $settings */
    $settings = $container->get(Settings::class);
    $server = new ResourceServer(
      $container->get(AccessTokenEntityInterface::class),
      $settings->getPathToPublicKey()
    );

    return $server;
  }
}
