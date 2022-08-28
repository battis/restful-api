<?php

namespace Battis\OAuth2;

use Battis\OAuth2\Factories\AuthorizationServerFactory;
use Battis\OAuth2\Factories\ResourceServerFactory;
use Battis\OAuth2\Repositories\AccessTokenRepository;
use Battis\OAuth2\Repositories\AuthCodeRepository;
use Battis\OAuth2\Repositories\ClientRepository;
use Battis\OAuth2\Repositories\RefreshTokenRepository;
use Battis\OAuth2\Repositories\ScopeRepository;
use Battis\OAuth2\Repositories\UserRepository;
use Battis\OAuth2\Settings;
use DI\Container;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;

class Dependencies
{
  public static function __invoke()
  {
    return [
      Settings::class => fn() => new Settings(),
      ClientRepositoryInterface::class => fn() => new ClientRepository(),
      ScopeRepositoryInterface::class => fn() => new ScopeRepository(),
      AccessTokenRepository::class => fn() => new AccessTokenRepository(),
      AuthCodeRepositoryInterface::class => fn() => new AuthCodeRepository(),
      RefreshTokenRepositoryInterface::class => fn() => new RefreshTokenRepository(),
      UserRepositoryInterface::class => fn() => new UserRepository(),
      AuthorizationServer::class => fn(
        ContainerInterface $container
      ) => AuthorizationServerFactory::createFromContainer($container),
      ResourceServer::class => fn(
        ContainerInterface $container
      ) => ResourceServerFactory::createFromContainer($container),
    ];
  }

  public static function prepare(Container $container)
  {
    foreach (self::__invoke() as $key => $value) {
      if (false === $container->has($key)) {
        $container->set($key, $value);
      }
    }
  }
}
