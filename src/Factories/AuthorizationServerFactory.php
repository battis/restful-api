<?php

namespace Battis\OAuth2\Factories;

use Battis\OAuth2\Settings;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Psr\Container\ContainerInterface;

class AuthorizationServerFactory
{
  public static function createFromContainer(ContainerInterface $container)
  {
    /** @var Settings $settings */
    $settings = $container->get(Settings::class);

    $server = new AuthorizationServer(
      $container->get(ClientRepositoryInterface::class),
      $container->get(AccessTokenRepositoryInterface::class),
      $container->get(ScopeRepositoryInterface::class),
      $settings->getPathToPrivateKey(),
      $settings->getPathToPublicKey()
    );

    $grants = $settings->getGrantTypes();
    foreach ($grants as $grant) {
      if (is_callable($grant)) {
        $grant = $grant($container);
      }
      $server->enableGrantType($grant, $settings->getAccessTokenTTL());
    }

    return $server;
  }
}
